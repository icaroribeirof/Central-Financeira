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
    <script src="js/animations.js"></script>
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
                <button class="btn-resetar" id="btn-resetar-filtros">🔄 Resetar Filtros</button>
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
                <label>Filtrar Categoria:</label>
                <select id="filtro-categoria">
                    <option value="">Todas as Categorias</option>
                </select>
            </div>
            <div class="col-input">
                <label>Filtrar Forma de Pagamento:</label>
                <select id="filtro-metodo">
                    <option value="">Todas as Formas</option>
                </select>
            </div>
            <div class="col-input">
                <label>Filtrar Assinatura:</label>
                <select id="filtro-assinatura">
                    <option value="">Todos</option>
                    <option value="assinatura">Assinatura</option>
                    <option value="nao-assinatura">Não Assinatura</option>
                </select>
            </div>
            <div class="col-input">
                <label>Filtrar Cartão:</label>
                <select id="filtro-cartao">
                    <option value="">Todos</option>
                    <option value="cartao">Cartão</option>
                    <option value="nao-cartao">Não Cartão</option>
                </select>
            </div>
            <div class="col-input">
                <label>Ordenar por:</label>
                <select id="ordem-select">
                    <option value="data-asc">Data (Mais antigo)</option>
                    <option value="data-desc">Data (Mais recente)</option>
                    <option value="valor-desc">Maior Valor</option>
                    <option value="valor-asc">Menor Valor</option>
                </select>
            </div>
        </div>

        <div id="resumo-filtros" class="resumo-filtros" style="display: none;">
            <div class="card-resumo-filtro receitas">
                <span class="label">Total Receitas</span>
                <p id="soma-receitas">R$ 0,00</p>
            </div>
            <div class="card-resumo-filtro despesas">
                <span class="label">Total Despesas</span>
                <p id="soma-despesas">R$ 0,00</p>
            </div>
            <div class="card-resumo-filtro saldo">
                <span class="label">Saldo</span>
                <p id="soma-saldo">R$ 0,00</p>
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

                    <!-- NOVO: Tipo de lançamento -->
                    <div class="col-input col-full">
                        <label>Tipo de Lançamento</label>
                        <div class="tipo-lancamento-grupo">
                            <label class="radio-card" id="radio-unico">
                                <input type="radio" name="tipo_lancamento" value="unico" checked>
                                <span class="radio-icon">1×</span>
                                <span class="radio-label">Único</span>
                            </label>
                            <label class="radio-card" id="radio-recorrente">
                                <input type="radio" name="tipo_lancamento" value="recorrente">
                                <span class="radio-icon">🔁</span>
                                <span class="radio-label">Recorrente</span>
                            </label>
                            <label class="radio-card" id="radio-parcelado">
                                <input type="radio" name="tipo_lancamento" value="parcelado">
                                <span class="radio-icon">📅</span>
                                <span class="radio-label">Parcelado</span>
                            </label>
                        </div>
                    </div>

                    <!-- NOVO: Campo de parcelas (visível só quando parcelado) -->
                    <div class="col-input col-full" id="campo-parcelas" style="display:none;">
                        <label>Número de Parcelas</label>
                        <input type="number" id="total-parcelas" min="2" max="360" value="2" placeholder="Ex: 12">
                        <span class="hint-parcelas" id="hint-parcelas"></span>
                    </div>

                    <!-- INFO: aviso recorrente -->
                    <div class="col-input col-full" id="aviso-recorrente" style="display:none;">
                        <p class="info-box">🔁 Serão gerados lançamentos mensais automáticos para os próximos <strong>24 meses</strong> a partir da data selecionada.</p>
                    </div>

                    <!-- NOVO: Checkbox de Assinatura -->
                    <div class="col-input col-full" style="display: flex; flex-direction: row; align-items: center; gap: 10px; margin-top: 10px;">
                        <input type="checkbox" id="eh-assinatura" style="width: 20px; height: 20px; cursor: pointer;">
                        <label for="eh-assinatura" style="margin: 0; text-transform: none; font-weight: normal; cursor: pointer;">
                            ✓ Marcar como assinatura ou serviço recorrente
                        </label>
                    </div>

                    <!-- NOVO: Checkbox de Cartão -->
                    <div class="col-input col-full" style="display: flex; flex-direction: row; align-items: center; gap: 10px; margin-top: 10px;">
                        <input type="checkbox" id="eh-cartao" style="width: 20px; height: 20px; cursor: pointer;">
                        <label for="eh-cartao" style="margin: 0; text-transform: none; font-weight: normal; cursor: pointer;">
                            💳 Marcar como transação em cartão
                        </label>
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

    <!-- Modal: escolha de escopo ao EDITAR parcelado/recorrente -->
    <div id="modal-editar-grupo" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <h3 id="editar-grupo-titulo">Salvar Alteração</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px;" id="editar-grupo-subtitulo"></p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button id="btn-salvar-unico"   style="background-color:#3498db;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Alterar só este lançamento</button>
                <button id="btn-salvar-futuros" style="background-color:#f39c12;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Alterar este e os seguintes</button>
                <button onclick="fecharModalEdicao()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;margin-top:8px;">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal: escolha de escopo ao excluir parcelado/recorrente -->
    <div id="modal-excluir-grupo" class="modal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <h3 id="excluir-titulo">Excluir Lançamento</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px;" id="excluir-subtitulo"></p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button id="btn-excluir-unico"  style="background-color:#e74c3c;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Excluir só este lançamento</button>
                <button id="btn-excluir-futuros" style="background-color:#f39c12;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Excluir este e os seguintes</button>
                <button id="btn-excluir-grupo"  style="background-color:#8e44ad;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:bold;">Excluir todos do grupo</button>
                <button onclick="fecharModal()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;margin-top:8px;">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="js/extrato.js"></script>
</body>
</html>
