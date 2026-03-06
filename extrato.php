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
    <link rel="stylesheet" href="css/extrato.css">
    <link rel="shortcut icon" href="icon/money-bag.png">
    <title>Extrato - Central Financeira</title>
</head>
<body>
    
    <?php include 'includes/menu.php'; ?>

    <main>
        <div class="header-extrato">
            <h2>Histórico de Movimentações</h2>
            <div class="header-btns">
                <button class="btn-novo" id="btn-abrir-cadastro">+ Novo Lançamento</button>
                <button class="btn-limpeza" id="btn-abrir-limpeza">🗑️ Limpar Mês</button>
            </div>
        </div>

        <div class="toolbar">
            <div class="col-input">
                <label>Filtrar Mês:</label>
                <input type="month" id="filtro-mes">
            </div>
            <div class="col-input">
                <label>Buscar Descrição:</label>
                <input type="text" id="input-busca" placeholder="Ex: Mercado, Aluguel...">
            </div>
            <div class="col-input">
                <label>Ordenar por:</label>
                <select id="ordem-select">
                    <option value="data-desc">Data (Mais recente)</option>
                    <option value="data-asc">Data (Mais antigo)</option>
                    <option value="valor-desc">Maior Valor</option>
                    <option value="valor-asc">Menor Valor</option>
                </select>
            </div>
        </div>

        <div id="lista-transacoes"></div>
    </main>

    <div id="modal-cadastro" class="modal">
        <div class="modal-content">
            <h3 id="modal-titulo">Novo Lançamento</h3>
            <form id="form-transacao">
                <div class="grid-form">
                    <div class="col-input">
                        <label>Descrição</label>
                        <input type="text" id="desc" placeholder="Ex: Compra no mercado" required>
                    </div>
                    <div class="col-input">
                        <label>Valor (R$)</label>
                        <input type="number" id="valor" step="0.01" placeholder="0,00" required>
                    </div>
                    <div class="col-input">
                        <label>Data</label>
                        <input type="date" id="data-lancamento" required>
                    </div>
                    <div class="col-input">
                        <label>Tipo</label>
                        <select id="tipo-transacao" required>
                            <option value="despesa">Despesa</option>
                            <option value="receita">Receita</option>
                        </select>
                    </div>
                    <div class="col-input">
                        <label>Categoria</label>
                        <select id="categoria-select" required>
                            </select>
                    </div>
                    <div class="col-input">
                        <label>Forma de Pagamento</label>
                        <select id="metodo-pagamento" required>
                            </select>
                    </div>
                </div>
                <br>
                <div class="modal-footer">
                    <button type="submit" class="btn-novo">Salvar Registro</button>
                    <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-limpeza" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h3>Limpar Registros</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px;">
                Selecione o que deseja excluir do mês selecionado:
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="executarLimpeza('tudo')" style="background-color: #e74c3c; color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">🗑️ Todas as Movimentações</button>
                <button onclick="executarLimpeza('despesa')" style="background-color: #f39c12; color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">📉 Somente Despesas</button>
                <button onclick="executarLimpeza('receita')" style="background-color: #2ecc71; color: white; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">📈 Somente Receitas</button>
                <button onclick="fecharModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; margin-top: 10px;">Fechar</button>
            </div>
        </div>
    </div>

    <script src="js/extrato.js"></script>
</body>
</html>
