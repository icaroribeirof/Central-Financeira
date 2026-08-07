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
    <title>Cartões - Central Financeira</title>
    
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

        .header-cartao {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-6);
            flex-wrap: wrap;
            gap: var(--spacing-4);
        }

        .header-cartao h2 {
            font-size: 1.5rem;
            letter-spacing: -0.02em;
        }

        /* Toolbar */
        .toolbar-cartao {
            display: flex;
            gap: var(--spacing-4);
            background: var(--bg-card);
            padding: var(--spacing-5);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            margin-bottom: var(--spacing-8);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }

        .toolbar-cartao .col-input {
            flex: 1;
            min-width: 200px;
        }

        .toolbar-cartao .col-input label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: var(--spacing-1);
            display: block;
        }

        .toolbar-cartao .col-input input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-strong);
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .toolbar-cartao .col-input input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        /* Lista de Cartões (CSS para o HTML gerado pelo JS) */
        .container-cartoes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
            gap: var(--spacing-6);
        }

        /* O estilo premium de Cartão */
        .card-item {
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
            background-color: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px; /* Bordas mais arredondadas para parecer cartão */
            padding: var(--spacing-6);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-4);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        [data-theme="light"] .card-item {
            background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
            border-color: var(--border-strong);
        }

        /* Efeito visual no cartão (brilho sutil no canto superior) */
        .card-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .card-item:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--border-strong);
        }
        
        .card-item:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .card-header h3::before {
            content: '\e15b'; /* Phosphor icon for credit card */
            font-family: "Phosphor";
            font-weight: normal;
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .fatura-label {
            background-color: var(--bg-body);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            border: 1px solid var(--border-strong);
        }

        .ciclo-info {
            font-size: 0.8rem;
            color: var(--text-tertiary);
            line-height: 1.4;
            margin: 0;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem;
            border-radius: var(--radius-sm);
        }
        
        [data-theme="light"] .ciclo-info {
            background: rgba(0,0,0,0.03);
        }

        .limite-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
            margin-top: auto; /* Empurra pro final */
        }

        .limite-texto {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
        }
        
        .limite-texto span:first-child strong {
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: var(--bg-body);
            border-radius: var(--radius-full);
            overflow: hidden;
            border: 1px solid var(--border-strong);
        }

        .progress-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.5s ease;
        }

        .card-footer {
            display: flex;
            gap: var(--spacing-2);
            margin-top: var(--spacing-2);
        }

        .card-footer .btn-edit, 
        .card-footer .btn-delete {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .card-footer .btn-edit {
            background-color: var(--bg-body);
            color: var(--text-primary);
            border: 1px solid var(--border-strong);
        }
        
        .card-footer .btn-edit:hover {
            background-color: var(--border-strong);
        }

        .card-footer .btn-delete {
            background-color: transparent;
            color: var(--danger-text);
            border: 1px solid transparent;
        }
        
        .card-footer .btn-delete:hover {
            background-color: var(--danger-bg);
            border-color: var(--danger);
        }

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
            max-width: 450px;
            margin: var(--spacing-4);
            box-shadow: var(--shadow-lg);
            animation: slideInUp 0.3s;
        }

        .modal-content h3 {
            margin-bottom: var(--spacing-6);
            font-size: 1.25rem;
        }

        @media (max-width: 768px) {
            .toolbar-cartao { flex-direction: column; }
            .toolbar-cartao .col-input { width: 100%; max-width: none !important; }
        }
    </style>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main class="page-layout animate-fade-in">
        <div class="header-cartao">
            <h2>Meus Cartões</h2>
            <button class="btn btn-primary" id="btn-abrir-modal">
                <i class="ph ph-plus"></i> Novo Cartão
            </button>
        </div>

        <div class="toolbar-cartao stagger-1">
            <div class="col-input">
                <label>Buscar Cartão</label>
                <input type="text" id="busca-cartao" placeholder="Nome do cartão...">
            </div>
            <div class="col-input" style="max-width: 200px;">
                <label>Ver Fatura de</label>
                <input type="month" id="filtro-data">
            </div>
        </div>

        <div id="lista-cartoes" class="container-cartoes stagger-2"></div>
    </main>

    <div id="modal-cartao" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Novo Cartão</h3>
            <form id="form-cartao">
                <div class="input-group">
                    <label class="label">Nome do Cartão</label>
                    <input type="text" class="input" id="nome-cartao" placeholder="Ex: Nubank, Inter..." required>
                </div>
                <div class="input-group">
                    <label class="label">Limite Total (R$)</label>
                    <input type="number" class="input" id="limite-total" step="0.01" placeholder="0.00" required>
                </div>
                <div class="input-group">
                    <label class="label">Dia de Fechamento</label>
                    <input type="number" class="input" id="dia-fechamento" min="1" max="31" placeholder="Ex: 10" required>
                    <span style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">Compras até este dia entram na fatura do mês seguinte.</span>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6); justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/cartao.js"></script>
</body>
</html>
