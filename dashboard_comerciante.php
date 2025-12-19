<?php
require_once 'config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'comerciante') {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cupons - Dashboard Comerciante</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="dashboard-header">
            <div class="user-info">
                <h2 id="nomeComercial">🏪 <?php echo htmlspecialchars($usuario['nome']); ?></h2>
                <p id="emailComercial"><?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>
            <button class="logout-btn" onclick="logout()">Sair</button>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="trocarAba('validar')">
                ✅ Validar Cupom
            </button>
            <button class="tab-btn" onclick="trocarAba('cadastrar')">
                🎫 Cadastrar Cupom
            </button>
            <button class="tab-btn" onclick="trocarAba('consultar')">
                🔍 Consultar Cupons
            </button>
            <button class="tab-btn" onclick="trocarAba('promocoes')">
                📋 Minhas Promoções
            </button>
            <button class="tab-btn" onclick="trocarAba('historico')">
                📊 Histórico de Uso
            </button>
        </div>

        <div id="tab-validar" class="tab-content active">
            <div class="header">
                <h1>✅ Utilizar Cupom</h1>
                <p>Insira o código do cupom apresentado pelo associado para registrar o uso.</p>
            </div>

            <div class="validacao-section">
                <div class="codigo-input-group">
                    <input type="text" 
                        id="codigoUtilizar"  class="codigo-input" 
                        placeholder="Código do Cupom (Ex: CPN1234)" 
                        maxlength="12">
                        
                    <button class="btn-validar" 
                            onclick="utilizarCupomDoFrontend()"> ✅ Registrar Uso
                    </button>
                </div>
                <div class="info-box">
                    ℹ️ Digite o código do cupom para uso e registro.
                </div>
            </div>

            <div id="cupomValidadoContainer"></div>
        </div>

        <div id="tab-cadastrar" class="tab-content">
            <div class="header">
                <h1>🎫 Cadastrar Cupom</h1>
                <p>Crie promoções e gere cupons de desconto</p>
            </div>

            <div class="success-message" id="successMessage">
                Cupons gerados com sucesso!
            </div>

            <form id="cadastroCupomForm">
                <div class="form-group">
                    <label for="titulo">Título da Promoção *</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Ex: Black Friday 2024" required>
                    <div class="error-message" id="tituloError">Por favor, preencha o título da promoção</div>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria *</label>
                    <select id="categoria" name="categoria" required>
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

                <div class="date-group">
                    <div class="form-group">
                        <label for="dataInicio">Data de Início *</label>
                        <input type="date" id="dataInicio" name="dataInicio" required>
                        <div class="error-message" id="dataInicioError">Data inválida</div>
                    </div>

                    <div class="form-group">
                        <label for="dataFim">Data de Fim *</label>
                        <input type="date" id="dataFim" name="dataFim" required>
                        <div class="error-message" id="dataFimError">Data inválida</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="desconto">Percentual de Desconto (%) *</label>
                    <input type="number" id="desconto" name="desconto" min="1" max="100" step="0.01" placeholder="Ex: 15" required>
                    <div class="error-message" id="descontoError">Insira um desconto entre 1% e 100%</div>
                </div>

                <div class="form-group">
                    <label for="quantidade">Quantidade de Cupons *</label>
                    <input type="number" id="quantidade" name="quantidade" min="1" max="1000" placeholder="Ex: 100" required>
                    <div class="error-message" id="quantidadeError">Insira uma quantidade entre 1 e 1000</div>
                </div>

                <div class="info-box">
                    ℹ️ Serão gerados <strong id="quantidadePreview">0</strong> cupons com código hash único de 12 caracteres. A data de emissão será a data atual.
                </div>

                <button type="submit" class="btn">Gerar Cupons</button>
            </form>
        </div>

        <div id="tab-consultar" class="tab-content">
            <div class="header">
                <h1>🔍 Consultar Cupons</h1>
                <p>Visualize e filtre todos os cupons disponibilizados</p>
            </div>

            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filtrarCupons('ativos')" data-filter="ativos">
                        ✅ Cupons Ativos
                    </button>
                    <button class="filter-btn" onclick="filtrarCupons('utilizados')" data-filter="utilizados">
                        ✓ Cupons Utilizados
                    </button>
                    <button class="filter-btn" onclick="filtrarCupons('vencidos')" data-filter="vencidos">
                        ⏰ Cupons Vencidos
                    </button>
                </div>
            </div>

            <div class="cupons-consulta">
                <div id="consultaCuponsContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔍</div>
                        <p>Carregando cupons...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-promocoes" class="tab-content">
            <div class="header">
                <h1>📋 Minhas Promoções</h1>
                <p>Promoções cadastradas</p>
            </div>

            <div class="cupons-list">
                <div id="promocoesContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔭</div>
                        <p>Nenhuma promoção cadastrada ainda</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-historico" class="tab-content">
            <div class="header">
                <h1>📊 Histórico de Uso</h1>
                <p>Cupons utilizados</p>
            </div>

            <div class="historico-uso">
                <div id="historicoContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔭</div>
                        <p>Nenhum cupom foi utilizado ainda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        async function logout() {
            if (confirm('Deseja realmente sair?')) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'logout');
                    
                    await fetch('auth.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    window.location.href = 'index.php';
                } catch (error) {
                    console.error('Erro ao fazer logout:', error);
                    window.location.href = 'index.php';
                }
            }
        }

        function trocarAba(aba) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            const tabButtons = {
                'validar': 0,
                'cadastrar': 1,
                'consultar': 2,
                'promocoes': 3,
                'historico': 4
            };
            
            document.querySelectorAll('.tab-btn')[tabButtons[aba]].classList.add('active');
            document.getElementById('tab-' + aba).classList.add('active');

            if (aba === 'promocoes') {
                carregarPromocoes();
            } else if (aba === 'historico') {
                carregarHistorico();
            } else if (aba === 'validar') {
                document.getElementById('codigoCupom').value = '';
                document.getElementById('cupomValidadoContainer').innerHTML = '';
            } else if (aba === 'consultar') {
                filtrarCupons('ativos');
            }
        }

        document.getElementById('codigoCupom').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        async function validarCupom() {
            const codigo = document.getElementById('codigoCupom').value.trim();
            const container = document.getElementById('cupomValidadoContainer');

            if (codigo === '') {
                container.innerHTML = `<div class="alert-box alert-error">❌ Por favor, digite um código de cupom</div>`;
                return;
            }

            if (codigo.length !== 12) {
                container.innerHTML = `<div class="alert-box alert-error">❌ O código deve ter exatamente 12 caracteres</div>`;
                return;
            }

            container.innerHTML = '<div class="alert-box" style="background: #e7f3ff; color: #333;">⏳ Validando cupom...</div>';

            try {
                const formData = new FormData();
                formData.append('action', 'validar');
                formData.append('codigo', codigo);

                const response = await fetch('cupons.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    container.innerHTML = `<div class="alert-box alert-error">❌ ${result.message}</div>`;
                    return;
                }

                const cupom = result.cupom;
                const dataFim = new Date(cupom.dta_termino_cupom).toLocaleDateString('pt-BR');
                const dataReserva = new Date(cupom.dta_cupom_associado).toLocaleDateString('pt-BR');

                container.innerHTML = `
                    <div class="cupom-validado">
                        <div class="cupom-header">
                            <div><div class="cupom-title">${cupom.tit_cupom}</div></div>
                            <span class="status-badge status-ativo">✓ Válido</span>
                        </div>
                        <div class="cupom-info-destaque">
                            <div class="desconto-valor">${cupom.per_desc_cupom}%</div>
                            <div class="desconto-label">DE DESCONTO</div>
                        </div>
                        <div class="codigo-cupom">
                            <div class="codigo-cupom-label">CÓDIGO DO CUPOM</div>
                            <div class="codigo-cupom-valor">${cupom.num_cupom}</div>
                        </div>
                        <div class="associado-info">
                            <strong>👤 Associado:</strong>
                            <p>${cupom.nom_associado || 'Não identificado'}</p>
                            <p>${cupom.email_associado}</p>
                        </div>
                        <div class="cupom-details">
                            <div class="cupom-detail">
                                <strong>📅 VÁLIDO ATÉ</strong>
                                <span>${dataFim}</span>
                            </div>
                            <div class="cupom-detail">
                                <strong>📌 RESERVADO EM</strong>
                                <span>${dataReserva}</span>
                            </div>
                        </div>
                        <button class="btn-confirmar-uso" onclick="confirmarUsoCupom('${codigo}')">✅ Confirmar Uso do Cupom</button>
                        <button class="btn-cancelar" onclick="document.getElementById('cupomValidadoContainer').innerHTML = ''; document.getElementById('codigoCupom').value = '';">Cancelar</button>
                    </div>
                `;

            } catch (error) {
                container.innerHTML = `<div class="alert-box alert-error">❌ Erro: ${error.message}</div>`;
            }
        }

        async function confirmarUsoCupom(codigo) {
            if (!confirm('Confirma o uso deste cupom?\n\nEsta ação não pode ser desfeita.')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'utilizar');
                formData.append('codigo', codigo);

                const response = await fetch('cupons.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Cupom utilizado com sucesso!');
                    document.getElementById('cupomValidadoContainer').innerHTML = '';
                    document.getElementById('codigoCupom').value = '';
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }

        async function filtrarCupons(filtro) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-filter="${filtro}"]`).classList.add('active');

            const container = document.getElementById('consultaCuponsContainer');
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Carregando...</p></div>';

            try {
                const response = await fetch(`cupons.php?action=consultar&filtro=${filtro}`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const cupons = result.cupons;

                if (cupons.length === 0) {
                    const mensagens = {
                        'ativos': 'Nenhum cupom ativo no momento',
                        'utilizados': 'Nenhum cupom foi utilizado ainda',
                        'vencidos': 'Nenhum cupom vencido'
                    };
                    
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <p>${mensagens[filtro]}</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = cupons.map(cupom => {
                    const dataReserva = cupom.dta_cupom_associado ? new Date(cupom.dta_cupom_associado).toLocaleDateString('pt-BR') : '-';
                    const dataUso = cupom.dta_uso_cupom_associado ? new Date(cupom.dta_uso_cupom_associado).toLocaleString('pt-BR') : '-';
                    const dataInicio = new Date(cupom.dta_inicio_cupom).toLocaleDateString('pt-BR');
                    const dataFim = new Date(cupom.dta_termino_cupom).toLocaleDateString('pt-BR');

                    let statusBadgeClass = '';
                    let statusTexto = '';
                    let borderClass = '';

                    if (filtro === 'ativos') {
                        statusBadgeClass = 'status-ativo';
                        statusTexto = '✓ Ativo';
                        borderClass = 'cupom-reservado';
                    } else if (filtro === 'utilizados') {
                        statusBadgeClass = 'status-usado';
                        statusTexto = '✓ Utilizado';
                        borderClass = 'cupom-usado';
                    } else {
                        statusBadgeClass = 'status-expirado';
                        statusTexto = '⏰ Vencido';
                        borderClass = 'cupom-vencido';
                    }

                    return `
                        <div class="${borderClass}">
                            <div class="cupom-header">
                                <div>
                                    <div class="cupom-title">${cupom.tit_cupom}</div>
                                    <div class="cupom-comerciante" style="color: #999; font-size: 12px; margin-top: 3px;">
                                        📅 ${dataInicio} até ${dataFim}
                                    </div>
                                </div>
                                <div>
                                    <div class="cupom-badge">${cupom.per_desc_cupom}% OFF</div>
                                    <span class="status-badge ${statusBadgeClass}" style="display: block; margin-top: 8px;">
                                        ${statusTexto}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="codigo-cupom">
                                <div class="codigo-cupom-label">CÓDIGO DO CUPOM</div>
                                <div class="codigo-cupom-valor">${cupom.num_cupom}</div>
                            </div>

                            ${cupom.cpf_associado ? `
                                <div class="associado-info">
                                    <strong>👤 Associado:</strong>
                                    <p>${cupom.nom_associado || 'Não identificado'}</p>
                                    <p>${cupom.email_associado}</p>
                                </div>
                            ` : ''}

                            <div class="cupom-details">
                                <div class="cupom-detail">
                                    <strong>📌 RESERVADO EM</strong>
                                    <span>${dataReserva}</span>
                                </div>
                                ${filtro === 'utilizados' ? `
                                    <div class="cupom-detail">
                                        <strong>✅ UTILIZADO EM</strong>
                                        <span>${dataUso}</span>
                                    </div>
                                ` : ''}
                                <div class="cupom-detail">
                                    <strong>💰 DESCONTO</strong>
                                    <span>${cupom.per_desc_cupom}%</span>
                                </div>
                            </div>

                            ${filtro === 'ativos' && !cupom.dta_uso_cupom_associado ? `
                                <button class="btn-confirmar-uso" onclick="confirmarUsoDireto('${cupom.num_cupom}')">
                                    ✅ Registrar Uso do Cupom
                                </button>
                            ` : ''}
                        </div>
                    `;
                }).join('');

            } catch (error) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>Erro: ${error.message}</p>
                    </div>
                `;
            }
        }

        document.getElementById('cadastroCupomForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            let valido = true;

            const titulo = document.getElementById('titulo').value.trim();
            if (titulo === '') {
                mostrarErro('titulo', 'tituloError', 'Por favor, preencha o título da promoção');
                valido = false;
            } else {
                removerErro('titulo', 'tituloError');
            }

            const categoria = document.getElementById('categoria').value;
            if (categoria === '') {
                mostrarErro('categoria', 'categoriaError', 'Por favor, selecione uma categoria');
                valido = false;
            } else {
                removerErro('categoria', 'categoriaError');
            }

            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            const hoje = new Date().toISOString().split('T')[0];

            if (!dataInicio || dataInicio < hoje) {
                mostrarErro('dataInicio', 'dataInicioError', 'A data de início deve ser hoje ou futura');
                valido = false;
            } else {
                removerErro('dataInicio', 'dataInicioError');
            }

            if (!dataFim || dataFim < dataInicio) {
                mostrarErro('dataFim', 'dataFimError', 'A data de fim deve ser igual ou posterior à data de início');
                valido = false;
            } else {
                removerErro('dataFim', 'dataFimError');
            }

            const desconto = parseFloat(document.getElementById('desconto').value);
            if (isNaN(desconto) || desconto < 1 || desconto > 100) {
                mostrarErro('desconto', 'descontoError', 'Insira um desconto entre 1% e 100%');
                valido = false;
            } else {
                removerErro('desconto', 'descontoError');
            }

            const quantidade = parseInt(document.getElementById('quantidade').value);
            if (isNaN(quantidade) || quantidade < 1 || quantidade > 1000) {
                mostrarErro('quantidade', 'quantidadeError', 'Insira uma quantidade entre 1 e 1000');
                valido = false;
            } else {
                removerErro('quantidade', 'quantidadeError');
            }

            if (valido) {
                const formData = new FormData();
                formData.append('action', 'cadastrar');
                formData.append('titulo', titulo);
                formData.append('categoria', categoria);
                formData.append('dataInicio', dataInicio);
                formData.append('dataFim', dataFim);
                formData.append('desconto', desconto);
                formData.append('quantidade', quantidade);

                try {
                    const response = await fetch('cupons.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('successMessage').classList.add('show');
                        document.getElementById('cadastroCupomForm').reset();
                        document.getElementById('quantidadePreview').textContent = '0';

                        setTimeout(() => {
                            document.getElementById('successMessage').classList.remove('show');
                        }, 3000);

                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        alert('Erro: ' + result.message);
                    }
                } catch (error) {
                    alert('Erro: ' + error.message);
                }
            }
        });

        // Confirmar uso direto da consulta
        async function confirmarUsoDireto(codigo) {
            if (!confirm('Confirma o uso deste cupom?\n\nEsta ação não pode ser desfeita.')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'utilizar');
                formData.append('codigo', codigo);

                const response = await fetch('cupons.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Cupom utilizado com sucesso!');
                    filtrarCupons('ativos');
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }

        async function utilizarCupomDoFrontend() {
            const codigoInput = document.getElementById('codigoUtilizar');
            const codigo = codigoInput ? codigoInput.value.trim() : '';

            if (!codigo) {
                alert('Por favor, digite o código do cupom.');
                return;
            }

            if (!confirm(`Confirma a utilização do cupom com código ${codigo}? Esta ação não pode ser desfeita e registra o uso.`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'utilizar');
                formData.append('codigo', codigo); // Envia o código para o PHP

                const response = await fetch('cupons.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(`✅ ${result.message}`);
                    codigoInput.value = ''; // Limpa o campo após o sucesso
                    // Opcional: Recarrega o histórico de cupons utilizados para atualizar a tela.
                    if (document.getElementById('historico').classList.contains('active-content')) {
                        buscarHistorico(); 
                    }
                } else {
                    alert(`❌ Erro na utilização: ${result.message}`);
                }
            } catch (error) {
                alert('Erro na requisição: ' + error.message);
            }
        }

        // Preview de quantidade
        document.getElementById('quantidade').addEventListener('input', function() {
            const quantidade = this.value || 0;
            document.getElementById('quantidadePreview').textContent = quantidade;
        });

        // Configurar datas mínimas
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('dataInicio').setAttribute('min', hoje);
        document.getElementById('dataFim').setAttribute('min', hoje);

        document.getElementById('dataInicio').addEventListener('change', function() {
            document.getElementById('dataFim').setAttribute('min', this.value);
        });

        // Funções auxiliares
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

        // Carregar promoções
        async function carregarPromocoes() {
            const container = document.getElementById('promocoesContainer');
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Carregando...</p></div>';

            try {
                const response = await fetch('cupons.php?action=minhas_promocoes');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const promocoes = result.promocoes;

                if (promocoes.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <p>Nenhuma promoção cadastrada ainda</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = promocoes.map(promo => {
                    const dataInicio = new Date(promo.dta_inicio_cupom).toLocaleDateString('pt-BR');
                    const dataFim = new Date(promo.dta_termino_cupom).toLocaleDateString('pt-BR');
                    const dataEmissao = new Date(promo.dta_emissao_cupom).toLocaleDateString('pt-BR');
                    
                    const cuponsReservados = promo.cupons_reservados || 0;
                    const cuponsUsados = promo.cupons_usados || 0;
                    const cuponsDisponiveis = promo.quantidade - cuponsReservados;
                    
                    return `
                        <div class="cupom-card">
                            <div class="cupom-header">
                                <div><div class="cupom-title">${promo.titulo}</div></div>
                                <div class="cupom-badge">${promo.desconto}% OFF</div>
                            </div>
                            <div class="cupom-details">
                                <div class="cupom-detail">
                                    <strong>📅 Período</strong>
                                    <span>${dataInicio} até ${dataFim}</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>🎫 Total de Cupons</strong>
                                    <span>${promo.quantidade} cupons</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>✅ Cupons Usados</strong>
                                    <span>${cuponsUsados} de ${promo.quantidade}</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>📌 Cupons Reservados</strong>
                                    <span>${cuponsReservados} cupons</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>🔓 Cupons Disponíveis</strong>
                                    <span>${cuponsDisponiveis} cupons</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>📆 Data de Emissão</strong>
                                    <span>${dataEmissao}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

            } catch (error) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>Erro: ${error.message}</p>
                    </div>
                `;
            }
        }

        // Carregar histórico
        async function carregarHistorico() {
            const container = document.getElementById('historicoContainer');
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Carregando...</p></div>';

            try {
                const response = await fetch('cupons.php?action=historico');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const historico = result.historico;

                if (historico.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <p>Nenhum cupom foi utilizado ainda</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = historico.map(item => {
                    const dataUso = new Date(item.dta_uso_cupom_associado).toLocaleString('pt-BR');
                    const dataReserva = new Date(item.dta_cupom_associado).toLocaleDateString('pt-BR');
                    
                    return `
                        <div class="cupom-usado">
                            <div class="cupom-header">
                                <div><div class="cupom-title">${item.tit_cupom}</div></div>
                                <div>
                                    <div class="cupom-badge">${item.per_desc_cupom}% OFF</div>
                                    <span class="status-badge status-usado" style="display: block; margin-top: 8px;">✓ Utilizado</span>
                                </div>
                            </div>
                            <div class="codigo-cupom">
                                <div class="codigo-cupom-label">CÓDIGO DO CUPOM</div>
                                <div class="codigo-cupom-valor">${item.num_cupom}</div>
                            </div>
                            <div class="associado-info">
                                <strong>👤 Associado:</strong>
                                <p>${item.nom_associado || 'Não identificado'}</p>
                                <p>${item.email_associado}</p>
                            </div>
                            <div class="cupom-details">
                                <div class="cupom-detail">
                                    <strong>📌 RESERVADO EM</strong>
                                    <span>${dataReserva}</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>✅ UTILIZADO EM</strong>
                                    <span>${dataUso}</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>💰 DESCONTO</strong>
                                    <span>${item.per_desc_cupom}%</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

            } catch (error) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>Erro: ${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>