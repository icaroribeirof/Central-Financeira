<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$method = $_SERVER['REQUEST_METHOD'];

// --- LISTAR TRANSAÇÕES E MÉTODOS (GET) ---
if ($method === 'GET') {
    try {
        // 1. Busca as transações do usuário
        $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC, id DESC");
        $stmt->execute([$usuario_id]);
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Busca os cartões do usuário para os métodos de pagamento
        $stmt_cartoes = $pdo->prepare("SELECT nome FROM cartoes WHERE usuario_id = ? ORDER BY nome ASC");
        $stmt_cartoes->execute([$usuario_id]);
        $cartoes = $stmt_cartoes->fetchAll(PDO::FETCH_COLUMN);

        // Opções padrão + Cartões do usuário
        $metodos = array_merge(['Dinheiro', 'PIX'], $cartoes);

        echo json_encode([
            'transacoes' => $transacoes,
            'metodos' => $metodos
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- SALVAR NOVA TRANSAÇÃO (POST) ---
if ($method === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    
    try {
        $sql = "INSERT INTO transacoes (descricao, valor, data, tipo, categoria, metodo, usuario_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $dados['descricao'],
            $dados['valor'],
            $dados['data'],
            $dados['tipo'],
            $dados['categoria'],
            $dados['metodo'],
            $usuario_id
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- EXCLUIR (DELETE) ---
if ($method === 'DELETE') {
    $dados = json_decode(file_get_contents('php://input'), true);
    try {
        $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$dados['id'], $usuario_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>