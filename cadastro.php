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
    <title>Sistema de Cupons - Cadastro</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Cadastro</h1>
            <p>Crie sua conta e aproveite os descontos</p>
        </div>

        <div class="success-message" id="successMessage">
            Cadastro realizado com sucesso! Redirecionando para o login...
        </div>

        <div class="user-type-selector">
            <button class="user-type-btn active" type="button" data-type="associado">
                👤 Associado
            </button>
            <button class="user-type-btn" type="button" data-type="comerciante">
                🏪 Comerciante
            </button>
        </div>

        <form id="cadastroForm">
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" required>
                <div class="error-message" id="nomeError">Por favor, preencha o nome completo</div>
            </div>

            <div class="form-group">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="emailError">Por favor, insira um e-mail válido</div>
            </div>

            <!-- CAMPOS ASSOCIADO -->
            <div id="associadoFields">
                <div class="form-group" id="cpfGroup">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00">
                    <div class="error-message" id="cpfError">CPF inválido</div>
                </div>

                <div class="form-group">
                    <label for="dataNascimento">Data de Nascimento *</label>
                    <input type="date" id="dataNascimento" name="dataNascimento">
                    <div class="error-message" id="dataNascimentoError">Por favor, insira uma data válida</div>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço *</label>
                    <input type="text" id="endereco" name="endereco" placeholder="Rua, número">
                    <div class="error-message" id="enderecoError">Por favor, preencha o endereço</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairro">Bairro *</label>
                        <input type="text" id="bairro" name="bairro">
                        <div class="error-message" id="bairroError">Preencha o bairro</div>
                    </div>

                    <div class="form-group">
                        <label for="cep">CEP *</label>
                        <input type="text" id="cep" name="cep" maxlength="10" placeholder="00000-000">
                        <div class="error-message" id="cepError">CEP inválido</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cidade">Cidade *</label>
                        <input type="text" id="cidade" name="cidade">
                        <div class="error-message" id="cidadeError">Preencha a cidade</div>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select id="estado" name="estado">
                            <option value="">Selecione</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                        <div class="error-message" id="estadoError">Selecione o estado</div>
                    </div>
                </div>
            </div>

            <!-- CAMPOS COMERCIANTE -->
            <div id="comercianteFields" style="display: none;">
                <div class="form-group" id="cnpjGroup">
                    <label for="cnpj">CNPJ *</label>
                    <input type="text" id="cnpj" name="cnpj" maxlength="18" placeholder="00.000.000/0000-00">
                    <div class="error-message" id="cnpjError">CNPJ inválido</div>
                </div>

                <div class="form-group" id="nomeComercialGroup">
                    <label for="nomeComercial">Nome do Estabelecimento *</label>
                    <input type="text" id="nomeComercial" name="nomeComercial">
                    <div class="error-message" id="nomeComercialError">Por favor, preencha o nome do estabelecimento</div>
                </div>

                <div class="form-group" id="categoriaGroup">
                    <label for="categoria">Categoria do Estabelecimento *</label>
                    <select id="categoria" name="categoria">
                        <option value="">Selecione uma categoria</option>
                        <option value="alimentacao">🍔 Alimentação</option>
                        <option value="moda">👕 Moda e Vestuário</option>
                        <option value="saude">💊 Saúde e Beleza</option>
                        <option value="tecnologia">💻 Tecnologia</option>
                        <option value="educacao">📚 Educação</option>
                        <option value="lazer">🎮 Lazer e Entretenimento</option>
                        <option value="servicos">🔧 Serviços</option>
                        <option value="outros">📦 Outros</option>
                    </select>
                    <div class="error-message" id="categoriaError">Por favor, selecione uma categoria</div>
                </div>

                <div class="form-group">
                    <label for="enderecoCom">Endereço *</label>
                    <input type="text" id="enderecoCom" name="enderecoCom" placeholder="Rua, número">
                    <div class="error-message" id="enderecoComError">Por favor, preencha o endereço</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="bairroCom">Bairro *</label>
                        <input type="text" id="bairroCom" name="bairroCom">
                        <div class="error-message" id="bairroComError">Preencha o bairro</div>
                    </div>

                    <div class="form-group">
                        <label for="cepCom">CEP *</label>
                        <input type="text" id="cepCom" name="cepCom" maxlength="10" placeholder="00000-000">
                        <div class="error-message" id="cepComError">CEP inválido</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cidadeCom">Cidade *</label>
                        <input type="text" id="cidadeCom" name="cidadeCom">
                        <div class="error-message" id="cidadeComError">Preencha a cidade</div>
                    </div>

                    <div class="form-group">
                        <label for="estadoCom">Estado *</label>
                        <select id="estadoCom" name="estadoCom">
                            <option value="">Selecione</option>
                            <option value="AC">AC</option>
                            <option value="AL">AL</option>
                            <option value="AP">AP</option>
                            <option value="AM">AM</option>
                            <option value="BA">BA</option>
                            <option value="CE">CE</option>
                            <option value="DF">DF</option>
                            <option value="ES">ES</option>
                            <option value="GO">GO</option>
                            <option value="MA">MA</option>
                            <option value="MT">MT</option>
                            <option value="MS">MS</option>
                            <option value="MG">MG</option>
                            <option value="PA">PA</option>
                            <option value="PB">PB</option>
                            <option value="PR">PR</option>
                            <option value="PE">PE</option>
                            <option value="PI">PI</option>
                            <option value="RJ">RJ</option>
                            <option value="RN">RN</option>
                            <option value="RS">RS</option>
                            <option value="RO">RO</option>
                            <option value="RR">RR</option>
                            <option value="SC">SC</option>
                            <option value="SP">SP</option>
                            <option value="SE">SE</option>
                            <option value="TO">TO</option>
                        </select>
                        <div class="error-message" id="estadoComError">Selecione o estado</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone *</label>
                <input type="text" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000">
                <div class="error-message" id="telefoneError">Por favor, insira um telefone válido</div>
            </div>

            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" required>
                <div class="error-message" id="senhaError">A senha deve ter no mínimo 6 caracteres</div>
            </div>

            <div class="form-group">
                <label for="confirmarSenha">Confirmar Senha *</label>
                <input type="password" id="confirmarSenha" name="confirmarSenha" required>
                <div class="error-message" id="confirmarSenhaError">As senhas não coincidem</div>
            </div>

            <button type="submit" class="btn">Cadastrar</button>
        </form>

        <div class="divider">
            <span>ou</span>
        </div>

        <div class="login-link">
            Já possui uma conta? <a href="index.php">Faça login</a>
        </div>
    </div>

    <script>
        let tipoUsuario = 'associado';

        // Alternar tipo de usuário
        document.querySelectorAll('.user-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.user-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                tipoUsuario = this.dataset.type;

                if (tipoUsuario === 'comerciante') {
                    document.getElementById('associadoFields').style.display = 'none';
                    document.getElementById('comercianteFields').style.display = 'block';
                    
                    // Remover required dos campos de associado
                    document.getElementById('cpf').removeAttribute('required');
                    document.getElementById('dataNascimento').removeAttribute('required');
                    document.getElementById('endereco').removeAttribute('required');
                    document.getElementById('bairro').removeAttribute('required');
                    document.getElementById('cep').removeAttribute('required');
                    document.getElementById('cidade').removeAttribute('required');
                    document.getElementById('estado').removeAttribute('required');
                    
                    // Adicionar required aos campos de comerciante
                    document.getElementById('cnpj').setAttribute('required', 'required');
                    document.getElementById('nomeComercial').setAttribute('required', 'required');
                    document.getElementById('categoria').setAttribute('required', 'required');
                    document.getElementById('enderecoCom').setAttribute('required', 'required');
                    document.getElementById('bairroCom').setAttribute('required', 'required');
                    document.getElementById('cepCom').setAttribute('required', 'required');
                    document.getElementById('cidadeCom').setAttribute('required', 'required');
                    document.getElementById('estadoCom').setAttribute('required', 'required');
                } else {
                    document.getElementById('associadoFields').style.display = 'block';
                    document.getElementById('comercianteFields').style.display = 'none';
                    
                    // Adicionar required aos campos de associado
                    document.getElementById('cpf').setAttribute('required', 'required');
                    document.getElementById('dataNascimento').setAttribute('required', 'required');
                    document.getElementById('endereco').setAttribute('required', 'required');
                    document.getElementById('bairro').setAttribute('required', 'required');
                    document.getElementById('cep').setAttribute('required', 'required');
                    document.getElementById('cidade').setAttribute('required', 'required');
                    document.getElementById('estado').setAttribute('required', 'required');
                    
                    // Remover required dos campos de comerciante
                    document.getElementById('cnpj').removeAttribute('required');
                    document.getElementById('nomeComercial').removeAttribute('required');
                    document.getElementById('categoria').removeAttribute('required');
                    document.getElementById('enderecoCom').removeAttribute('required');
                    document.getElementById('bairroCom').removeAttribute('required');
                    document.getElementById('cepCom').removeAttribute('required');
                    document.getElementById('cidadeCom').removeAttribute('required');
                    document.getElementById('estadoCom').removeAttribute('required');
                }
            });
        });

        // Máscaras de entrada
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

        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('cep').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        document.getElementById('cepCom').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });

        // Buscar endereço pelo CEP - Associado
        document.getElementById('cep').addEventListener('blur', async function(e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    
                    if (!data.erro) {
                        document.getElementById('endereco').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                    }
                } catch (error) {
                    console.error('Erro ao buscar CEP:', error);
                }
            }
        });

        // Buscar endereço pelo CEP - Comerciante
        document.getElementById('cepCom').addEventListener('blur', async function(e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    
                    if (!data.erro) {
                        document.getElementById('enderecoCom').value = data.logradouro;
                        document.getElementById('bairroCom').value = data.bairro;
                        document.getElementById('cidadeCom').value = data.localidade;
                        document.getElementById('estadoCom').value = data.uf;
                    }
                } catch (error) {
                    console.error('Erro ao buscar CEP:', error);
                }
            }
        });

        // Funções de validação
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

        function validarEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validarData(data) {
            if (!data) return false;
            const dataObj = new Date(data);
            const hoje = new Date();
            const idade = hoje.getFullYear() - dataObj.getFullYear();
            return idade >= 16 && idade <= 120;
        }

        function mostrarErro(inputId, errorId, mensagem) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('error');
            input.classList.remove('success');
            error.textContent = mensagem;
            error.classList.add('show');
        }

        function removerErro(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.remove('error');
            input.classList.add('success');
            error.classList.remove('show');
        }

        // Submit do formulário
        document.getElementById('cadastroForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            let valido = true;

            // Validar nome
            const nome = document.getElementById('nome').value.trim();
            if (nome === '' || nome.split(' ').length < 2) {
                mostrarErro('nome', 'nomeError', 'Por favor, preencha o nome completo');
                valido = false;
            } else {
                removerErro('nome', 'nomeError');
            }

            // Validar email
            const email = document.getElementById('email').value.trim();
            if (!validarEmail(email)) {
                mostrarErro('email', 'emailError', 'Por favor, insira um e-mail válido');
                valido = false;
            } else {
                removerErro('email', 'emailError');
            }

            if (tipoUsuario === 'associado') {
                // Validar CPF
                const cpf = document.getElementById('cpf').value;
                if (!validarCPF(cpf)) {
                    mostrarErro('cpf', 'cpfError', 'CPF inválido');
                    valido = false;
                } else {
                    removerErro('cpf', 'cpfError');
                }

                // Validar data de nascimento
                const dataNascimento = document.getElementById('dataNascimento').value;
                if (!validarData(dataNascimento)) {
                    mostrarErro('dataNascimento', 'dataNascimentoError', 'Data inválida (idade mínima: 16 anos)');
                    valido = false;
                } else {
                    removerErro('dataNascimento', 'dataNascimentoError');
                }

                // Validar endereço
                const endereco = document.getElementById('endereco').value.trim();
                if (endereco === '') {
                    mostrarErro('endereco', 'enderecoError', 'Por favor, preencha o endereço');
                    valido = false;
                } else {
                    removerErro('endereco', 'enderecoError');
                }

                // Validar bairro
                const bairro = document.getElementById('bairro').value.trim();
                if (bairro === '') {
                    mostrarErro('bairro', 'bairroError', 'Por favor, preencha o bairro');
                    valido = false;
                } else {
                    removerErro('bairro', 'bairroError');
                }

                // Validar CEP
                const cep = document.getElementById('cep').value.replace(/\D/g, '');
                if (cep.length !== 8) {
                    mostrarErro('cep', 'cepError', 'CEP inválido');
                    valido = false;
                } else {
                    removerErro('cep', 'cepError');
                }

                // Validar cidade
                const cidade = document.getElementById('cidade').value.trim();
                if (cidade === '') {
                    mostrarErro('cidade', 'cidadeError', 'Por favor, preencha a cidade');
                    valido = false;
                } else {
                    removerErro('cidade', 'cidadeError');
                }

                // Validar estado
                const estado = document.getElementById('estado').value;
                if (estado === '') {
                    mostrarErro('estado', 'estadoError', 'Por favor, selecione o estado');
                    valido = false;
                } else {
                    removerErro('estado', 'estadoError');
                }
            } else {
                // Validar CNPJ
                const cnpj = document.getElementById('cnpj').value;
                if (!validarCNPJ(cnpj)) {
                    mostrarErro('cnpj', 'cnpjError', 'CNPJ inválido');
                    valido = false;
                } else {
                    removerErro('cnpj', 'cnpjError');
                }

                // Validar nome comercial
                const nomeComercial = document.getElementById('nomeComercial').value.trim();
                if (nomeComercial === '') {
                    mostrarErro('nomeComercial', 'nomeComercialError', 'Por favor, preencha o nome do estabelecimento');
                    valido = false;
                } else {
                    removerErro('nomeComercial', 'nomeComercialError');
                }

                // Validar categoria
                const categoria = document.getElementById('categoria').value;
                if (categoria === '') {
                    mostrarErro('categoria', 'categoriaError', 'Por favor, selecione uma categoria');
                    valido = false;
                } else {
                    removerErro('categoria', 'categoriaError');
                }

                // Validar endereço
                const enderecoCom = document.getElementById('enderecoCom').value.trim();
                if (enderecoCom === '') {
                    mostrarErro('enderecoCom', 'enderecoComError', 'Por favor, preencha o endereço');
                    valido = false;
                } else {
                    removerErro('enderecoCom', 'enderecoComError');
                }

                // Validar bairro
                const bairroCom = document.getElementById('bairroCom').value.trim();
                if (bairroCom === '') {
                    mostrarErro('bairroCom', 'bairroComError', 'Por favor, preencha o bairro');
                    valido = false;
                } else {
                    removerErro('bairroCom', 'bairroComError');
                }

                // Validar CEP
                const cepCom = document.getElementById('cepCom').value.replace(/\D/g, '');
                if (cepCom.length !== 8) {
                    mostrarErro('cepCom', 'cepComError', 'CEP inválido');
                    valido = false;
                } else {
                    removerErro('cepCom', 'cepComError');
                }

                // Validar cidade
                const cidadeCom = document.getElementById('cidadeCom').value.trim();
                if (cidadeCom === '') {
                    mostrarErro('cidadeCom', 'cidadeComError', 'Por favor, preencha a cidade');
                    valido = false;
                } else {
                    removerErro('cidadeCom', 'cidadeComError');
                }

                // Validar estado
                const estadoCom = document.getElementById('estadoCom').value;
                if (estadoCom === '') {
                    mostrarErro('estadoCom', 'estadoComError', 'Por favor, selecione o estado');
                    valido = false;
                } else {
                    removerErro('estadoCom', 'estadoComError');
                }
            }

            // Validar telefone
            const telefone = document.getElementById('telefone').value.replace(/\D/g, '');
            if (telefone.length < 10) {
                mostrarErro('telefone', 'telefoneError', 'Por favor, insira um telefone válido');
                valido = false;
            } else {
                removerErro('telefone', 'telefoneError');
            }

            // Validar senha
            const senha = document.getElementById('senha').value;
            if (senha.length < 6) {
                mostrarErro('senha', 'senhaError', 'A senha deve ter no mínimo 6 caracteres');
                valido = false;
            } else {
                removerErro('senha', 'senhaError');
            }

            // Validar confirmação de senha
            const confirmarSenha = document.getElementById('confirmarSenha').value;
            if (senha !== confirmarSenha) {
                mostrarErro('confirmarSenha', 'confirmarSenhaError', 'As senhas não coincidem');
                valido = false;
            } else {
                removerErro('confirmarSenha', 'confirmarSenhaError');
            }

            if (valido) {
                const formData = new FormData();
                formData.append('action', 'cadastro');
                formData.append('tipo', tipoUsuario);
                formData.append('nome', nome);
                formData.append('email', email);
                formData.append('telefone', document.getElementById('telefone').value);
                formData.append('senha', senha);

                if (tipoUsuario === 'associado') {
                    formData.append('cpf', document.getElementById('cpf').value);
                    formData.append('dataNascimento', document.getElementById('dataNascimento').value);
                    formData.append('endereco', document.getElementById('endereco').value);
                    formData.append('bairro', document.getElementById('bairro').value);
                    formData.append('cep', document.getElementById('cep').value);
                    formData.append('cidade', document.getElementById('cidade').value);
                    formData.append('estado', document.getElementById('estado').value);
                } else {
                    formData.append('cnpj', document.getElementById('cnpj').value);
                    formData.append('nomeComercial', document.getElementById('nomeComercial').value);
                    formData.append('categoria', document.getElementById('categoria').value);
                    formData.append('endereco', document.getElementById('enderecoCom').value);
                    formData.append('bairro', document.getElementById('bairroCom').value);
                    formData.append('cep', document.getElementById('cepCom').value);
                    formData.append('cidade', document.getElementById('cidadeCom').value);
                    formData.append('estado', document.getElementById('estadoCom').value);
                }

                try {
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('successMessage').classList.add('show');
                        document.getElementById('cadastroForm').reset();

                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 2000);
                    } else {
                        alert('Erro ao cadastrar: ' + result.message);
                    }
                } catch (error) {
                    alert('Erro ao cadastrar: ' + error.message);
                }
            }
        });
    </script>
</body>
</html>