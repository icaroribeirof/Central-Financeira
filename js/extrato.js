document.addEventListener('DOMContentLoaded', () => {
    // ── Elementos ────────────────────────────────────────────────────────────
    const form           = document.getElementById('form-transacao');
    const descInput      = document.getElementById('desc');
    const valorInput     = document.getElementById('valor');
    const dataInput      = document.getElementById('data-lancamento');
    const tipoInput      = document.getElementById('tipo-transacao');
    const catSelect      = document.getElementById('categoria-select');
    const metSelect      = document.getElementById('metodo-pagamento');

    const filtroMesInput = document.getElementById('filtro-mes');
    const buscaInput     = document.getElementById('input-busca');
    const ordemSelect    = document.getElementById('ordem-select');
    const listaDiv       = document.getElementById('lista-transacoes');
    const filtroCategoria = document.getElementById('filtro-categoria');
    const filtroMetodo   = document.getElementById('filtro-metodo');
    const filtroAssinatura = document.getElementById('filtro-assinatura');
    const filtroCartao   = document.getElementById('filtro-cartao');
    const resumoFiltros  = document.getElementById('resumo-filtros');

    const modalCadastro  = document.getElementById('modal-cadastro');
    const modalLimpeza   = document.getElementById('modal-limpeza');
    const modalGrupo     = document.getElementById('modal-excluir-grupo');
    const btnAbrirCadastro = document.getElementById('btn-abrir-cadastro');
    const btnAbrirLimpeza  = document.getElementById('btn-abrir-limpeza');
    const btnResetarFiltros = document.getElementById('btn-resetar-filtros');

    // Novos elementos de tipo de lançamento
    const campoParcelas   = document.getElementById('campo-parcelas');
    const totalParcelasIn = document.getElementById('total-parcelas');
    const hintParcelas    = document.getElementById('hint-parcelas');
    const avisoRecorrente = document.getElementById('aviso-recorrente');
    const ehAssinaturaInput = document.getElementById('eh-assinatura');
    const ehCartaoInput = document.getElementById('eh-cartao');

    // Setar mês atual no filtro
    const hoje = new Date();
    filtroMesInput.value = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, '0')}`;

    let idEdicao           = null;
    let tipoLancamentoEdicao = 'unico'; // guarda o tipo do item sendo editado

    // ── Lógica dos radio cards (único / recorrente / parcelado) ──────────────
    document.querySelectorAll('input[name="tipo_lancamento"]').forEach(radio => {
        radio.addEventListener('change', () => atualizarUiTipoLancamento());
    });

    valorInput.addEventListener('input', () => atualizarHintParcelas());
    totalParcelasIn.addEventListener('input', () => atualizarHintParcelas());

    function getTipoLancamento() {
        const sel = document.querySelector('input[name="tipo_lancamento"]:checked');
        return sel ? sel.value : 'unico';
    }

    function atualizarUiTipoLancamento() {
        const tipo = getTipoLancamento();
        campoParcelas.style.display   = tipo === 'parcelado'   ? 'block' : 'none';
        avisoRecorrente.style.display = tipo === 'recorrente'  ? 'block' : 'none';
        // highlight radio card ativo
        document.querySelectorAll('.radio-card').forEach(card => card.classList.remove('ativo'));
        const checkedRadio = document.querySelector('input[name="tipo_lancamento"]:checked');
        if (checkedRadio) checkedRadio.closest('.radio-card').classList.add('ativo');
        atualizarHintParcelas();
    }

    function atualizarHintParcelas() {
        const n     = parseInt(totalParcelasIn.value) || 0;
        const total = parseFloat(valorInput.value) || 0;
        if (n > 0 && total > 0) {
            const parcela = (total / n).toFixed(2).replace('.', ',');
            hintParcelas.textContent = `${n}× de R$ ${parcela} — Total: R$ ${total.toFixed(2).replace('.', ',')}`;
        } else {
            hintParcelas.textContent = '';
        }
    }

    // ── Popular selects ──────────────────────────────────────────────────────
    async function popularSelects() {
        try {
            const resCat = await fetch('api/api_categorias.php');
            const categorias = await resCat.json();
            catSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
            
            // Popular filtro de categorias também
            filtroCategoria.innerHTML = '<option value="">Todas as Categorias</option>';
            
            if (Array.isArray(categorias)) {
                categorias.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value       = c.nome;
                    opt.textContent = c.nome;
                    catSelect.appendChild(opt);
                    
                    // Adicionar também ao filtro
                    const optFiltro = document.createElement('option');
                    optFiltro.value       = c.nome;
                    optFiltro.textContent = c.nome;
                    filtroCategoria.appendChild(optFiltro);
                });
            }

            let cartoes = [];
            try {
                const resCard  = await fetch('api/api_cartao.php?acao=buscar');
                const dataCard = await resCard.json();
                cartoes = dataCard.cartoes || [];
            } catch (e) { console.error('Erro ao buscar cartões', e); }

            metSelect.innerHTML = `
                <option value="Boleto">Boleto</option>    
                <option value="Débito">Débito</option>    
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
            `;
            
            // Popular filtro de métodos também
            filtroMetodo.innerHTML = '<option value="">Todas as Formas</option>';
            
            // Adicionar métodos padrão ao filtro
            const metodosPadrao = [
                { valor: 'Boleto', label: 'Boleto' },
                { valor: 'Débito', label: 'Débito' },
                { valor: 'Dinheiro', label: 'Dinheiro' },
                { valor: 'Pix', label: 'Pix' }
            ];
            
            metodosPadrao.forEach(m => {
                const optFiltro = document.createElement('option');
                optFiltro.value = m.valor;
                optFiltro.textContent = m.label;
                filtroMetodo.appendChild(optFiltro);
            });
            
            // Adicionar cartões ao formulário e ao filtro
            cartoes.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.nome;
                opt.textContent = c.nome;
                metSelect.appendChild(opt);
                
                // Adicionar também ao filtro
                const optFiltro = document.createElement('option');
                optFiltro.value = c.nome;
                optFiltro.textContent = c.nome;
                filtroMetodo.appendChild(optFiltro);
            });
        } catch (e) { console.error('Erro ao popular selects', e); }
    }

    // ── Abrir / fechar modais ────────────────────────────────────────────────
    btnAbrirCadastro.onclick = () => {
        idEdicao             = null;
        tipoLancamentoEdicao = 'unico';
        form.reset();
        ehAssinaturaInput.checked = false;  // ✅ NOVO - Reset checkbox
        ehCartaoInput.checked = false;      // ✅ NOVO - Reset checkbox cartão
        document.getElementById('modal-titulo').innerText = 'Novo Lançamento';
        // Reabilita e reseta tipo lançamento para 'unico'
        document.querySelectorAll('input[name="tipo_lancamento"]').forEach(radio => {
            radio.disabled = false;
            radio.checked  = (radio.value === 'unico');
        });
        totalParcelasIn.disabled = false;
        atualizarUiTipoLancamento();
        document.getElementById('radio-unico').closest('.col-full').style.display = '';
        campoParcelas.style.display   = 'none';
        avisoRecorrente.style.display = 'none';
        modalCadastro.style.display = 'flex';
        popularSelects();
    };

    const modalEditarGrupo = document.getElementById('modal-editar-grupo');

    btnAbrirLimpeza.onclick = () => modalLimpeza.style.display = 'flex';

    // ── Resetar Filtros ──────────────────────────────────────────────────────
    btnResetarFiltros.onclick = () => {
        // Reseta todos os filtros para os valores padrão
        filtroMesInput.value = `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`;
        buscaInput.value = '';
        filtroCategoria.value = '';
        filtroMetodo.value = '';
        filtroAssinatura.value = '';
        filtroCartao.value = '';
        ordemSelect.value = 'data-asc';
        
        // Recarrega o histórico com os filtros resetados
        carregarHistorico();
    };

    window.fecharModal = () => {
        modalCadastro.style.display  = 'none';
        modalLimpeza.style.display   = 'none';
        modalGrupo.style.display     = 'none';
        modalEditarGrupo.style.display = 'none';
    };

    window.fecharModalEdicao = () => {
        modalEditarGrupo.style.display = 'none';
    };

    // ── Salvar lançamento ────────────────────────────────────────────────────
    const executarSalvar = async (escopo_edicao) => {
        const tipo_lancamento = getTipoLancamento();
        const dados = {
            id:              idEdicao,
            descricao:       descInput.value,
            valor:           parseFloat(valorInput.value),
            data:            dataInput.value,
            tipo:            tipoInput.value,
            categoria:       catSelect.value,
            metodo:          metSelect.value,
            tipo_lancamento: tipo_lancamento,
            total_parcelas:  tipo_lancamento === 'parcelado' ? parseInt(totalParcelasIn.value) : null,
            escopo_edicao:   escopo_edicao,
            eh_assinatura:   ehAssinaturaInput.checked ? 1 : 0,
            eh_cartao:       ehCartaoInput.checked ? 1 : 0,
        };

        const res    = await fetch('api/api_extrato.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(dados),
        });
        const result = await res.json();
        if (result.success || result.sucesso) {
            fecharModal();
            carregarHistorico();
        } else {
            alert('Erro ao salvar: ' + (result.error || result.erro));
        }
    };

    form.onsubmit = async (e) => {
        e.preventDefault();

        // Novo lançamento: salva direto
        if (!idEdicao) {
            await executarSalvar('unico');
            return;
        }

        const tipoSelecionado = getTipoLancamento();

        // Tipo mudou → recria o(s) lançamento(s), sem perguntar escopo
        if (tipoSelecionado !== tipoLancamentoEdicao) {
            await executarSalvar('unico');
            return;
        }

        // Tipo não mudou e é único → salva direto
        if (tipoLancamentoEdicao === 'unico') {
            await executarSalvar('unico');
            return;
        }

        // Tipo não mudou e é parcelado/recorrente → pergunta escopo
        const titulo    = document.getElementById('editar-grupo-titulo');
        const subtitulo = document.getElementById('editar-grupo-subtitulo');
        titulo.textContent    = 'Salvar Alteração';
        subtitulo.textContent = tipoLancamentoEdicao === 'parcelado'
            ? 'Este lançamento faz parte de um parcelamento. Como deseja aplicar a alteração?'
            : 'Este lançamento é recorrente. Como deseja aplicar a alteração?';

        document.getElementById('btn-salvar-unico').onclick   = async () => { fecharModalEdicao(); await executarSalvar('unico'); };
        document.getElementById('btn-salvar-futuros').onclick = async () => { fecharModalEdicao(); await executarSalvar('futuros'); };

        modalEditarGrupo.style.display = 'flex';
    };

    // ── Carregar histórico ───────────────────────────────────────────────────
    async function carregarHistorico() {
        const mes      = filtroMesInput.value;
        const busca    = buscaInput.value.toLowerCase();
        const ordem    = ordemSelect.value;
        const categoria = filtroCategoria.value;
        const metodo   = filtroMetodo.value;
        const assinatura = filtroAssinatura.value;
        const cartao   = filtroCartao.value;

        try {
            const res  = await fetch(`api/api_extrato.php?mes=${mes}`);
            const data = await res.json();
            let transacoes = Array.isArray(data) ? data : (data.transacoes || []);

            if (busca) {
                transacoes = transacoes.filter(t =>
                    t.descricao.toLowerCase().includes(busca)
                );
            }

            // Filtro por categoria
            if (categoria) {
                transacoes = transacoes.filter(t =>
                    t.categoria === categoria
                );
            }

            // Filtro por método de pagamento
            if (metodo) {
                transacoes = transacoes.filter(t =>
                    t.metodo === metodo
                );
            }

            // Filtro por assinatura
            if (assinatura === 'assinatura') {
                transacoes = transacoes.filter(t =>
                    t.eh_assinatura == 1
                );
            } else if (assinatura === 'nao-assinatura') {
                transacoes = transacoes.filter(t =>
                    t.eh_assinatura == 0
                );
            }

            // Filtro por cartão
            if (cartao === 'cartao') {
                transacoes = transacoes.filter(t =>
                    t.eh_cartao == 1
                );
            } else if (cartao === 'nao-cartao') {
                transacoes = transacoes.filter(t =>
                    t.eh_cartao == 0
                );
            }

            transacoes.sort((a, b) => {
                if (ordem === 'data-desc')  return new Date(b.data) - new Date(a.data);
                if (ordem === 'data-asc')   return new Date(a.data) - new Date(b.data);
                if (ordem === 'valor-desc') return b.valor - a.valor;
                if (ordem === 'valor-asc')  return a.valor - b.valor;
                return 0;
            });

            // ✅ NOVO: Calcular receitas SEM filtros (sempre do mês todo)
            const res_data  = await fetch(`api/api_extrato.php?mes=${mes}`);
            const data_receitas = await res_data.json();
            let todasTransacoes = Array.isArray(data_receitas) ? data_receitas : (data_receitas.transacoes || []);
            
            const somaReceitasTotal = todasTransacoes.filter(t => t.tipo === 'receita')
                                                    .reduce((sum, t) => sum + parseFloat(t.valor || 0), 0);

            // Calcular despesas COM filtros (apenas o que é exibido)
            const somaDespesas = transacoes.filter(t => t.tipo === 'despesa')
                                          .reduce((sum, t) => sum + parseFloat(t.valor || 0), 0);
            
            const saldo = somaReceitasTotal - somaDespesas;

            // Atualizar resumo de filtros - sempre aparecer quando há transações
            if (transacoes.length > 0) {
                resumoFiltros.style.display = 'grid';
                document.getElementById('soma-receitas').textContent = `R$ ${somaReceitasTotal.toFixed(2).replace('.', ',')}`;
                document.getElementById('soma-despesas').textContent = `R$ ${somaDespesas.toFixed(2).replace('.', ',')}`;
                document.getElementById('soma-saldo').textContent = `R$ ${saldo.toFixed(2).replace('.', ',')}`;
            } else {
                resumoFiltros.style.display = 'none';
            }

            listaDiv.innerHTML = '';

            if (transacoes.length === 0) {
                const vazio = document.createElement('p');
                vazio.style.cssText = 'text-align:center;color:var(--text-secondary);margin-top:40px;';
                vazio.textContent = 'Nenhuma movimentação encontrada para este filtro.';
                listaDiv.appendChild(vazio);
                return;
            }

            transacoes.forEach(t => {
                const item = document.createElement('div');
                item.className = `item-movimentacao ${t.tipo}`;

                // ── Info principal ──
                const info = document.createElement('div');
                info.className = 'info-principal';

                const topo = document.createElement('div');
                topo.className = 'topo-item';

                const h4 = document.createElement('h4');
                h4.textContent = t.descricao;

                // Badge de tipo de lançamento
                if (t.tipo_lancamento === 'recorrente') {
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-recorrente';
                    badge.textContent = '🔁 Recorrente';
                    topo.appendChild(h4);
                    topo.appendChild(badge);
                } else if (t.tipo_lancamento === 'parcelado') {
                    const badge = document.createElement('span');
                    badge.className = 'badge badge-parcelado';
                    badge.textContent = `📅 ${t.parcela_atual}/${t.total_parcelas}`;
                    topo.appendChild(h4);
                    topo.appendChild(badge);
                } else {
                    topo.appendChild(h4);
                }

                const span = document.createElement('span');
                span.textContent = `${t.data.split('-').reverse().join('/')} | ${t.categoria} | ${t.metodo}`;

                info.appendChild(topo);
                info.appendChild(span);

                // ── Ações ──
                const acoes = document.createElement('div');
                acoes.className = 'acoes-item';

                const valorSpan = document.createElement('span');
                valorSpan.className = `valor-mov ${t.tipo}`;
                valorSpan.textContent = `${t.tipo === 'despesa' ? '-' : '+'} R$ ${parseFloat(t.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;

                const btnEdit = document.createElement('button');
                btnEdit.className = 'btn-edit';
                btnEdit.textContent = '✏️';
                btnEdit.title = 'Editar';
                btnEdit.addEventListener('click', () => prepararEdicao(t));

                const btnDel = document.createElement('button');
                btnDel.className = 'btn-delete';
                btnDel.textContent = '🗑️';
                btnDel.title = 'Excluir';
                btnDel.addEventListener('click', () => confirmarExclusao(t));

                acoes.appendChild(valorSpan);
                acoes.appendChild(btnEdit);
                acoes.appendChild(btnDel);

                item.appendChild(info);
                item.appendChild(acoes);
                listaDiv.appendChild(item);
            });
        } catch (error) {
            console.error('Erro ao carregar histórico:', error);
        }
    }

    // ── Editar lançamento ────────────────────────────────────────────────────
    const prepararEdicao = async (t) => {
        idEdicao             = t.id;
        tipoLancamentoEdicao = t.tipo_lancamento || 'unico';
        await popularSelects();
        descInput.value  = t.descricao;
        valorInput.value = t.valor;
        dataInput.value  = t.data;
        tipoInput.value  = t.tipo;
        catSelect.value  = t.categoria;
        metSelect.value  = t.metodo;
        ehAssinaturaInput.checked = (t.eh_assinatura == 1);  // ✅ NOVO
        ehCartaoInput.checked = (t.eh_cartao == 1);          // ✅ NOVO
        document.getElementById('modal-titulo').innerText = 'Editar Lançamento';

        // Exibir o bloco de tipo de lançamento como somente leitura
        const blocoTipo = document.getElementById('radio-unico').closest('.col-full');
        blocoTipo.style.display = '';

        // Marca o radio correto (sem desabilitar — apenas informativo visual)
        document.querySelectorAll('input[name="tipo_lancamento"]').forEach(radio => {
            radio.checked  = (radio.value === (t.tipo_lancamento || 'unico'));
            radio.disabled = false;
        });
        atualizarUiTipoLancamento();

        // Ajusta os campos extras conforme o tipo
        if (t.tipo_lancamento === 'parcelado') {
            campoParcelas.style.display = 'block';
            totalParcelasIn.value       = t.total_parcelas;
            totalParcelasIn.disabled    = false;
            hintParcelas.textContent    = `Parcela ${t.parcela_atual} de ${t.total_parcelas}`;
            avisoRecorrente.style.display = 'none';
        } else if (t.tipo_lancamento === 'recorrente') {
            campoParcelas.style.display   = 'none';
            avisoRecorrente.style.display = 'block';
        } else {
            campoParcelas.style.display   = 'none';
            avisoRecorrente.style.display = 'none';
        }

        modalCadastro.style.display = 'flex';
    };

    // ── Excluir lançamento (com escopo para grupos) ──────────────────────────
    const confirmarExclusao = (t) => {
        if (t.tipo_lancamento === 'unico' || !t.grupo_id) {
            // Lançamento simples: exclui direto
            if (confirm('Excluir esta transação?')) executarExclusao(t.id, 'unico');
            return;
        }

        // Parcelado ou recorrente: mostra modal de opções
        const titulo    = document.getElementById('excluir-titulo');
        const subtitulo = document.getElementById('excluir-subtitulo');

        if (t.tipo_lancamento === 'parcelado') {
            titulo.textContent    = 'Excluir Parcela';
            subtitulo.textContent = `Parcela ${t.parcela_atual} de ${t.total_parcelas} — "${t.descricao}". Como deseja excluir?`;
        } else {
            titulo.textContent    = 'Excluir Recorrência';
            subtitulo.textContent = `Lançamento recorrente "${t.descricao}". Como deseja excluir?`;
        }

        document.getElementById('btn-excluir-unico').onclick   = () => { fecharModal(); executarExclusao(t.id, 'unico'); };
        document.getElementById('btn-excluir-futuros').onclick = () => { fecharModal(); executarExclusao(t.id, 'futuros'); };
        document.getElementById('btn-excluir-grupo').onclick   = () => { fecharModal(); executarExclusao(t.id, 'grupo'); };

        modalGrupo.style.display = 'flex';
    };

    const executarExclusao = async (id, escopo) => {
        const res    = await fetch('api/api_extrato.php', {
            method:  'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id, escopo }),
        });
        const result = await res.json();
        if (result.success || result.sucesso) carregarHistorico();
        else alert('Erro ao excluir: ' + (result.error || result.erro));
    };

    // ── Limpar mês ───────────────────────────────────────────────────────────
    window.executarLimpeza = async (tipo) => {
        const mes = filtroMesInput.value;
        if (confirm(`Deseja limpar os registros (${tipo}) deste mês?`)) {
            await fetch('api/api_extrato.php', {
                method:  'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ tipo, mes }),
            });
            fecharModal();
            carregarHistorico();
        }
    };

    // ── Eventos ──────────────────────────────────────────────────────────────
    filtroMesInput.onchange = carregarHistorico;
    buscaInput.oninput      = carregarHistorico;
    ordemSelect.onchange    = carregarHistorico;
    filtroCategoria.onchange = carregarHistorico;
    filtroMetodo.onchange = carregarHistorico;
    filtroAssinatura.onchange = carregarHistorico;
    filtroCartao.onchange = carregarHistorico;

    // Inicializar: popular selects e depois carregar histórico
    (async () => {
        await popularSelects(); // Aguarda população das categorias
        carregarHistorico();    // Depois carrega o histórico
    })();
});

// ── Tema ─────────────────────────────────────────────────────────────────────
// Lógica de Tema
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
};

// Aplicar tema salvo ou padrão ao carregar
const temaSalvo = localStorage.getItem('theme') || 'dark';
aplicarTema(temaSalvo);

// Listener do botão
const btnTheme = document.getElementById('theme-toggle');
if (btnTheme) {
    btnTheme.onclick = () => {
        const temaAtual = document.documentElement.getAttribute('data-theme');
        const novoTema = temaAtual === 'light' ? 'dark' : 'light';
        aplicarTema(novoTema);
    };
}
