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
// CORREÇÃO: filtro de mês aplicado no backend (antes era ignorado)
if ($method === 'GET') {
    try {
        $mes = $_GET['mes'] ?? null;

        if ($mes) {
            $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE usuario_id = ? AND data LIKE ? ORDER BY data DESC, id DESC");
            $stmt->execute([$usuario_id, $mes . '%']);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE usuario_id = ? ORDER BY data DESC, id DESC");
            $stmt->execute([$usuario_id]);
        }
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt_cartoes = $pdo->prepare("SELECT nome FROM cartoes WHERE usuario_id = ? ORDER BY nome ASC");
        $stmt_cartoes->execute([$usuario_id]);
        $cartoes = $stmt_cartoes->fetchAll(PDO::FETCH_COLUMN);

        $metodos = array_merge(['Dinheiro', 'PIX'], $cartoes);

        echo json_encode([
            'transacoes' => $transacoes,
            'metodos' => $metodos
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- SALVAR OU EDITAR TRANSAÇÃO (POST) ---
// CORREÇÃO: quando 'id' é enviado, faz UPDATE em vez de INSERT duplicado
if ($method === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);

    try {
        if (!empty($dados['id'])) {
            // Editar transação existente (garante que pertence ao usuário)
            $sql = "UPDATE transacoes 
                    SET descricao = ?, valor = ?, data = ?, tipo = ?, categoria = ?, metodo = ?
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['descricao'],
                $dados['valor'],
                $dados['data'],
                $dados['tipo'],
                $dados['categoria'],
                $dados['metodo'],
                $dados['id'],
                $usuario_id
            ]);
        } else {
            // Inserir nova transação
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
        }
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

// --- LIMPAR MÊS (PATCH) ---
// CORREÇÃO: método PATCH implementado para o botão "Limpar Mês"
if ($method === 'PATCH') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $mes  = $dados['mes']  ?? null;
    $tipo = $dados['tipo'] ?? 'tudo';

    if (!$mes) {
        echo json_encode(['success' => false, 'error' => 'Mês não informado.']);
        exit;
    }

    try {
        if ($tipo === 'tudo') {
            $stmt = $pdo->prepare("DELETE FROM transacoes WHERE usuario_id = ? AND data LIKE ?");
            $stmt->execute([$usuario_id, $mes . '%']);
        } else {
            // 'receita' ou 'despesa'
            $stmt = $pdo->prepare("DELETE FROM transacoes WHERE usuario_id = ? AND data LIKE ? AND tipo = ?");
            $stmt->execute([$usuario_id, $mes . '%', $tipo]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>