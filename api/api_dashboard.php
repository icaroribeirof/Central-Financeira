<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Sessão expirada']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mes = $_GET['mes'] ?? date('Y-m');

// 1. Resumo (Receitas, Despesas, Saldo) - FILTRADO POR USUÁRIO
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
    SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
    FROM transacoes 
    WHERE data LIKE ? AND usuario_id = ?");
$stmt->execute([$mes . '%', $usuario_id]);
$resumoDb = $stmt->fetch(PDO::FETCH_ASSOC);

$receitas = (float)($resumoDb['receitas'] ?? 0);
$despesas = (float)($resumoDb['despesas'] ?? 0);

// 2. Despesas por Categoria (Gráfico de Pizza) - FILTRADO POR USUÁRIO
$stmt = $pdo->prepare("SELECT categoria, SUM(valor) as total FROM transacoes 
                       WHERE data LIKE ? AND tipo = 'despesa' AND usuario_id = ?
                       GROUP BY categoria");
$stmt->execute([$mes . '%', $usuario_id]);
$catData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Evolução (Últimos 6 meses) - FILTRADO POR USUÁRIO
$evoLabels = [];
$evoValores = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months", strtotime($mes . "-01")));
    $stmt = $pdo->prepare("SELECT SUM(valor) as total FROM transacoes WHERE data LIKE ? AND tipo = 'despesa' AND usuario_id = ?");
    $stmt->execute([$m . '%', $usuario_id]);
    $val = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $mesesNomes = ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"];
    $mesIndex = (int)date('m', strtotime($m."-01")) - 1;
    $evoLabels[] = $mesesNomes[$mesIndex];
    $evoValores[] = (float)$val;
}

echo json_encode([
    'resumo' => [
        'receitas' => $receitas,
        'despesas' => $despesas,
        'saldo' => $receitas - $despesas
    ],
    'categorias' => [
        'labels' => array_column($catData, 'categoria'),
        'valores' => array_map('floatval', array_column($catData, 'total'))
    ],
    'evolucao' => [
        'labels' => $evoLabels,
        'valores' => $evoValores
    ]
]);