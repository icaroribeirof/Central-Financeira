<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$acao = $_GET['acao'] ?? '';

try {
    if ($acao === 'cadastrar') {
        // Verifica se o email já existe
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$input['email']]);
        if ($check->fetch()) {
            echo json_encode(['sucesso' => false, 'erro' => 'Este e-mail já está cadastrado.']);
            exit;
        }

        $senhaHash = password_hash($input['senha'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$input['nome'], $input['email'], $senhaHash]);
        echo json_encode(['sucesso' => true]);
    } 
    
    elseif ($acao === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$input['email']]);
        $user = $stmt->fetch();

        if ($user && password_verify($input['senha'], $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha inválidos.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro no banco de dados.']);
}
?>