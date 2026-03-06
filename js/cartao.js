document.addEventListener('DOMContentLoaded', () => {
    const listaDiv = document.getElementById('lista-cartoes');
    const form = document.getElementById('form-cartao');
    const modal = document.getElementById('modal-cartao');
    const btnAbrir = document.getElementById('btn-abrir-modal');
    const filtroData = document.getElementById('filtro-data');
    const buscaInput = document.getElementById('busca-cartao');
    
    // Setar data atual no filtro (YYYY-MM)
    const hoje = new Date();
    filtroData.value = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, '0')}`;

    let idEdicao = null;

    if (btnAbrir) {
        btnAbrir.onclick = () => {
            idEdicao = null;
            form.reset();
            document.getElementById('modal-titulo').innerText = "Novo Cartão";
            modal.style.display = 'flex';
        };
    }

    window.fecharModal = () => modal.style.display = 'none';

    // --- SALVAR OU EDITAR ---
    form.onsubmit = async (e) => {
        e.preventDefault();
        const dados = {
            id: idEdicao,
            nome: document.getElementById('nome-cartao').value,
            limite: parseFloat(document.getElementById('limite-total').value)
        };

        try {
            const res = await fetch('api/api_cartao.php?acao=salvar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const result = await res.json();
            if (result.sucesso) {
                fecharModal();
                renderizar();
            } else {
                alert("Erro ao salvar: " + result.erro);
            }
        } catch (error) {
            console.error("Erro na requisição:", error);
        }
    };

    // --- RENDERIZAR CARTÕES ---
    async function renderizar() {
        const dataFiltro = filtroData.value; 
        const termoBusca = buscaInput ? buscaInput.value.toLowerCase() : "";

        try {
            const res = await fetch(`api/api_cartao.php?acao=buscar&mes=${dataFiltro}`);
            const data = await res.json();
            const cartoes = data.cartoes || [];

            const cartoesFiltrados = cartoes.filter(c => c.nome.toLowerCase().includes(termoBusca));

            /*if (cartoesFiltrados.length === 0) {
                listaDiv.innerHTML = `<p style="text-align:center; color: #888; margin-top: 20px;">Nenhum cartão encontrado.</p>`;
                return;
            }*/

            listaDiv.innerHTML = cartoesFiltrados.map((c) => {
                const gastos = parseFloat(c.total_gasto || 0);
                const limite = parseFloat(c.limite);
                const percentual = limite > 0 ? Math.min((gastos / limite) * 100, 100) : 0;
                const disponivel = limite - gastos;

                return `
                    <div class="card-item">
                        <div class="card-header">
                            <h3>${c.nome}</h3>
                            <span class="fatura-label">Fatura: ${dataFiltro.split('-').reverse().join('/')}</span>
                        </div>
                        <div class="limite-info">
                            <div class="limite-texto">
                                <span>Fatura Atual: <strong>R$ ${gastos.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></span>
                                <span>${percentual.toFixed(0)}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${percentual}%"></div>
                            </div>
                            <div class="limite-texto" style="margin-top: 10px; font-size: 0.75rem; color: var(--text-secondary);">
                                <span>Disponível: R$ ${disponivel.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                                <span>Limite: R$ ${limite.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn-edit" onclick="prepararEdicao(${c.id}, '${c.nome.replace(/'/g, "\\'")}', ${c.limite})">Editar</button>
                            <button class="btn-delete" onclick="removerCartao(${c.id})">Excluir</button>
                        </div>
                    </div>`;
            }).join('');
        } catch (error) {
            console.error("Erro ao renderizar:", error);
        }
    }

    window.prepararEdicao = (id, nome, limite) => {
        idEdicao = id;
        document.getElementById('nome-cartao').value = nome;
        document.getElementById('limite-total').value = limite;
        document.getElementById('modal-titulo').innerText = "Editar Cartão";
        modal.style.display = 'flex';
    };

    window.removerCartao = async (id) => {
        if (confirm("Deseja excluir este cartão?")) {
            const res = await fetch('api/api_cartao.php?acao=excluir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await res.json();
            if (result.sucesso) renderizar();
        }
    };

    filtroData.onchange = renderizar;
    if (buscaInput) buscaInput.oninput = renderizar;
    
    renderizar();
});

// Lógica de Tema
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
};
aplicarTema(localStorage.getItem('theme') || 'dark');
document.getElementById('theme-toggle').onclick = () => {
    const novo = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    aplicarTema(novo);
};
