<?php

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'cadastro':
        cadastrarUsuario();
        break;
    case 'login':
        fazerLogin();
        break;
    case 'logout':
        fazerLogout();
        break;
    case 'verificar_sessao':
        verificarSessao();
        break;
    case 'recuperar_senha':
        recuperarSenha();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

function cadastrarUsuario() {
    try {
        $db = getDB();
        
        $tipo = $_POST['tipo'] ?? '';
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        if (empty($tipo) || empty($nome) || empty($email) || empty($senha)) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido');
        }
        
        if (strlen($senha) < 6) {
            throw new Exception('A senha deve ter no mínimo 6 caracteres');
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        if ($tipo === 'associado') {
            $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
            $dataNascimento = $_POST['dataNascimento'] ?? '';
            $endereco = trim($_POST['endereco'] ?? '');
            $bairro = trim($_POST['bairro'] ?? '');
            $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
            $cidade = trim($_POST['cidade'] ?? '');
            $estado = trim($_POST['estado'] ?? '');
            
            if (strlen($cpf) !== 11) {
                throw new Exception('CPF inválido');
            }
            
            if (empty($dataNascimento)) {
                throw new Exception('Data de nascimento é obrigatória');
            }
            
            $dataNasc = new DateTime($dataNascimento);
            $hoje = new DateTime();
            $idade = $hoje->diff($dataNasc)->y;
            
            if ($idade < 16) {
                throw new Exception('É necessário ter no mínimo 16 anos para se cadastrar');
            }
            
            if (empty($endereco) || empty($bairro) || empty($cidade) || empty($estado)) {
                throw new Exception('Todos os campos de endereço são obrigatórios');
            }
            
            if (strlen($cep) !== 8) {
                throw new Exception('CEP inválido');
            }
            
            $stmt = $db->prepare("SELECT cpf_associado FROM associado WHERE cpf_associado = ?");
            $stmt->execute([$cpf]);
            if ($stmt->fetch()) {
                throw new Exception('CPF já cadastrado');
            }
            
            $stmt = $db->prepare("SELECT email_associado FROM associado WHERE email_associado = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('E-mail já cadastrado');
            }
            
            $stmt = $db->prepare("
                INSERT INTO associado (
                    cpf_associado, 
                    nom_associado, 
                    cel_associado, 
                    email_associado, 
                    sen_associado,
                    dat_nascimento,
                    endereco,
                    bairro,
                    cep,
                    cidade,
                    estado
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $cpf, 
                $nome, 
                $telefone, 
                $email, 
                $senhaHash,
                $dataNascimento,
                $endereco,
                $bairro,
                $cep,
                $cidade,
                $estado
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'tipo' => 'associado'
            ]);
            
        } else if ($tipo === 'comerciante') {
            $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
            $nomeComercial = trim($_POST['nomeComercial'] ?? '');
            $categoria = $_POST['categoria'] ?? 'outros';
            $endereco = trim($_POST['endereco'] ?? '');
            $bairro = trim($_POST['bairro'] ?? '');
            $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
            $cidade = trim($_POST['cidade'] ?? '');
            $uf = trim($_POST['estado'] ?? '');
            
            if (strlen($cnpj) !== 14) {
                throw new Exception('CNPJ inválido');
            }
            
            if (empty($nomeComercial)) {
                throw new Exception('Nome do estabelecimento é obrigatório');
            }
            
            if (empty($endereco) || empty($bairro) || empty($cidade) || empty($uf)) {
                throw new Exception('Todos os campos de endereço são obrigatórios');
            }
            
            if (strlen($cep) !== 8) {
                throw new Exception('CEP inválido');
            }
            
            $stmt = $db->prepare("SELECT cnpj_comercio FROM comercio WHERE cnpj_comercio = ?");
            $stmt->execute([$cnpj]);
            if ($stmt->fetch()) {
                throw new Exception('CNPJ já cadastrado');
            }
            
            $stmt = $db->prepare("SELECT email_comercio FROM comercio WHERE email_comercio = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('E-mail já cadastrado');
            }
            
            $stmt = $db->prepare("SELECT id_categoria FROM categoria WHERE nom_categoria = ?");
            $categorias = [
                'alimentacao' => 'Alimentação',
                'moda' => 'Moda e Vestuário',
                'saude' => 'Saúde e Beleza',
                'tecnologia' => 'Tecnologia',
                'educacao' => 'Educação',
                'lazer' => 'Lazer e Entretenimento',
                'servicos' => 'Serviços',
                'outros' => 'Outros'
            ];
            $stmt->execute([$categorias[$categoria] ?? 'Outros']);
            $categoriaRow = $stmt->fetch();
            $idCategoria = $categoriaRow['id_categoria'];
            
            $stmt = $db->prepare("
                INSERT INTO comercio (
                    cnpj_comercio, 
                    id_categoria, 
                    raz_social_comercio, 
                    nom_fantasia_comercio, 
                    con_comercio, 
                    email_comercio, 
                    sen_comercio,
                    end_comercio,
                    bai_comercio,
                    cep_comercio,
                    cd_comercio,
                    uf_comercio
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $cnpj, 
                $idCategoria, 
                $nome, 
                $nomeComercial, 
                $telefone, 
                $email, 
                $senhaHash,
                $endereco,
                $bairro,
                $cep,
                $cidade,
                $uf
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'tipo' => 'comerciante'
            ]);
        } else {
            throw new Exception('Tipo de usuário inválido');
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function fazerLogin() {
    try {
        $db = getDB();
        
        $tipo = $_POST['tipo'] ?? '';
        $documento = preg_replace('/\D/', '', $_POST['documento'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        if (empty($tipo) || empty($documento) || empty($senha)) {
            throw new Exception('Todos os campos são obrigatórios');
        }
        
        if ($tipo === 'associado') {
            if (strlen($documento) !== 11) {
                throw new Exception('CPF inválido');
            }
            
            $stmt = $db->prepare("
                SELECT cpf_associado, nom_associado, email_associado, sen_associado
                FROM associado
                WHERE cpf_associado = ?
            ");
            $stmt->execute([$documento]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception('CPF não encontrado');
            }
            
            if (!password_verify($senha, $usuario['sen_associado'])) {
                throw new Exception('Senha incorreta');
            }
            
            $_SESSION['usuario'] = [
                'tipo' => 'associado',
                'cpf' => $usuario['cpf_associado'],
                'nome' => $usuario['nom_associado'],
                'email' => $usuario['email_associado']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso!',
                'tipo' => 'associado',
                'redirect' => 'dashboard_associado.php'
            ]);
            
        } else if ($tipo === 'comerciante') {
            if (strlen($documento) !== 14) {
                throw new Exception('CNPJ inválido');
            }
            
            // Primeiro, vamos verificar se o comerciante existe
            $stmt = $db->prepare("
                SELECT cnpj_comercio, nom_fantasia_comercio, email_comercio, sen_comercio
                FROM comercio
                WHERE cnpj_comercio = ?
            ");
            $stmt->execute([$documento]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception('CNPJ não encontrado no sistema');
            }
            
            // Verificar se a senha está armazenada como hash ou texto plano
            $senhaArmazenada = $usuario['sen_comercio'];
            
            // Tentar verificar com password_verify (senha com hash)
            if (password_verify($senha, $senhaArmazenada)) {
                // Senha válida com hash
                $_SESSION['usuario'] = [
                    'tipo' => 'comerciante',
                    'cnpj' => $usuario['cnpj_comercio'],
                    'nome' => $usuario['nom_fantasia_comercio'],
                    'email' => $usuario['email_comercio']
                ];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login realizado com sucesso!',
                    'tipo' => 'comerciante',
                    'redirect' => 'dashboard_comerciante.php'
                ]);
            } 
            // Se não funcionar com hash, tentar comparação direta (senha em texto plano)
            else if ($senha === $senhaArmazenada) {
                // Senha válida em texto plano - vamos fazer login e atualizar para hash
                $_SESSION['usuario'] = [
                    'tipo' => 'comerciante',
                    'cnpj' => $usuario['cnpj_comercio'],
                    'nome' => $usuario['nom_fantasia_comercio'],
                    'email' => $usuario['email_comercio']
                ];
                
                // Atualizar a senha para hash
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE comercio SET sen_comercio = ? WHERE cnpj_comercio = ?");
                $updateStmt->execute([$novoHash, $documento]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login realizado com sucesso!',
                    'tipo' => 'comerciante',
                    'redirect' => 'dashboard_comerciante.php'
                ]);
            } 
            else {
                throw new Exception('Senha incorreta');
            }
            
        } else {
            throw new Exception('Tipo de usuário inválido');
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function fazerLogout() {
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Logout realizado com sucesso'
    ]);
}

function verificarSessao() {
    if (isset($_SESSION['usuario'])) {
        echo json_encode([
            'success' => true,
            'logado' => true,
            'usuario' => $_SESSION['usuario']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logado' => false
        ]);
    }
}

function recuperarSenha() {
    try {
        $db = getDB();
        
        $documento = preg_replace('/\D/', '', $_POST['documento'] ?? '');
        
        if (empty($documento)) {
            throw new Exception('Documento não informado');
        }
        
        $email = null;
        
        if (strlen($documento) === 11) {
            $stmt = $db->prepare("SELECT email_associado FROM associado WHERE cpf_associado = ?");
            $stmt->execute([$documento]);
            $result = $stmt->fetch();
            if ($result) {
                $email = $result['email_associado'];
            }
        } else if (strlen($documento) === 14) {
            $stmt = $db->prepare("SELECT email_comercio FROM comercio WHERE cnpj_comercio = ?");
            $stmt->execute([$documento]);
            $result = $stmt->fetch();
            if ($result) {
                $email = $result['email_comercio'];
            }
        }
        
        if (!$email) {
            throw new Exception('Documento não encontrado');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Instruções de recuperação enviadas para: ' . $email
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>