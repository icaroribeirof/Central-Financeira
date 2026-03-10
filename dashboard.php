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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="shortcut icon" href="icon/money-bag.png">
    <title>Dashboard - Central Financeira</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main>
        <div class="header-dash">
            <div>
                <h2 style="margin-bottom: 5px;">Olá, <?php echo explode(' ', $usuario_nome)[0]; ?>!</h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">Aqui está seu resumo financeiro.</p>
            </div>
            <div class="filter-box">
                <label style="font-size: 0.7rem; font-weight: bold;">MÊS:</label>
                <input type="month" id="dash-mes">
            </div>
        </div>

        <div class="resumo-container">
            <div class="card-resumo receitas">
                <h3>Receitas</h3>
                <p id="total-receitas">R$ 0,00</p>
            </div>
            <div class="card-resumo despesas">
                <h3>Despesas (Não Cartão)</h3>
                <p id="total-despesas">R$ 0,00</p>
            </div>
        </div>

        <div class="resumo-container">
            <div class="card-resumo saldo">
                <h3>Saldo Atual</h3>
                <p id="total-saldo">R$ 0,00</p>
            </div>
            <div class="card-resumo cartao">
                <h3>Despesas (Cartão)</h3>
                <p id="total-cartao">R$ 0,00</p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card-chart">
                <h4>Despesas por Categoria</h4>
                <canvas id="chartCategorias"></canvas>
            </div>
            <div class="card-chart">
                <h4>Evolução - Despesas Não Cartão (6 meses)</h4>
                <canvas id="chartEvolucaoNaoCartao"></canvas>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card-chart">
                <h4>Evolução - Despesas Cartão (6 meses)</h4>
                <canvas id="chartEvolucaoCartao"></canvas>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>
