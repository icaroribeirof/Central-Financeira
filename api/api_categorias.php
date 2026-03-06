<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

// Verifica se o usuário está logado na sessão
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$method = $_SERVER['REQUEST_METHOD'];

// --- LISTAR CATEGORIAS (GET) ---
if ($method === 'GET') {
    try {
        // Busca apenas categorias que pertencem ao usuário logado
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE usuario_id = ? ORDER BY nome ASC");
        $stmt->execute([$usuario_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- SALVAR OU EDITAR (POST) ---
if ($method === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $nome = $dados['nome'] ?? '';
    $id = $dados['id'] ?? null;

    if (empty($nome)) {
        echo json_encode(['success' => false, 'error' => 'O nome da categoria é obrigatório.']);
        exit;
    }

    try {
        if ($id) {
            // Edita apenas se a categoria pertencer ao usuário logado
            $stmt = $pdo->prepare("UPDATE categorias SET nome = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$nome, $id, $usuario_id]);
        } else {
            // Insere vinculando ao usuário logado
            $stmt = $pdo->prepare("INSERT INTO categorias (nome, usuario_id) VALUES (?, ?)");
            $stmt->execute([$nome, $usuario_id]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- EXCLUIR (DELETE) ---
if ($method === 'DELETE') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $id = $dados['id'] ?? null;

    try {
        // Deleta apenas se a categoria pertencer ao usuário logado
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>