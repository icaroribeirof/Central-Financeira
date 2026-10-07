<?php
require_once 'db_connect.php';

// Se não houver sessão, volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Nome ao salvar na Tela de Início -->
    <meta name="apple-mobile-web-app-title" content="C. Financeira">
    <!-- Ícone da Tela de Início (iOS) -->
    <link rel="apple-touch-icon" sizes="180x180" href="icon/money-bag.png">
    <title>Extrato - Central Financeira</title>
    
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/components.css">
    
    <link rel="shortcut icon" href="icon/money-bag.png">
    <link rel="apple-touch-icon" href="icon/icon.png">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="js/animations.js"></script>
    
    <style>
        .page-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-6) var(--spacing-4);
        }

        .header-extrato {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-6);
            flex-wrap: wrap;
            gap: var(--spacing-4);
        }

        .header-extrato h2 {
            font-size: 1.5rem;
            letter-spacing: -0.02em;
        }

        .header-btns {
            display: flex;
            gap: var(--spacing-3);
            flex-wrap: wrap;
        }

        /* Toolbar / Filters */
        .toolbar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--spacing-4);
            background: var(--bg-card);
            padding: var(--spacing-5);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-sm);
        }

        .toolbar .col-input label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: var(--spacing-1);
            display: block;
        }

        .toolbar .col-input input,
        .toolbar .col-input select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-strong);
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .toolbar .col-input input:focus,
        .toolbar .col-input select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        /* Resumo Filtros */
        .resumo-filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-6);
        }

        .card-resumo-filtro {
            background: var(--bg-card);
            padding: var(--spacing-4);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-1);
        }

        .card-resumo-filtro .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-secondary);
        }

        .card-resumo-filtro p {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .card-resumo-filtro.receitas p { color: var(--success-text); }
        .card-resumo-filtro.despesas p { color: var(--danger-text); }

        /* Lista de Transações - Estilizando o HTML gerado pelo JS */
        #lista-transacoes {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-3);
        }

        .item-movimentacao {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4) var(--spacing-5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }

        .item-movimentacao:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            border-color: var(--border-strong);
            background: var(--bg-card-hover);
        }

        /* Side border indicator */
        .item-movimentacao::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }
        
        .item-movimentacao.receita::before { background-color: var(--success); }
        .item-movimentacao.despesa::before { background-color: var(--danger); }

        .info-principal {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-1);
        }

        .topo-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }

        .topo-item h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .info-principal span {
            font-size: 0.8rem;
            color: var(--text-tertiary);
        }

        .acoes-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .valor-mov {
            font-weight: 700;
            font-size: 1.125rem;
            margin-right: var(--spacing-3);
        }

        .valor-mov.receita { color: var(--success-text); }
        .valor-mov.despesa { color: var(--text-primary); } /* Keep expense white/black, just use the minus sign */

        /* Modal specific styling */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-xl);
            padding: var(--spacing-6);
            width: 100%;
            max-width: 600px;
            margin: var(--spacing-4);
            box-shadow: var(--shadow-lg);
            animation: slideInUp 0.3s;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            margin-bottom: var(--spacing-6);
            font-size: 1.25rem;
        }

        .grid-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-4);
        }

        .col-full {
            grid-column: 1 / -1;
        }

        /* Radio Cards */
        .tipo-lancamento-grupo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-3);
            margin-top: var(--spacing-2);
        }

        .radio-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-1);
            padding: var(--spacing-3);
            background: var(--bg-body);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .radio-card input[type="radio"] {
            display: none;
        }

        .radio-card.ativo {
            border-color: var(--accent-color);
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
        }

        .radio-icon { font-size: 1.25rem; }
        .radio-label { font-size: 0.75rem; font-weight: 600; }

        .hint-parcelas {
            display: block;
            margin-top: var(--spacing-1);
            font-size: 0.8rem;
            color: var(--accent-color);
        }

        .info-box {
            background: var(--info-bg);
            color: var(--info);
            padding: var(--spacing-3);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-3);
            margin-top: var(--spacing-6);
            padding-top: var(--spacing-4);
            border-top: 1px solid var(--border-subtle);
        }
        
        .badge {
            background: var(--bg-body);
            border: 1px solid var(--border-strong);
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .grid-form { grid-template-columns: 1fr; }
            .item-movimentacao { flex-direction: column; align-items: flex-start; gap: var(--spacing-3); }
            .acoes-item { width: 100%; justify-content: flex-start; gap: var(--spacing-2); border-top: 1px solid var(--border-subtle); padding-top: var(--spacing-3); }
            .acoes-item .valor-mov { margin-right: auto; }
        }
    </style>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main class="page-layout animate-fade-in">
        <div class="header-extrato">
            <h2>Histórico de Movimentações</h2>
            <div class="header-btns">
                <button class="btn btn-secondary" id="btn-resetar-filtros">
                    <i class="ph ph-arrows-clockwise"></i> Resetar Filtros
                </button>
                <button class="btn btn-danger" id="btn-abrir-limpeza">
                    <i class="ph ph-trash"></i> Limpar Mês
                </button>
                <button class="btn btn-primary" id="btn-abrir-cadastro">
                    <i class="ph ph-plus"></i> Novo Lançamento
                </button>
            </div>
        </div>

        <div class="toolbar stagger-1">
            <div class="col-input">
                <label>Mês</label>
                <input type="month" id="filtro-mes">
            </div>
            <div class="col-input">
                <label>Buscar</label>
                <input type="text" id="input-busca" placeholder="Mercado, Aluguel...">
            </div>
            <div class="col-input">
                <label>Categoria</label>
                <select id="filtro-categoria">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-input">
                <label>Pagamento</label>
                <select id="filtro-metodo">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-input">
                <label>Assinatura</label>
                <select id="filtro-assinatura">
                    <option value="">Todos</option>
                    <option value="assinatura">Sim</option>
                    <option value="nao-assinatura">Não</option>
                </select>
            </div>
            <div class="col-input">
                <label>Cartão</label>
                <select id="filtro-cartao">
                    <option value="">Todos</option>
                    <option value="cartao">Sim</option>
                    <option value="nao-cartao">Não</option>
                </select>
            </div>
            <div class="col-input">
                <label>Ordenar</label>
                <select id="ordem-select">
                    <option value="data-asc">Data (Antigo)</option>
                    <option value="data-desc">Data (Recente)</option>
                    <option value="valor-desc">Maior Valor</option>
                    <option value="valor-asc">Menor Valor</option>
                </select>
            </div>
        </div>

        <div id="resumo-filtros" class="resumo-filtros stagger-2" style="display: none;">
            <div class="card-resumo-filtro receitas">
                <span class="label">Total Receitas</span>
                <p id="soma-receitas">R$ 0,00</p>
            </div>
            <div class="card-resumo-filtro despesas">
                <span class="label">Total Despesas</span>
                <p id="soma-despesas">R$ 0,00</p>
            </div>
            <div class="card-resumo-filtro saldo">
                <span class="label">Saldo</span>
                <p id="soma-saldo">R$ 0,00</p>
            </div>
        </div>

        <div id="lista-transacoes" class="stagger-3"></div>
    </main>

    <!-- Modals -->
    <div id="modal-cadastro" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Novo Lançamento</h3>
            <form id="form-transacao">
                <div class="grid-form">
                    <div class="col-input input-group">
                        <label class="label">Descrição</label>
                        <input type="text" class="input" id="desc" placeholder="Ex: Compra no mercado" required style="padding-left: 1rem;">
                    </div>
                    <div class="col-input input-group">
                        <label class="label">Valor (R$)</label>
                        <input type="number" class="input" id="valor" step="0.01" placeholder="0.00" required style="padding-left: 1rem;">
                    </div>
                    <div class="col-input input-group">
                        <label class="label">Data</label>
                        <input type="date" class="input" id="data-lancamento" required style="padding-left: 1rem;">
                    </div>
                    <div class="col-input input-group">
                        <label class="label">Tipo</label>
                        <select id="tipo-transacao" class="input" required style="padding-left: 1rem;">
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="col-input input-group">
                        <label class="label">Categoria</label>
                        <select id="categoria-select" class="input" required style="padding-left: 1rem;"></select>
                    </div>
                    <div class="col-input input-group">
                        <label class="label">Pagamento</label>
                        <select id="metodo-pagamento" class="input" required style="padding-left: 1rem;"></select>
                    </div>

                    <div class="col-input col-full">
                        <label class="label">Recorrência</label>
                        <div class="tipo-lancamento-grupo">
                            <label class="radio-card" id="radio-unico">
                                <input type="radio" name="tipo_lancamento" value="unico" checked>
                                <span class="radio-icon"><i class="ph ph-number-circle-one"></i></span>
                                <span class="radio-label">Único</span>
                            </label>
                            <label class="radio-card" id="radio-recorrente">
                                <input type="radio" name="tipo_lancamento" value="recorrente">
                                <span class="radio-icon"><i class="ph ph-arrows-clockwise"></i></span>
                                <span class="radio-label">Recorrente</span>
                            </label>
                            <label class="radio-card" id="radio-parcelado">
                                <input type="radio" name="tipo_lancamento" value="parcelado">
                                <span class="radio-icon"><i class="ph ph-calendar-plus"></i></span>
                                <span class="radio-label">Parcelado</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-input col-full input-group" id="campo-parcelas" style="display:none;">
                        <label class="label">Número de Parcelas</label>
                        <input type="number" class="input" id="total-parcelas" min="2" max="360" value="2" style="padding-left: 1rem;">
                        <span class="hint-parcelas" id="hint-parcelas"></span>
                    </div>

                    <div class="col-input col-full" id="aviso-recorrente" style="display:none;">
                        <p class="info-box"><i class="ph ph-info"></i> Lançamentos mensais automáticos para os próximos 24 meses.</p>
                    </div>

                    <div class="col-input col-full" style="display: flex; gap: var(--spacing-4); margin-top: var(--spacing-2);">
                        <label style="display:flex; align-items:center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="eh-assinatura"> Assinatura
                        </label>
                        <label style="display:flex; align-items:center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="eh-cartao"> Compra no Cartão
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Outros Modais Auxiliares -->
    <div id="modal-limpeza" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <div class="confirm-modal-icon-container">
                <div class="confirm-modal-icon-bg">
                    <i class="ph ph-warning-circle"></i>
                </div>
            </div>
            <h3>Limpar Registros</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-6);">
                Selecione o que deseja excluir do mês selecionado:
            </p>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-2);">
                <button onclick="executarLimpeza('tudo')" class="btn btn-danger-solid" style="justify-content: center; width: 100%;">Todas as Movimentações</button>
                <button onclick="executarLimpeza('despesa')" class="btn btn-secondary" style="justify-content: center; width: 100%;">Somente Despesas</button>
                <button onclick="executarLimpeza('receita')" class="btn btn-secondary" style="justify-content: center; width: 100%;">Somente Receitas</button>
                <button onclick="fecharModal()" class="btn btn-ghost" style="justify-content: center; width: 100%; margin-top: var(--spacing-2);">Cancelar</button>
            </div>
        </div>
    </div>

    <div id="modal-editar-grupo" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <h3 id="editar-grupo-titulo">Salvar Alteração</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-6);" id="editar-grupo-subtitulo"></p>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-2);">
                <button id="btn-salvar-unico" class="btn btn-secondary" style="justify-content: center;">Alterar só este</button>
                <button id="btn-salvar-futuros" class="btn btn-primary" style="justify-content: center;">Alterar este e futuros</button>
                <button onclick="fecharModalEdicao()" class="btn btn-ghost" style="justify-content: center;">Cancelar</button>
            </div>
        </div>
    </div>

    <div id="modal-excluir-grupo" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <div class="confirm-modal-icon-container">
                <div class="confirm-modal-icon-bg">
                    <i class="ph ph-trash"></i>
                </div>
            </div>
            <h3 id="excluir-titulo">Excluir Lançamento</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-6);" id="excluir-subtitulo"></p>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-2);">
                <button id="btn-excluir-unico" class="btn btn-secondary" style="justify-content: center;">Só este</button>
                <button id="btn-excluir-futuros" class="btn btn-primary" style="justify-content: center;">Este e futuros</button>
                <button id="btn-excluir-grupo" class="btn btn-danger-solid" style="justify-content: center;">Todos do grupo</button>
                <button onclick="fecharModal()" class="btn btn-ghost" style="justify-content: center;">Cancelar</button>
            </div>
        </div>
    </div>
    
    <script src="js/extrato.js"></script>
</body>
</html>
