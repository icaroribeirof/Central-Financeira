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

// 1. Resumo (Receitas, Despesas em Cartão, Despesas Não Cartão, Saldo) - FILTRADO POR USUÁRIO
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
    SUM(CASE WHEN tipo = 'despesa' AND eh_cartao = 1 THEN valor ELSE 0 END) as despesas_cartao,
    SUM(CASE WHEN tipo = 'despesa' AND eh_cartao = 0 THEN valor ELSE 0 END) as despesas_nao_cartao
    FROM transacoes 
    WHERE data LIKE ? AND usuario_id = ?");
$stmt->execute([$mes . '%', $usuario_id]);
$resumoDb = $stmt->fetch(PDO::FETCH_ASSOC);

$receitas = (float)($resumoDb['receitas'] ?? 0);
$despesas_cartao = (float)($resumoDb['despesas_cartao'] ?? 0);
$despesas_nao_cartao = (float)($resumoDb['despesas_nao_cartao'] ?? 0);
$despesas_total = $despesas_cartao + $despesas_nao_cartao;

// 2. Despesas por Categoria (Gráfico de Pizza) - FILTRADO POR USUÁRIO
$stmt = $pdo->prepare("SELECT categoria, SUM(valor) as total FROM transacoes 
                       WHERE data LIKE ? AND tipo = 'despesa' AND usuario_id = ?
                       GROUP BY categoria");
$stmt->execute([$mes . '%', $usuario_id]);
$catData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Evolução (Últimos 6 meses) - SEPARADO EM CARTÃO E NÃO CARTÃO - FILTRADO POR USUÁRIO
$evoLabels = [];
$evoValoresNaoCartao = [];
$evoValoresCartao = [];

for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months", strtotime($mes . "-01")));
    
    // Despesas não cartão
    $stmt = $pdo->prepare("SELECT SUM(valor) as total FROM transacoes WHERE data LIKE ? AND tipo = 'despesa' AND eh_cartao = 0 AND usuario_id = ?");
    $stmt->execute([$m . '%', $usuario_id]);
    $valNaoCartao = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Despesas cartão
    $stmt = $pdo->prepare("SELECT SUM(valor) as total FROM transacoes WHERE data LIKE ? AND tipo = 'despesa' AND eh_cartao = 1 AND usuario_id = ?");
    $stmt->execute([$m . '%', $usuario_id]);
    $valCartao = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $mesesNomes = ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"];
    $mesIndex = (int)date('m', strtotime($m."-01")) - 1;
    $evoLabels[] = $mesesNomes[$mesIndex];
    $evoValoresNaoCartao[] = (float)$valNaoCartao;
    $evoValoresCartao[] = (float)$valCartao;
}

echo json_encode([
    'resumo' => [
        'receitas' => $receitas,
        'despesas_nao_cartao' => $despesas_nao_cartao,
        'despesas_cartao' => $despesas_cartao,
        'despesas_total' => $despesas_total,
        'saldo' => $receitas - $despesas_nao_cartao
    ],
    'categorias' => [
        'labels' => array_column($catData, 'categoria'),
        'valores' => array_map('floatval', array_column($catData, 'total'))
    ],
    'evolucao_nao_cartao' => [
        'labels' => $evoLabels,
        'valores' => $evoValoresNaoCartao
    ],
    'evolucao_cartao' => [
        'labels' => $evoLabels,
        'valores' => $evoValoresCartao
    ]
]);