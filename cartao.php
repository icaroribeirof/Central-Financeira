<?php
require_once 'db_connect.php';

// Se não houver sessão, volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cartao.css">
    <link rel="shortcut icon" href="icon/money-bag.png">
    <title>Cartões - Central Financeira</title>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main>
        <div class="header-cartao">
            <h2>Meus Cartões</h2>
            <button class="btn-novo" id="btn-abrir-modal">+ Novo Cartão</button>
        </div>

        <div class="toolbar-cartao">
            <div class="col-input">
                <label>Buscar Cartão</label>
                <input type="text" id="busca-cartao" placeholder="Nome do cartão...">
            </div>
            <div class="col-input" style="max-width: 200px;">
                <label>Ver Fatura de:</label>
                <input type="month" id="filtro-data">
            </div>
        </div>

        <div id="lista-cartoes" class="container-cartoes">
            </div>
    </main>

    <div id="modal-cartao" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Novo Cartão</h3>
            <form id="form-cartao">
                <div class="col-input" style="margin-bottom: 15px;">
                    <label>Nome do Cartão</label>
                    <input type="text" id="nome-cartao" placeholder="Ex: Nubank, Inter..." required>
                </div>
                <div class="col-input" style="margin-bottom: 15px;">
                    <label>Limite Total (R$)</label>
                    <input type="number" id="limite-total" step="0.01" placeholder="0.00" required>
                </div>
                <div class="col-input" style="margin-bottom: 15px;">
                    <label>Dia de Fechamento</label>
                    <input type="number" id="dia-fechamento" min="1" max="31" placeholder="Ex: 10" required>
                    <span style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">Compras até este dia entram na fatura do mês seguinte.</span>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-novo" style="flex: 2;">Salvar</button>
                    <button type="button" onclick="fecharModal()" style="flex: 1; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/cartao.js"></script>
</body>
</html>
