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
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="shortcut icon" href="icon/money-bag.png">
    <title>Categorias - Central Financeira</title>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main>
        <div class="header-extrato">
            <h2>Categorias</h2>
            <button class="btn-novo" id="btn-abrir-cadastro">+ Nova Categoria</button>
        </div>

        <div class="toolbar-cat">
            <div class="col-input">
                <label>Buscar Categoria</label>
                <input type="text" id="input-busca" placeholder="Digite o nome...">
            </div>
            <div class="col-input" style="flex: 0.5;">
                <label>Ordenar</label>
                <select id="ordem-select">
                    <option value="alfa">A - Z</option>
                    <option value="alfa-desc">Z - A</option>
                </select>
            </div>
        </div>

        <div id="lista-categorias"></div>
    </main>

    <div id="modal-categoria" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Nova Categoria</h3>
            <form id="form-categoria">
                <div class="col-input" style="margin-bottom: 20px;">
                    <label>Nome da Categoria</label>
                    <input type="text" id="nome-cat" placeholder="Ex: Lazer, Saúde..." required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-novo" style="flex: 2;">Salvar</button>
                    <button type="button" onclick="fecharModal()" style="flex: 1; background: #95a5a6; color: white; border: none; border-radius: 8px; cursor: pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/categorias.js"></script>
</body>
</html>
