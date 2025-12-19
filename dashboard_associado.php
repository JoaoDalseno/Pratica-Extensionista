<?php
require_once 'config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'associado') {
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
    <title>Sistema de Cupons - Dashboard Associado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="dashboard-header">
            <div class="user-info">
                <h2 id="nomeAssociado">👤 <?php echo htmlspecialchars($usuario['nome']); ?></h2>
                <p id="emailAssociado"><?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>
            <button class="logout-btn" onclick="logout()">Sair</button>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="trocarAba('disponiveis')">
                🔍 Cupons Disponíveis
            </button>
            <button class="tab-btn" onclick="trocarAba('consultar')">
                📋 Consultar Meus Cupons
            </button>
            <button class="tab-btn" onclick="trocarAba('meus-cupons')">
                🎫 Meus Cupons Ativos
            </button>
        </div>

        <div id="tab-disponiveis" class="tab-content active">
            <div class="header">
                <h1>🔍 Buscar Cupons</h1>
                <p>Pesquise e reserve cupons de desconto</p>
            </div>

            <div class="search-section">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label for="categoria">Categoria do Comércio</label>
                    <select id="categoria" name="categoria">
                        <option value="">Todas as categorias</option>
                        <option value="Alimentação">🍔 Alimentação</option>
                        <option value="Moda e Vestuário">👕 Moda e Vestuário</option>
                        <option value="Saúde e Beleza">💊 Saúde e Beleza</option>
                        <option value="Tecnologia">💻 Tecnologia</option>
                        <option value="Educação">📚 Educação</option>
                        <option value="Lazer e Entretenimento">🎮 Lazer e Entretenimento</option>
                        <option value="Serviços">🔧 Serviços</option>
                        <option value="Outros">📦 Outros</option>
                    </select>
                </div>
                <button class="btn btn-search" onclick="buscarCupons()">Buscar Cupons</button>
            </div>

            <div class="cupons-disponiveis">
                <h2>📋 Promoções Disponíveis</h2>
                <div id="cuponsContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔍</div>
                        <p>Selecione uma categoria e clique em "Buscar Cupons" para ver as promoções disponíveis</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-consultar" class="tab-content">
            <div class="header">
                <h1>📋 Consultar Meus Cupons</h1>
                <p>Visualize e filtre todos os seus cupons reservados</p>
            </div>

            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filtrarMeusCupons('ativos')" data-filter="ativos">
                        ✅ Cupons Ativos
                    </button>
                    <button class="filter-btn" onclick="filtrarMeusCupons('utilizados')" data-filter="utilizados">
                        ✓ Cupons Utilizados
                    </button>
                    <button class="filter-btn" onclick="filtrarMeusCupons('vencidos')" data-filter="vencidos">
                        ⏰ Cupons Vencidos
                    </button>
                </div>
            </div>

            <div class="cupons-consulta">
                <div id="consultaMeusCuponsContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔍</div>
                        <p>Carregando seus cupons...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-meus-cupons" class="tab-content">
            <div class="header">
                <h1>🎫 Meus Cupons Ativos</h1>
                <p>Cupons que você reservou e estão válidos</p>
            </div>

            <div class="meus-cupons">
                <div id="meusCuponsContainer">
                    <div class="empty-state">
                        <div class="empty-state-icon">🔭</div>
                        <p>Você ainda não reservou nenhum cupom</p>
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
                'disponiveis': 0,
                'consultar': 1,
                'meus-cupons': 2
            };
            
            document.querySelectorAll('.tab-btn')[tabButtons[aba]].classList.add('active');
            document.getElementById('tab-' + aba).classList.add('active');

            if (aba === 'meus-cupons') {
                carregarMeusCupons();
            } else if (aba === 'consultar') {
                filtrarMeusCupons('ativos');
            }
        }

        async function buscarCupons() {
            const categoria = document.getElementById('categoria').value;
            const container = document.getElementById('cuponsContainer');
            
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Buscando cupons...</p></div>';

            try {
                const response = await fetch(`cupons.php?action=buscar_disponiveis&categoria=${encodeURIComponent(categoria)}`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const cupons = result.cupons;

                if (cupons.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">😕</div>
                            <p>Nenhuma promoção disponível ${categoria ? 'nesta categoria' : 'no momento'}</p>
                        </div>
                    `;
                    return;
                }

                const promocoes = {};
                cupons.forEach(cupom => {
                    const key = `${cupom.tit_cupom}_${cupom.cnpj_comercio}`;
                    if (!promocoes[key]) {
                        promocoes[key] = {
                            titulo: cupom.tit_cupom,
                            desconto: cupom.per_desc_cupom,
                            comerciante: cupom.nom_fantasia_comercio,
                            dataInicio: cupom.dta_inicio_cupom,
                            dataFim: cupom.dta_termino_cupom,
                            categoria: cupom.nom_categoria,
                            cupons: []
                        };
                    }
                    promocoes[key].cupons.push(cupom.num_cupom);
                });

                container.innerHTML = Object.values(promocoes).map(promo => {
                    const dataFim = new Date(promo.dataFim).toLocaleDateString('pt-BR');
                    const cuponsDisponiveis = promo.cupons.length;
                    
                    return `
                        <div class="cupom-card">
                            <div class="cupom-header">
                                <div>
                                    <div class="cupom-title">${promo.titulo}</div>
                                    <div class="cupom-comerciante">
                                        🏪 ${promo.comerciante}
                                    </div>
                                </div>
                                <div class="cupom-badge">${promo.desconto}% OFF</div>
                            </div>
                            <div class="cupom-details">
                                <div class="cupom-detail">
                                    <strong>📅 VÁLIDO ATÉ</strong>
                                    <span>${dataFim}</span>
                                </div>
                                <div class="cupom-detail">
                                    <strong>🎫 CUPONS DISPONÍVEIS</strong>
                                    <span>${cuponsDisponiveis}</span>
                                </div>
                            </div>
                            <div class="cupom-footer">
                                <div class="cupons-restantes">
                                    ⚡ <strong>${cuponsDisponiveis}</strong> ${cuponsDisponiveis === 1 ? 'cupom disponível' : 'cupons disponíveis'}
                                </div>
                                <button class="btn-reservar" onclick="reservarCupom('${promo.cupons[0]}')">
                                    Reservar Cupom
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

            } catch (error) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>Erro ao buscar cupons: ${error.message}</p>
                    </div>
                `;
            }
        }

        async function reservarCupom(numCupom) {
            if (!confirm('Tem certeza que deseja reservar este cupom? A reserva é imediata.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'reservar');
                // O backend (cupons.php) foi ajustado para ler 'numCupom'
                formData.append('numCupom', numCupom); 

                const response = await fetch('cupons.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(`✅ ${result.message}\n\nVocê pode visualizá-lo na aba "Meus Cupons"`);
                    
                    buscarCupons(); 
                    
                    // Se a aba "Meus Cupons Ativos" estiver visível, atualiza ela também (opcional)
                    if (document.getElementById('meus-cupons').classList.contains('active-content')) {
                        buscarMeusCupons();
                    }
                } else {
                    alert(`❌ Erro ao reservar: ${result.message}`);
                }
            } catch (error) {
                alert('Erro na requisição: ' + error.message);
            }
        }

        async function filtrarMeusCupons(filtro) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-filter="${filtro}"]`).classList.add('active');

            const container = document.getElementById('consultaMeusCuponsContainer');
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Carregando...</p></div>';

            try {
                const response = await fetch(`cupons.php?action=meus_cupons&filtro=${filtro}`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const cupons = result.cupons;

                if (cupons.length === 0) {
                    const mensagens = {
                        'ativos': 'Você não possui cupons ativos no momento',
                        'utilizados': 'Você ainda não utilizou nenhum cupom',
                        'vencidos': 'Você não possui cupons vencidos'
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
                    const dataReserva = new Date(cupom.dta_reserva).toLocaleDateString('pt-BR');
                    const dataUso = cupom.dta_uso ? new Date(cupom.dta_uso).toLocaleString('pt-BR') : '-';
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
                                    <div class="cupom-comerciante">
                                        🏪 ${cupom.nom_fantasia_comercio}
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

                            <div class="cupom-details">
                                <div class="cupom-detail">
                                    <strong>📅 VÁLIDO ATÉ</strong>
                                    <span>${dataFim}</span>
                                </div>
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

                            ${filtro === 'ativos' ? `
                                <div class="info-box" style="margin-top: 15px;">
                                    ℹ️ Apresente este código no estabelecimento para utilizar o desconto.
                                </div>
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

        async function carregarMeusCupons() {
            const container = document.getElementById('meusCuponsContainer');
            container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⏳</div><p>Carregando...</p></div>';

            try {
                const response = await fetch('cupons.php?action=meus_cupons&filtro=ativos');
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const cupons = result.cupons;

                if (cupons.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">🔭</div>
                            <p>Você não possui cupons ativos no momento</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = cupons.map(cupom => {
                    const dataFim = new Date(cupom.dta_termino_cupom).toLocaleDateString('pt-BR');
                    const dataReserva = new Date(cupom.dta_reserva).toLocaleDateString('pt-BR');
                    
                    return `
                        <div class="cupom-reservado">
                            <div class="cupom-header">
                                <div>
                                    <div class="cupom-title">${cupom.tit_cupom}</div>
                                    <div class="cupom-comerciante">
                                        🏪 ${cupom.nom_fantasia_comercio}
                                    </div>
                                </div>
                                <div>
                                    <div class="cupom-badge">${cupom.per_desc_cupom}% OFF</div>
                                    <span class="status-badge status-ativo" style="display: block; margin-top: 8px;">
                                        ✓ Ativo
                                    </span>
                                </div>
                            </div>
                            
                            <div class="codigo-cupom">
                                <div class="codigo-cupom-label">CÓDIGO DO CUPOM</div>
                                <div class="codigo-cupom-valor">${cupom.num_cupom}</div>
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
                                <div class="cupom-detail">
                                    <strong>💰 DESCONTO</strong>
                                    <span>${cupom.per_desc_cupom}%</span>
                                </div>
                            </div>

                            <div class="info-box" style="margin-top: 15px;">
                                ℹ️ Apresente este código no estabelecimento para utilizar o desconto.
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

        window.addEventListener('load', () => {
            buscarCupons();
        });
    </script>
</body>
</html>