<?php

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'cadastrar':
        cadastrarPromocao();
        break;
    case 'buscar_disponiveis':
        buscarCuponsDisponiveis();
        break;
    case 'reservar':
        reservarCupom();
        break;
    case 'meus_cupons':
        meusCupons();
        break;
    case 'validar':
        validarCupom();
        break;
    case 'utilizar':
        utilizarCupom();
        break;
    case 'consultar':
        consultarCupons();
        break;
    case 'minhas_promocoes':
        minhasPromocoes();
        break;
    case 'historico':
        historico();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

function cadastrarPromocao() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Apenas comerciantes podem cadastrar promoções');
        }
        
        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = $_POST['categoria'] ?? '';
        $dataInicio = $_POST['dataInicio'] ?? '';
        $dataFim = $_POST['dataFim'] ?? '';
        $desconto = floatval($_POST['desconto'] ?? 0);
        $quantidade = intval($_POST['quantidade'] ?? 0);
        
        // Validações
        if (empty($titulo) || empty($dataInicio) || empty($dataFim)) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
        }
        
        if ($desconto <= 0 || $desconto > 100) {
            throw new Exception('Desconto deve estar entre 1% e 100%');
        }
        
        if ($quantidade <= 0 || $quantidade > 1000) {
            throw new Exception('Quantidade deve estar entre 1 e 1000');
        }
        
        if (strtotime($dataFim) < strtotime($dataInicio)) {
            throw new Exception('Data de término deve ser posterior à data de início');
        }
        
        $db->beginTransaction();
        
        $cuponsGerados = [];
        for ($i = 0; $i < $quantidade; $i++) {
            $codigo = gerarCodigoUnico($db);
            
            $stmt = $db->prepare("
                INSERT INTO cupom (num_cupom, tit_cupom, cnpj_comercio, dta_emissao_cupom, 
                                  dta_inicio_cupom, dta_termino_cupom, per_desc_cupom)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ");
            $stmt->execute([
                $codigo,
                $titulo,
                $usuario['cnpj'],
                $dataInicio,
                $dataFim,
                $desconto
            ]);
            
            $cuponsGerados[] = $codigo;
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Promoção cadastrada com sucesso! {$quantidade} cupons gerados.",
            'cupons' => $cuponsGerados
        ]);
        
    } catch(Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function gerarCodigoUnico($db) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $tentativas = 0;
    $maxTentativas = 10;
    
    do {
        $codigo = '';
        for ($i = 0; $i < 12; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        
        $stmt = $db->prepare("SELECT num_cupom FROM cupom WHERE num_cupom = ?");
        $stmt->execute([$codigo]);
        
        if (!$stmt->fetch()) {
            return $codigo;
        }
        
        $tentativas++;
    } while ($tentativas < $maxTentativas);
    
    throw new Exception('Não foi possível gerar código único');
}

function buscarCuponsDisponiveis() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'associado') {
            throw new Exception('Acesso negado');
        }
        
        $categoria = $_GET['categoria'] ?? '';
        
        $sql = "
            SELECT 
                c.num_cupom,
                c.tit_cupom,
                c.per_desc_cupom,
                c.dta_inicio_cupom,
                c.dta_termino_cupom,
                co.nom_fantasia_comercio,
                co.cnpj_comercio,
                cat.nom_categoria,
                CASE 
                    WHEN ca.num_cupom IS NULL THEN 'Disponível'
                    ELSE 'Reservado'
                END AS status_cupom
            FROM cupom c
            INNER JOIN comercio co ON c.cnpj_comercio = co.cnpj_comercio
            INNER JOIN categoria cat ON co.id_categoria = cat.id_categoria
            LEFT JOIN cupom_associado ca ON c.num_cupom = ca.num_cupom
            WHERE c.dta_termino_cupom >= CURDATE()
            AND ca.num_cupom IS NULL
        ";
        
        if (!empty($categoria)) {
            $sql .= " AND cat.nom_categoria = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$categoria]);
        } else {
            $stmt = $db->query($sql);
        }
        
        $cupons = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'cupons' => $cupons
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function reservarCupom() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'associado') {
            throw new Exception('Apenas associados podem reservar cupons');
        }
        
        // CORREÇÃO AQUI: De $_POST['codigo'] para $_POST['numCupom']
        $codigo = trim($_POST['numCupom'] ?? '');
        $cpf = $usuario['cpf'];

        if (empty($codigo) || empty($cpf)) {
            throw new Exception('Dados inválidos para a reserva.');
        }

        // 1. Verificar se o cupom já está reservado ou usado pelo associado
        $stmtCheck = $db->prepare("
            SELECT num_cupom 
            FROM cupom_associado 
            WHERE num_cupom = ? AND cpf_associado = ?
        ");
        $stmtCheck->execute([$codigo, $cpf]);
        if ($stmtCheck->fetch()) {
            throw new Exception('Você já possui este cupom reservado ou ele já foi utilizado.');
        }

        // 2. Tentar inserir na tabela cupom_associado
        // A data de reserva (dta_cupom_associado) será a data atual.
        // O campo dta_uso_cupom_associado deve ser NULL.
        $stmtInsert = $db->prepare("
            INSERT INTO cupom_associado (num_cupom, cpf_associado, dta_cupom_associado, dta_uso_cupom_associado)
            VALUES (?, ?, NOW(), NULL)
        ");
        $success = $stmtInsert->execute([$codigo, $cpf]);

        if (!$success) {
            throw new Exception('Não foi possível registrar a reserva do cupom.');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cupom reservado com sucesso!'
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function meusCupons() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'associado') {
            throw new Exception('Acesso negado');
        }
        
        $filtro = $_GET['filtro'] ?? 'ativos';
        
        $sql = "
            SELECT 
                ca.id_cupom_associado,
                ca.num_cupom,
                c.tit_cupom,
                c.per_desc_cupom,
                c.dta_inicio_cupom,
                c.dta_termino_cupom,
                co.nom_fantasia_comercio,
                cat.nom_categoria,
                ca.dta_cupom_associado AS dta_reserva,
                ca.dta_uso_cupom_associado AS dta_uso,
                CASE 
                    WHEN ca.dta_uso_cupom_associado IS NOT NULL THEN 'utilizados'
                    WHEN c.dta_termino_cupom < CURDATE() THEN 'vencidos'
                    ELSE 'ativos'
                END AS status_cupom
            FROM cupom_associado ca
            INNER JOIN cupom c ON ca.num_cupom = c.num_cupom
            INNER JOIN comercio co ON c.cnpj_comercio = co.cnpj_comercio
            INNER JOIN categoria cat ON co.id_categoria = cat.id_categoria
            WHERE ca.cpf_associado = ?
        ";
        
        if ($filtro === 'ativos') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NULL AND c.dta_termino_cupom >= CURDATE()";
        } else if ($filtro === 'utilizados') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NOT NULL";
        } else if ($filtro === 'vencidos') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NULL AND c.dta_termino_cupom < CURDATE()";
        }
        
        $sql .= " ORDER BY c.dta_inicio_cupom DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$usuario['cpf']]);
        $cupons = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'cupons' => $cupons
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function validarCupom() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Apenas comerciantes podem validar cupons');
        }
        
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        
        if (empty($codigo) || strlen($codigo) !== 12) {
            throw new Exception('Código inválido');
        }
        
        $stmt = $db->prepare("
            SELECT 
                c.num_cupom,
                c.tit_cupom,
                c.per_desc_cupom,
                c.dta_inicio_cupom,
                c.dta_termino_cupom,
                c.cnpj_comercio,
                co.nom_fantasia_comercio,
                ca.cpf_associado,
                ca.dta_cupom_associado,
                ca.dta_uso_cupom_associado,
                a.nom_associado,
                a.email_associado
            FROM cupom c
            INNER JOIN comercio co ON c.cnpj_comercio = co.cnpj_comercio
            LEFT JOIN cupom_associado ca ON c.num_cupom = ca.num_cupom
            LEFT JOIN associado a ON ca.cpf_associado = a.cpf_associado
            WHERE c.num_cupom = ?
        ");
        $stmt->execute([$codigo]);
        $cupom = $stmt->fetch();
        
        if (!$cupom) {
            throw new Exception('Cupom não encontrado');
        }
        
        if ($cupom['cnpj_comercio'] !== $usuario['cnpj']) {
            throw new Exception('Este cupom não pertence ao seu estabelecimento');
        }
        
        if (!$cupom['cpf_associado']) {
            throw new Exception('Este cupom ainda não foi reservado');
        }
        
        if ($cupom['dta_uso_cupom_associado']) {
            throw new Exception('Este cupom já foi utilizado em ' . 
                date('d/m/Y H:i', strtotime($cupom['dta_uso_cupom_associado'])));
        }
        
        if (strtotime($cupom['dta_termino_cupom']) < time()) {
            throw new Exception('Este cupom está vencido');
        }
        
        echo json_encode([
            'success' => true,
            'cupom' => $cupom,
            'message' => 'Cupom válido'
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function utilizarCupom() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        // 1. Verificar Permissão
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Apenas comerciantes podem utilizar cupons.');
        }

        $codigo = trim($_POST['codigo'] ?? ''); // O frontend deve enviar o código como 'codigo'
        $cnpjComerciante = $usuario['cnpj'];

        if (empty($codigo)) {
            throw new Exception('Código do cupom não informado.');
        }

        // 2. Tentativa Atômica de Utilização (UPDATE)
        // Tenta atualizar a data de uso (dta_uso_cupom_associado) para NOW()
        // A atualização só é bem-sucedida se:
        // a) O cupom existe e está reservado (ca.num_cupom = ?).
        // b) O cupom ainda NÃO foi utilizado (ca.dta_uso_cupom_associado IS NULL).
        // c) O cupom pertence ao comerciante logado (c.cnpj_comercio = ?).
        $stmt = $db->prepare("
            UPDATE cupom_associado ca
            INNER JOIN cupom c ON ca.num_cupom = c.num_cupom
            SET ca.dta_uso_cupom_associado = NOW()
            WHERE 
                ca.num_cupom = ? AND
                c.cnpj_comercio = ? AND
                ca.dta_uso_cupom_associado IS NULL
        ");
        
        $stmt->execute([$codigo, $cnpjComerciante]);
        $rowsAffected = $stmt->rowCount();

        // 3. Checagem de Sucesso e Feedback de Erro Específico
        if ($rowsAffected === 0) {
            // Se o UPDATE não afetou linhas, algo falhou. Vamos checar o motivo para dar feedback.
            $stmtCheck = $db->prepare("
                SELECT c.cnpj_comercio, ca.dta_uso_cupom_associado
                FROM cupom_associado ca
                INNER JOIN cupom c ON ca.num_cupom = c.num_cupom
                WHERE ca.num_cupom = ?
            ");
            $stmtCheck->execute([$codigo]);
            $result = $stmtCheck->fetch();

            if (!$result) {
                // Cupom não encontrado ou não reservado
                throw new Exception('Código de cupom inválido ou ainda não reservado por um associado.');
            }

            if ($result['cnpj_comercio'] !== $cnpjComerciante) {
                // Encontrado, mas pertence a outro comerciante
                throw new Exception('Este cupom não pertence ao seu estabelecimento.');
            }

            if ($result['dta_uso_cupom_associado'] !== NULL) {
                // Encontrado, pertence, mas já foi usado
                $dataUso = date('d/m/Y H:i', strtotime($result['dta_uso_cupom_associado']));
                throw new Exception("Este cupom já foi utilizado em $dataUso.");
            }

             // Caso fallback para erro desconhecido (improvável)
            throw new Exception('Falha desconhecida ao utilizar o cupom. Tente novamente.');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cupom utilizado com sucesso! Desconto aplicado.'
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function consultarCupons() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Acesso negado');
        }
        
        $filtro = $_GET['filtro'] ?? 'ativos';
        
        $sql = "
            SELECT 
                c.num_cupom,
                c.tit_cupom,
                c.per_desc_cupom,
                c.dta_inicio_cupom,
                c.dta_termino_cupom,
                ca.cpf_associado,
                ca.dta_cupom_associado,
                ca.dta_uso_cupom_associado,
                a.nom_associado,
                a.email_associado,
                CASE 
                    WHEN ca.dta_uso_cupom_associado IS NOT NULL THEN 'utilizados'
                    WHEN c.dta_termino_cupom < CURDATE() THEN 'vencidos'
                    WHEN ca.cpf_associado IS NOT NULL THEN 'ativos'
                    ELSE 'disponivel'
                END AS status_cupom
            FROM cupom c
            LEFT JOIN cupom_associado ca ON c.num_cupom = ca.num_cupom
            LEFT JOIN associado a ON ca.cpf_associado = a.cpf_associado
            WHERE c.cnpj_comercio = ?
        ";
        
        if ($filtro === 'ativos') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NULL 
                     AND c.dta_termino_cupom >= CURDATE()
                     AND ca.cpf_associado IS NOT NULL";
        } else if ($filtro === 'utilizados') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NOT NULL";
        } else if ($filtro === 'vencidos') {
            $sql .= " AND ca.dta_uso_cupom_associado IS NULL 
                     AND c.dta_termino_cupom < CURDATE()
                     AND ca.cpf_associado IS NOT NULL";
        }
        
        $sql .= " ORDER BY c.dta_inicio_cupom DESC, c.tit_cupom";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$usuario['cnpj']]);
        $cupons = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'cupons' => $cupons
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function minhasPromocoes() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Acesso negado');
        }
        
        $stmt = $db->prepare("
            SELECT 
                c.tit_cupom AS titulo,
                c.per_desc_cupom AS desconto,
                c.dta_emissao_cupom,
                c.dta_inicio_cupom,
                c.dta_termino_cupom,
                COUNT(c.num_cupom) AS quantidade,
                COUNT(ca.num_cupom) AS cupons_reservados,
                COUNT(CASE WHEN ca.dta_uso_cupom_associado IS NOT NULL THEN 1 END) AS cupons_usados
            FROM cupom c
            LEFT JOIN cupom_associado ca ON c.num_cupom = ca.num_cupom
            WHERE c.cnpj_comercio = ?
            GROUP BY c.tit_cupom, c.per_desc_cupom, c.dta_emissao_cupom, 
                     c.dta_inicio_cupom, c.dta_termino_cupom
            ORDER BY c.dta_inicio_cupom DESC
        ");
        $stmt->execute([$usuario['cnpj']]);
        $promocoes = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'promocoes' => $promocoes
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function historico() {
    try {
        $db = getDB();
        $usuario = $_SESSION['usuario'];
        
        if ($usuario['tipo'] !== 'comerciante') {
            throw new Exception('Acesso negado');
        }
        
        $stmt = $db->prepare("
            SELECT 
                c.num_cupom,
                c.tit_cupom,
                c.per_desc_cupom,
                ca.dta_cupom_associado,
                ca.dta_uso_cupom_associado,
                a.nom_associado,
                a.email_associado
            FROM cupom c
            INNER JOIN cupom_associado ca ON c.num_cupom = ca.num_cupom
            INNER JOIN associado a ON ca.cpf_associado = a.cpf_associado
            WHERE c.cnpj_comercio = ?
            AND ca.dta_uso_cupom_associado IS NOT NULL
            ORDER BY ca.dta_uso_cupom_associado DESC
        ");
        $stmt->execute([$usuario['cnpj']]);
        $historico = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'historico' => $historico
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>