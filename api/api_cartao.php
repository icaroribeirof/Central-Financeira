<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$acao = $_GET['acao'] ?? '';

try {
    if ($acao === 'buscar') {
        $mes_fatura = $_GET['mes'] ?? date('Y-m');
        [$ano_fat, $mes_fat] = explode('-', $mes_fatura);

        // Busca todos os cartões do usuário
        $stmt = $pdo->prepare("SELECT * FROM cartoes WHERE usuario_id = ? ORDER BY nome ASC");
        $stmt->execute([$usuario_id]);
        $cartoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cartoes as &$cartao) {
            $dia_fech = (int)$cartao['dia_fechamento'];

            // Regra: a fatura do mês M fecha no dia X do próprio mês M.
            // O período de compras vai do dia X+1 do mês M-1 até o dia X do mês M.
            // Ex: fatura Abril, fecha dia 5 → período 06/Mar até 05/Abr.

            $mes_anterior = date('Y-m', mktime(0, 0, 0, (int)$mes_fat - 1, 1, (int)$ano_fat));

            // Último dia do mês anterior (para não ultrapassar)
            $ultimo_dia_mes_ant = (int)date('t', strtotime($mes_anterior . '-01'));
            $dia_inicio = min($dia_fech + 1, $ultimo_dia_mes_ant);

            // Último dia do mês da fatura (para não ultrapassar o dia_fech)
            $ultimo_dia_mes_fat = (int)date('t', strtotime($mes_fatura . '-01'));
            $dia_fim = min($dia_fech, $ultimo_dia_mes_fat);

            $data_ini = $mes_anterior . '-' . str_pad($dia_inicio, 2, '0', STR_PAD_LEFT);
            $data_fim = $mes_fatura   . '-' . str_pad($dia_fim,    2, '0', STR_PAD_LEFT);

            $q = $pdo->prepare(
                "SELECT SUM(valor) as total
                 FROM transacoes
                 WHERE metodo = ? AND usuario_id = ? AND tipo = 'despesa'
                 AND data BETWEEN ? AND ?"
            );
            $q->execute([$cartao['nome'], $usuario_id, $data_ini, $data_fim]);
            $row = $q->fetch();

            $cartao['total_gasto']  = $row['total'] ?? 0;
            $cartao['periodo_ini']  = $data_ini;
            $cartao['periodo_fim']  = $data_fim;
        }
        unset($cartao);

        echo json_encode(['cartoes' => $cartoes]);

    } elseif ($acao === 'salvar') {
        $input        = json_decode(file_get_contents('php://input'), true);
        $id           = $input['id']            ?? null;
        $nome         = $input['nome'];
        $limite       = $input['limite'];
        $dia_fech     = max(1, min(31, (int)($input['dia_fechamento'] ?? 1)));

        if ($id) {
            $stmt = $pdo->prepare(
                "UPDATE cartoes SET nome = ?, limite = ?, dia_fechamento = ? WHERE id = ? AND usuario_id = ?"
            );
            $stmt->execute([$nome, $limite, $dia_fech, $id, $usuario_id]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO cartoes (nome, limite, dia_fechamento, usuario_id) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$nome, $limite, $dia_fech, $usuario_id]);
        }
        echo json_encode(['sucesso' => true]);

    } elseif ($acao === 'excluir') {
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt  = $pdo->prepare("DELETE FROM cartoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$input['id'], $usuario_id]);
        echo json_encode(['sucesso' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?>