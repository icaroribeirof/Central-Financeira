<?php
require_once 'db_connect.php';

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
    <title>Dashboard - Central Financeira</title>
    
    <!-- New Premium CSS -->
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/components.css">
    
    <link rel="shortcut icon" href="icon/money-bag.png">
    <link rel="apple-touch-icon" href="icon/icon.png">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .page-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-6) var(--spacing-4);
        }

        .header-dash {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: var(--spacing-8);
            flex-wrap: wrap;
            gap: var(--spacing-4);
        }

        .welcome-text h2 {
            margin-bottom: var(--spacing-1);
            font-size: 1.75rem;
            letter-spacing: -0.03em;
        }

        .welcome-text p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Filter Box */
        .filter-box {
            display: flex;
            align-items: center;
            background-color: var(--bg-card);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-lg);
            padding: var(--spacing-2) var(--spacing-4);
            gap: var(--spacing-3);
            box-shadow: var(--shadow-sm);
            transition: border-color 0.2s;
        }
        
        .filter-box:focus-within {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .filter-box i {
            color: var(--text-tertiary);
            font-size: 1.2rem;
        }

        .dash-mes {
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 500;
            outline: none;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--spacing-5);
            margin-bottom: var(--spacing-10);
        }

        .metric-card {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2);
        }

        .metric-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .metric-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            font-size: 1.125rem;
        }
        
        .icon-receita { background-color: var(--success-bg); color: var(--success-text); }
        .icon-despesa { background-color: var(--danger-bg); color: var(--danger-text); }
        .icon-cartao { background-color: rgba(139, 92, 246, 0.1); color: #8B5CF6; }
        .icon-saldo { background-color: rgba(59, 130, 246, 0.1); color: var(--accent-color); }

        .metric-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: var(--spacing-6);
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }
        
        .card-chart-title {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: var(--spacing-6);
            color: var(--text-primary);
        }

        @media (max-width: 768px) {
            .page-layout { padding: var(--spacing-4); }
            .header-dash { flex-direction: column; align-items: flex-start; }
            .filter-box { width: 100%; justify-content: space-between; }
        }
    </style>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main class="page-layout animate-fade-in">
        <div class="header-dash">
            <div class="welcome-text">
                <h2>Olá, <?php echo htmlspecialchars(explode(' ', $usuario_nome)[0]); ?></h2>
                <p>Aqui está o seu panorama financeiro.</p>
            </div>
            
            <div class="filter-box">
                <i class="ph ph-calendar-blank"></i>
                <input type="month" id="dash-mes" class="dash-mes">
            </div>
        </div>

        <div class="summary-grid">
            <div class="card card-hover stagger-1 metric-card">
                <div class="metric-header">
                    <div class="metric-icon icon-receita">
                        <i class="ph ph-trend-up"></i>
                    </div>
                    Receitas
                </div>
                <div class="metric-value" id="total-receitas">R$ 0,00</div>
            </div>
            
            <div class="card card-hover stagger-2 metric-card">
                <div class="metric-header">
                    <div class="metric-icon icon-despesa">
                        <i class="ph ph-trend-down"></i>
                    </div>
                    Despesas (Geral)
                </div>
                <div class="metric-value" id="total-despesas">R$ 0,00</div>
            </div>
            
            <div class="card card-hover stagger-3 metric-card">
                <div class="metric-header">
                    <div class="metric-icon icon-cartao">
                        <i class="ph ph-credit-card"></i>
                    </div>
                    Faturas
                </div>
                <div class="metric-value" id="total-cartao">R$ 0,00</div>
            </div>
            
            <div class="card card-hover stagger-4 metric-card">
                <div class="metric-header">
                    <div class="metric-icon icon-saldo">
                        <i class="ph ph-wallet"></i>
                    </div>
                    Saldo Livre
                </div>
                <div class="metric-value" id="total-saldo">R$ 0,00</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card card-hover stagger-1">
                <h4 class="card-chart-title">
                    <i class="ph ph-pie-chart" style="color: var(--accent-color);"></i>
                    Por Categoria
                </h4>
                <div class="chart-container">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </div>
            
            <div class="card card-hover stagger-2">
                <h4 class="card-chart-title">
                    <i class="ph ph-chart-line-up" style="color: var(--warning);"></i>
                    Evolução Geral
                </h4>
                <div class="chart-container">
                    <canvas id="chartEvolucaoNaoCartao"></canvas>
                </div>
            </div>
            
            <div class="card card-hover stagger-3">
                <h4 class="card-chart-title">
                    <i class="ph ph-chart-bar" style="color: #8B5CF6;"></i>
                    Evolução Cartões
                </h4>
                <div class="chart-container">
                    <canvas id="chartEvolucaoCartao"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>