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
    <title>Categorias - Central Financeira</title>
    
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/components.css">
    
    <link rel="shortcut icon" href="icon/money-bag.png">
    <link rel="apple-touch-icon" href="icon/icon.png">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="js/animations.js"></script>
    
    <style>
        .page-layout {
            max-width: 1000px; /* Categories doesn't need to be as wide as dashboard */
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

        /* Toolbar */
        .toolbar-cat {
            display: flex;
            gap: var(--spacing-4);
            background: var(--bg-card);
            padding: var(--spacing-5);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            margin-bottom: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }

        .toolbar-cat .col-input {
            flex: 1;
            min-width: 200px;
        }

        .toolbar-cat .col-input label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: var(--spacing-1);
            display: block;
        }

        .toolbar-cat .col-input input,
        .toolbar-cat .col-input select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-strong);
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .toolbar-cat .col-input input:focus,
        .toolbar-cat .col-input select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        /* Lista de Categorias (CSS para o HTML gerado pelo JS) */
        #lista-categorias {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
            gap: var(--spacing-4);
        }

        .item-categoria {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: var(--spacing-5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }

        .item-categoria:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            border-color: var(--border-strong);
            background: var(--bg-card-hover);
        }

        .info-cat h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        
        .info-cat h4::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--accent-color);
        }

        .acoes {
            display: flex;
            gap: var(--spacing-2);
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
            .toolbar-cat { flex-direction: column; }
            .toolbar-cat .col-input { width: 100%; }
        }
    </style>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main class="page-layout animate-fade-in">
        <div class="header-extrato">
            <h2>Tags e Categorias</h2>
            <button class="btn btn-primary" id="btn-abrir-cadastro">
                <i class="ph ph-plus"></i> Nova Categoria
            </button>
        </div>

        <div class="toolbar-cat stagger-1">
            <div class="col-input">
                <label>Buscar Categoria</label>
                <input type="text" id="input-busca" placeholder="Digite o nome...">
            </div>
            <div class="col-input" style="flex: 0.5;">
                <label>Ordenar</label>
                <select id="ordem-select">
                    <option value="alfa">A - Z</option>
                    <option value="alfa-desc">Z - A</option>
                </select>
            </div>
        </div>

        <div id="lista-categorias" class="stagger-2"></div>
    </main>

    <div id="modal-categoria" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Nova Categoria</h3>
            <form id="form-categoria">
                <div class="input-group">
                    <label class="label">Nome da Categoria</label>
                    <input type="text" class="input" id="nome-cat" placeholder="Ex: Lazer, Saúde..." required>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6); justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/categorias.js"></script>
</body>
</html>
