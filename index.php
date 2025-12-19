<?php
require_once 'config.php';

if (isset($_SESSION['usuario'])) {
    $tipo = $_SESSION['usuario']['tipo'];
    header("Location: dashboard_$tipo.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cupons - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎫</div>
            <h1>Bem-vindo!</h1>
            <p>Faça login para acessar o sistema de cupons</p>
        </div>

        <div class="user-type-selector">
            <button class="user-type-btn active" type="button" data-type="associado">
                👤 Associado
            </button>
            <button class="user-type-btn" type="button" data-type="comerciante">
                🏪 Comerciante
            </button>
        </div>

        <form id="loginForm">
            <div class="form-group" id="cpfGroup">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>
                <div class="error-message" id="cpfError">CPF inválido</div>
            </div>

            <div class="form-group" id="cnpjGroup" style="display: none;">
                <label for="cnpj">CNPJ</label>
                <input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="00.000.000/0000-00">
                <div class="error-message" id="cnpjError">CNPJ inválido</div>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
                <div class="error-message" id="senhaError">Por favor, insira sua senha</div>
            </div>

            <div class="forgot-password">
                <a href="#" id="recuperarSenhaLink">Esqueceu sua senha?</a>
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>

        <div class="divider">
            <span>ou</span>
        </div>

        <div class="register-link">
            Não possui uma conta? <a href="cadastro.php">Criar conta</a>
        </div>
    </div>

    <div class="modal" id="recuperarSenhaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔒 Recuperar Senha</h2>
                <button class="close-btn" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="success-alert" id="recuperacaoSuccess">
                    E-mail de recuperação enviado com sucesso! Verifique sua caixa de entrada.
                </div>

                <p>Informe seu CPF ou CNPJ e enviaremos instruções para recuperar sua senha por e-mail.</p>

                <form id="recuperacaoForm">
                    <div class="form-group">
                        <label for="documentoRecuperacao">CPF ou CNPJ</label>
                        <input type="text" id="documentoRecuperacao" name="documentoRecuperacao" 
                               maxlength="18" placeholder="000.000.000-00 ou 00.000.000/0000-00" required>
                        <div class="error-message" id="documentoRecuperacaoError">Documento inválido</div>
                    </div>

                    <button type="submit" class="btn">Enviar E-mail de Recuperação</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let tipoUsuario = 'associado';

        document.querySelectorAll('.user-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.user-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                tipoUsuario = this.dataset.type;

                if (tipoUsuario === 'comerciante') {
                    document.getElementById('cpfGroup').style.display = 'none';
                    document.getElementById('cnpjGroup').style.display = 'block';
                    document.getElementById('cpf').removeAttribute('required');
                    document.getElementById('cnpj').setAttribute('required', 'required');
                } else {
                    document.getElementById('cpfGroup').style.display = 'block';
                    document.getElementById('cnpjGroup').style.display = 'none';
                    document.getElementById('cpf').setAttribute('required', 'required');
                    document.getElementById('cnpj').removeAttribute('required');
                }
            });
        });


        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('cnpj').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('documentoRecuperacao').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 11) {

                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            
            e.target.value = value;
        });

        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let resto = 11 - (soma % 11);
            let digito1 = resto >= 10 ? 0 : resto;

            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += parseInt(cpf.charAt(i)) * (11 - i);
            }
            resto = 11 - (soma % 11);
            let digito2 = resto >= 10 ? 0 : resto;

            return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
        }

        function validarCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;

            let tamanho = cnpj.length - 2;
            let numeros = cnpj.substring(0, tamanho);
            let digitos = cnpj.substring(tamanho);
            let soma = 0;
            let pos = tamanho - 7;

            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }

            let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
            if (resultado != digitos.charAt(0)) return false;

            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;

            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }

            resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
            return resultado == digitos.charAt(1);
        }


        function mostrarErro(inputId, errorId, mensagem) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('error');
            error.textContent = mensagem;
            error.classList.add('show');
        }

        function removerErro(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.remove('error');
            error.classList.remove('show');
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            let valido = true;

            const senha = document.getElementById('senha').value;
            let documento = '';

            if (tipoUsuario === 'associado') {
                const cpf = document.getElementById('cpf').value;
                if (!validarCPF(cpf)) {
                    mostrarErro('cpf', 'cpfError', 'CPF inválido');
                    valido = false;
                } else {
                    removerErro('cpf', 'cpfError');
                    documento = cpf;
                }
            } else {
                const cnpj = document.getElementById('cnpj').value;
                if (!validarCNPJ(cnpj)) {
                    mostrarErro('cnpj', 'cnpjError', 'CNPJ inválido');
                    valido = false;
                } else {
                    removerErro('cnpj', 'cnpjError');
                    documento = cnpj;
                }
            }

            if (senha === '') {
                mostrarErro('senha', 'senhaError', 'Por favor, insira sua senha');
                valido = false;
            } else {
                removerErro('senha', 'senhaError');
            }

            if (valido) {
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('tipo', tipoUsuario);
                formData.append('documento', documento);
                formData.append('senha', senha);

                try {
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = result.redirect;
                    } else {
                        alert('Erro ao fazer login: ' + result.message);
                    }
                } catch (error) {
                    alert('Erro ao fazer login: ' + error.message);
                }
            }
        });

        document.getElementById('recuperarSenhaLink').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('recuperarSenhaModal').classList.add('show');
        });

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('recuperarSenhaModal').classList.remove('show');
            document.getElementById('recuperacaoForm').reset();
            document.getElementById('recuperacaoSuccess').classList.remove('show');
            removerErro('documentoRecuperacao', 'documentoRecuperacaoError');
        });

        document.getElementById('recuperarSenhaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.getElementById('recuperacaoForm').reset();
                document.getElementById('recuperacaoSuccess').classList.remove('show');
                removerErro('documentoRecuperacao', 'documentoRecuperacaoError');
            }
        });

        document.getElementById('recuperacaoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const documento = document.getElementById('documentoRecuperacao').value.replace(/\D/g, '');
            let valido = false;

            if (documento.length === 11) {
                valido = validarCPF(documento);
            } else if (documento.length === 14) {
                valido = validarCNPJ(documento);
            }

            if (!valido) {
                mostrarErro('documentoRecuperacao', 'documentoRecuperacaoError', 'CPF ou CNPJ inválido');
                return;
            } else {
                removerErro('documentoRecuperacao', 'documentoRecuperacaoError');
            }

            const formData = new FormData();
            formData.append('action', 'recuperar_senha');
            formData.append('documento', document.getElementById('documentoRecuperacao').value);

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('recuperacaoSuccess').classList.add('show');
                    document.getElementById('recuperacaoForm').reset();
                } else {
                    mostrarErro('documentoRecuperacao', 'documentoRecuperacaoError', result.message);
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        });
    </script>
</body>
</html>