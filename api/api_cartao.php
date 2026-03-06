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

            // Regra: compras ATÉ o dia de fechamento entram na fatura do mês seguinte (M+1).
            //        compras APÓS o dia de fechamento entram na fatura de dois meses à frente (M+2).
            //
            // Invertendo para a busca: dado que queremos a fatura de $mes_fatura,
            //   as compras que a compõem vieram de dois intervalos distintos:
            //     - Dias (dia_fech+1) até fim do mês  de (mes_fatura - 2)
            //     - Dias 1 até dia_fech               de (mes_fatura - 1)

            $mes_m2  = date('Y-m', mktime(0,0,0, (int)$mes_fat - 2, 1, (int)$ano_fat));
            $mes_m1  = date('Y-m', mktime(0,0,0, (int)$mes_fat - 1, 1, (int)$ano_fat));

            // Último dia do mês M-2
            $ultimo_dia_m2 = (int)date('t', strtotime($mes_m2 . '-01'));
            // Garante que dia_fechamento não ultrapasse o último dia do mês
            $dia_fech_m1 = min($dia_fech, (int)date('t', strtotime($mes_m1 . '-01')));

            $data_ini = $mes_m2 . '-' . str_pad($dia_fech + 1, 2, '0', STR_PAD_LEFT);
            $data_fim = $mes_m1 . '-' . str_pad($dia_fech_m1, 2, '0', STR_PAD_LEFT);

            // Se dia_fech = último dia do mês, início é o dia 1 do mês M-1
            if ($dia_fech >= $ultimo_dia_m2) {
                $data_ini = $mes_m1 . '-01';
            }

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