<?php
require_once '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$method     = $_SERVER['REQUEST_METHOD'];

// ─── GET: listar transações e métodos ───────────────────────────────────────
if ($method === 'GET') {
    try {
        $mes = $_GET['mes'] ?? null;

        if ($mes) {
            $stmt = $pdo->prepare(
                "SELECT * FROM transacoes
                 WHERE usuario_id = ? AND data LIKE ?
                 ORDER BY data DESC, id DESC"
            );
            $stmt->execute([$usuario_id, $mes . '%']);
        } else {
            $stmt = $pdo->prepare(
                "SELECT * FROM transacoes
                 WHERE usuario_id = ?
                 ORDER BY data DESC, id DESC"
            );
            $stmt->execute([$usuario_id]);
        }
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt_cartoes = $pdo->prepare(
            "SELECT nome FROM cartoes WHERE usuario_id = ? ORDER BY nome ASC"
        );
        $stmt_cartoes->execute([$usuario_id]);
        $cartoes = $stmt_cartoes->fetchAll(PDO::FETCH_COLUMN);

        $metodos = array_merge(['Dinheiro', 'PIX'], $cartoes);

        echo json_encode(['transacoes' => $transacoes, 'metodos' => $metodos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── POST: criar ou editar transação ────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true);

    try {
        // ── EDIÇÃO de lançamento existente ──
        if (!empty($d['id'])) {
            $escopo          = $d['escopo_edicao']   ?? 'unico';
            $novo_tipo_lanc  = $d['tipo_lancamento']  ?? 'unico';
            $total_parcelas  = (int)($d['total_parcelas'] ?? 1);

            // Busca dados atuais do lançamento (grupo_id, parcela_atual, tipo_lancamento)
            $info = $pdo->prepare(
                "SELECT grupo_id, parcela_atual, tipo_lancamento, data
                 FROM transacoes WHERE id = ? AND usuario_id = ?"
            );
            $info->execute([$d['id'], $usuario_id]);
            $t = $info->fetch();

            if (!$t) {
                echo json_encode(['success' => false, 'error' => 'Lançamento não encontrado.']);
                exit;
            }

            $tipo_original = $t['tipo_lancamento'];
            $data_base     = $d['data'];

            // ── Caso 1: tipo NÃO mudou — atualiza campos normalmente ──
            if ($novo_tipo_lanc === $tipo_original) {
                if ($escopo === 'futuros' && $t['grupo_id']) {
                    $stmt = $pdo->prepare(
                        "UPDATE transacoes
                         SET descricao = ?, valor = ?, tipo = ?, categoria = ?, metodo = ?
                         WHERE grupo_id = ? AND usuario_id = ? AND parcela_atual >= ?"
                    );
                    $stmt->execute([
                        $d['descricao'], $d['valor'], $d['tipo'],
                        $d['categoria'], $d['metodo'],
                        $t['grupo_id'], $usuario_id, $t['parcela_atual']
                    ]);
                } else {
                    // escopo 'unico'
                    $stmt = $pdo->prepare(
                        "UPDATE transacoes
                         SET descricao = ?, valor = ?, data = ?, tipo = ?,
                             categoria = ?, metodo = ?
                         WHERE id = ? AND usuario_id = ?"
                    );
                    $stmt->execute([
                        $d['descricao'], $d['valor'], $data_base,
                        $d['tipo'], $d['categoria'], $d['metodo'],
                        $d['id'], $usuario_id
                    ]);
                }

            // ── Caso 2: tipo mudou — exclui este item e gera os novos ──
            } else {
                // Remove apenas este lançamento
                $del = $pdo->prepare(
                    "DELETE FROM transacoes WHERE id = ? AND usuario_id = ?"
                );
                $del->execute([$d['id'], $usuario_id]);

                // Gera novo grupo_id para os registros recriados
                $grupo_id = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );

                $sql = "INSERT INTO transacoes
                            (descricao, valor, data, tipo, categoria, metodo, usuario_id,
                             tipo_lancamento, grupo_id, parcela_atual, total_parcelas)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $ins = $pdo->prepare($sql);

                if ($novo_tipo_lanc === 'unico') {
                    $ins->execute([
                        $d['descricao'], $d['valor'], $data_base,
                        $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                        'unico', null, null, null
                    ]);

                } elseif ($novo_tipo_lanc === 'parcelado') {
                    $valor_parcela = round($d['valor'] / $total_parcelas, 2);
                    for ($i = 0; $i < $total_parcelas; $i++) {
                        $data_p = date('Y-m-d', strtotime("$data_base +$i months"));
                        $ins->execute([
                            $d['descricao'], $valor_parcela, $data_p,
                            $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                            'parcelado', $grupo_id, ($i + 1), $total_parcelas
                        ]);
                    }

                } elseif ($novo_tipo_lanc === 'recorrente') {
                    $MESES = 24;
                    for ($i = 0; $i < $MESES; $i++) {
                        $data_r = date('Y-m-d', strtotime("$data_base +$i months"));
                        $ins->execute([
                            $d['descricao'], $d['valor'], $data_r,
                            $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                            'recorrente', $grupo_id, ($i + 1), $MESES
                        ]);
                    }
                }
            }

            echo json_encode(['success' => true]);
            exit;
        }

        // ── NOVO lançamento ──
        $tipo_lancamento = $d['tipo_lancamento'] ?? 'unico';
        $total_parcelas  = (int)($d['total_parcelas'] ?? 1);
        $data_base       = $d['data']; // formato YYYY-MM-DD

        // Gera um UUID v4 simples para agrupar parcelas/recorrências
        $grupo_id = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $sql = "INSERT INTO transacoes
                    (descricao, valor, data, tipo, categoria, metodo, usuario_id,
                     tipo_lancamento, grupo_id, parcela_atual, total_parcelas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($tipo_lancamento === 'unico') {
            // Lançamento simples — insere 1 linha
            $stmt->execute([
                $d['descricao'], $d['valor'], $data_base,
                $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                'unico', null, null, null
            ]);

        } elseif ($tipo_lancamento === 'parcelado') {
            // Gera N parcelas mensais a partir da data base
            // O valor total é dividido entre as parcelas
            $valor_parcela = round($d['valor'] / $total_parcelas, 2);
            for ($i = 0; $i < $total_parcelas; $i++) {
                $data_parcela = date('Y-m-d', strtotime("$data_base +$i months"));
                $stmt->execute([
                    $d['descricao'], $valor_parcela, $data_parcela,
                    $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                    'parcelado', $grupo_id, ($i + 1), $total_parcelas
                ]);
            }

        } elseif ($tipo_lancamento === 'recorrente') {
            // Gera 24 meses de recorrência a partir da data base
            $MESES_RECORRENCIA = 24;
            for ($i = 0; $i < $MESES_RECORRENCIA; $i++) {
                $data_rec = date('Y-m-d', strtotime("$data_base +$i months"));
                $stmt->execute([
                    $d['descricao'], $d['valor'], $data_rec,
                    $d['tipo'], $d['categoria'], $d['metodo'], $usuario_id,
                    'recorrente', $grupo_id, ($i + 1), $MESES_RECORRENCIA
                ]);
            }
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── DELETE: excluir transação ───────────────────────────────────────────────
// escopo: 'unico' (só esta) | 'grupo' (todas do grupo) | 'futuros' (esta e futuras)
if ($method === 'DELETE') {
    $d      = json_decode(file_get_contents('php://input'), true);
    $id     = $d['id']     ?? null;
    $escopo = $d['escopo'] ?? 'unico';

    try {
        // Busca a transação para obter grupo_id, data e parcela_atual
        $info = $pdo->prepare(
            "SELECT grupo_id, data, parcela_atual, tipo_lancamento
             FROM transacoes WHERE id = ? AND usuario_id = ?"
        );
        $info->execute([$id, $usuario_id]);
        $t = $info->fetch();

        if (!$t) {
            echo json_encode(['success' => false, 'error' => 'Transação não encontrada.']);
            exit;
        }

        if ($escopo === 'grupo' && $t['grupo_id']) {
            // Exclui todas do grupo
            $stmt = $pdo->prepare(
                "DELETE FROM transacoes WHERE grupo_id = ? AND usuario_id = ?"
            );
            $stmt->execute([$t['grupo_id'], $usuario_id]);

        } elseif ($escopo === 'futuros' && $t['grupo_id']) {
            // Exclui esta e as parcelas/recorrências futuras (parcela >= atual)
            $stmt = $pdo->prepare(
                "DELETE FROM transacoes
                 WHERE grupo_id = ? AND usuario_id = ? AND parcela_atual >= ?"
            );
            $stmt->execute([$t['grupo_id'], $usuario_id, $t['parcela_atual']]);

        } else {
            // Exclui apenas esta linha
            $stmt = $pdo->prepare(
                "DELETE FROM transacoes WHERE id = ? AND usuario_id = ?"
            );
            $stmt->execute([$id, $usuario_id]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ─── PATCH: limpar mês ───────────────────────────────────────────────────────
if ($method === 'PATCH') {
    $d    = json_decode(file_get_contents('php://input'), true);
    $mes  = $d['mes']  ?? null;
    $tipo = $d['tipo'] ?? 'tudo';

    if (!$mes) {
        echo json_encode(['success' => false, 'error' => 'Mês não informado.']);
        exit;
    }

    try {
        if ($tipo === 'tudo') {
            $stmt = $pdo->prepare(
                "DELETE FROM transacoes WHERE usuario_id = ? AND data LIKE ?"
            );
            $stmt->execute([$usuario_id, $mes . '%']);
        } else {
            $stmt = $pdo->prepare(
                "DELETE FROM transacoes WHERE usuario_id = ? AND data LIKE ? AND tipo = ?"
            );
            $stmt->execute([$usuario_id, $mes . '%', $tipo]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>