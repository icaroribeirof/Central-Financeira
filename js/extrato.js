document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-transacao');
    const descInput = document.getElementById('desc');
    const valorInput = document.getElementById('valor');
    const dataInput = document.getElementById('data-lancamento');
    const tipoInput = document.getElementById('tipo-transacao');
    const catSelect = document.getElementById('categoria-select');
    const metSelect = document.getElementById('metodo-pagamento');
    
    const filtroMesInput = document.getElementById('filtro-mes');
    const buscaInput = document.getElementById('input-busca');
    const ordemSelect = document.getElementById('ordem-select');
    const listaDiv = document.getElementById('lista-transacoes');
    
    const modalCadastro = document.getElementById('modal-cadastro');
    const modalLimpeza = document.getElementById('modal-limpeza');
    const btnAbrirCadastro = document.getElementById('btn-abrir-cadastro');
    const btnAbrirLimpeza = document.getElementById('btn-abrir-limpeza');

    // Setar mês atual no filtro
    const hoje = new Date();
    filtroMesInput.value = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, '0')}`;

    let idEdicao = null;

    // --- POPULAR SELECTS (Categorias e Cartões do Usuário) ---
    async function popularSelects() {
        try {
            // 1. Busca categorias
            const resCat = await fetch('api/api_categorias.php');
            const categorias = await resCat.json();
            
            catSelect.innerHTML = '<option value="">Selecione uma categoria</option>';
            if (Array.isArray(categorias)) {
                categorias.forEach(c => {
                    catSelect.innerHTML += `<option value="${c.nome}">${c.nome}</option>`;
                });
            }

            // 2. Busca cartões (Ajustado para o novo formato com ?acao=buscar)
            let cartoes = [];
            try {
                const resCard = await fetch('api/api_cartao.php?acao=buscar');
                const dataCard = await resCard.json();
                
                // O PHP agora retorna um objeto { "cartoes": [...] }
                cartoes = dataCard.cartoes || [];
            } catch (errCard) {
                console.error("Erro ao buscar cartões, usando apenas padrões.", errCard);
            }

            // Define as opções padrão e anexa os cartões logo abaixo
            metSelect.innerHTML = `
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
                <option value="Débito">Débito</option>
            `;
            
            cartoes.forEach(c => {
                metSelect.innerHTML += `<option value="${c.nome}">${c.nome}</option>`;
            });

        } catch (e) { 
            console.error("Erro geral ao popular selects", e); 
        }
    }

    btnAbrirCadastro.onclick = () => {
        idEdicao = null;
        form.reset();
        document.getElementById('modal-titulo').innerText = "Novo Lançamento";
        modalCadastro.style.display = 'flex';
        popularSelects();
    };

    btnAbrirLimpeza.onclick = () => modalLimpeza.style.display = 'flex';
    
    window.fecharModal = () => {
        modalCadastro.style.display = 'none';
        modalLimpeza.style.display = 'none';
    };

    // --- SALVAR LANÇAMENTO ---
    form.onsubmit = async (e) => {
        e.preventDefault();
        const dados = {
            id: idEdicao,
            descricao: descInput.value,
            valor: parseFloat(valorInput.value),
            data: dataInput.value,
            tipo: tipoInput.value,
            categoria: catSelect.value,
            metodo: metSelect.value
        };

        const res = await fetch('api/api_extrato.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const result = await res.json();
        if (result.success || result.sucesso) {
            fecharModal();
            carregarHistorico();
        } else {
            alert("Erro ao salvar: " + (result.error || result.erro));
        }
    };

    // --- CARREGAR HISTÓRICO ---
    async function carregarHistorico() {
        const mes = filtroMesInput.value;
        const busca = buscaInput.value.toLowerCase();
        const ordem = ordemSelect.value;

        try {
            const res = await fetch(`api/api_extrato.php?mes=${mes}`);
            const data = await res.json();
            
            let transacoes = Array.isArray(data) ? data : (data.transacoes || []);

            if (busca) {
                transacoes = transacoes.filter(t => t.descricao.toLowerCase().includes(busca));
            }

            transacoes.sort((a, b) => {
                if (ordem === 'data-desc') return new Date(b.data) - new Date(a.data);
                if (ordem === 'data-asc') return new Date(a.data) - new Date(b.data);
                if (ordem === 'valor-desc') return b.valor - a.valor;
                if (ordem === 'valor-asc') return a.valor - b.valor;
                return 0;
            });

            listaDiv.innerHTML = transacoes.map(t => `
                <div class="item-movimentacao ${t.tipo}">
                    <div class="info-principal">
                        <h4>${t.descricao}</h4>
                        <span>${t.data.split('-').reverse().join('/')} | ${t.categoria} | ${t.metodo}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="valor-mov ${t.tipo}">
                            ${t.tipo === 'despesa' ? '-' : '+'} R$ ${parseFloat(t.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </span>
                        <button class="btn-edit" onclick="prepararEdicao(${t.id}, '${t.descricao.replace(/'/g, "\\'")}', ${t.valor}, '${t.data}', '${t.tipo}', '${t.categoria}', '${t.metodo}')">✏️</button>
                        <button class="btn-delete" onclick="removerTransacao(${t.id})">🗑️</button>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error("Erro ao carregar histórico:", error);
        }
    }

    window.prepararEdicao = async (id, desc, valor, data, tipo, cat, met) => {
        idEdicao = id;
        await popularSelects();
        descInput.value = desc;
        valorInput.value = valor;
        dataInput.value = data;
        tipoInput.value = tipo;
        catSelect.value = cat;
        metSelect.value = met;
        document.getElementById('modal-titulo').innerText = "Editar Lançamento";
        modalCadastro.style.display = 'flex';
    };

    window.removerTransacao = async (id) => {
        if (confirm("Excluir esta transação?")) {
            const res = await fetch('api/api_extrato.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await res.json();
            if (result.success || result.sucesso) carregarHistorico();
        }
    };

    window.executarLimpeza = async (tipo) => {
        const mes = filtroMesInput.value;
        if (confirm(`Deseja limpar os registros (${tipo}) deste mês?`)) {
            await fetch('api/api_extrato.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo: tipo, mes: mes })
            });
            fecharModal();
            carregarHistorico();
        }
    };

    filtroMesInput.onchange = carregarHistorico;
    buscaInput.oninput = carregarHistorico;
    ordemSelect.onchange = carregarHistorico;
    
    carregarHistorico();
});

// Lógica de Tema
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
};
aplicarTema(localStorage.getItem('theme') || 'dark');

const btnTheme = document.getElementById('theme-toggle');
if (btnTheme) {
    btnTheme.onclick = () => {
        const novo = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        aplicarTema(novo);
    };
}