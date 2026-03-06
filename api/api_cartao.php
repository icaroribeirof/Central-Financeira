<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

// Verifica se o utilizador está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$acao = $_GET['acao'] ?? '';

try {
    if ($acao === 'buscar') {
        // Busca cartões e calcula o total gasto no mês filtrado
        $mes = $_GET['mes'] ?? date('Y-m');
        
        $sql = "SELECT c.*, 
                (SELECT SUM(valor) FROM transacoes t 
                 WHERE t.metodo = c.nome 
                 AND t.usuario_id = c.usuario_id 
                 AND t.data LIKE ?) as total_gasto
                FROM cartoes c 
                WHERE c.usuario_id = ? 
                ORDER BY c.nome ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mes . '%', $usuario_id]);
        echo json_encode(['cartoes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    
    elseif ($acao === 'salvar') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $nome = $input['nome'];
        $limite = $input['limite'];

        if ($id) {
            // EDITAR cartão existente
            $stmt = $pdo->prepare("UPDATE cartoes SET nome = ?, limite = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$nome, $limite, $id, $usuario_id]);
        } else {
            // INSERIR novo cartão
            $stmt = $pdo->prepare("INSERT INTO cartoes (nome, limite, usuario_id) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $limite, $usuario_id]);
        }
        echo json_encode(['sucesso' => true]);
    }

    elseif ($acao === 'excluir') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];
        
        $stmt = $pdo->prepare("DELETE FROM cartoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        echo json_encode(['sucesso' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}