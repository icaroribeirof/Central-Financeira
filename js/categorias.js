document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-categoria');
    const nomeInput = document.getElementById('nome-cat');
    const buscaInput = document.getElementById('input-busca');
    const ordemSelect = document.getElementById('ordem-select');
    const listaDiv = document.getElementById('lista-categorias');
    const modal = document.getElementById('modal-categoria');
    const btnAbrir = document.getElementById('btn-abrir-cadastro');

    let idEdicao = null; // Agora usamos o ID do banco, não o index do array

    btnAbrir.onclick = () => {
        idEdicao = null;
        form.reset();
        document.getElementById('modal-titulo').innerText = "Nova Categoria";
        modal.style.display = 'flex';
    };

    window.fecharModal = () => modal.style.display = 'none';

    // --- SALVAR OU EDITAR ---
    form.onsubmit = async (e) => {
        e.preventDefault();
        
        const dados = {
            id: idEdicao,
            nome: nomeInput.value
        };

        try {
            const response = await fetch('api/api_categorias.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const resultado = await response.json();
            if (resultado.success) {
                fecharModal();
                renderizar();
            } else {
                alert('Erro ao salvar: ' + resultado.error);
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
        }
    };

    // --- RENDERIZAR (BUSCAR DO BANCO) ---
    async function renderizar() {
        try {
            const response = await fetch('api/api_categorias.php');
            let categorias = await response.json();

            const termo = buscaInput.value.toLowerCase();
            const ordem = ordemSelect.value;

            // Filtro de busca
            let filtradas = categorias.filter(c => c.nome.toLowerCase().includes(termo));

            // Ordenação
            filtradas.sort((a, b) => {
                return ordem === 'alfa' 
                    ? a.nome.localeCompare(b.nome) 
                    : b.nome.localeCompare(a.nome);
            });

            /*if (filtradas.length === 0) {
                listaDiv.innerHTML = '<p style="text-align:center; color: #888; margin-top: 20px;">Nenhuma categoria encontrada.</p>';
                return;
            }*/

            listaDiv.innerHTML = filtradas.map((c) => `
                <div class="item-categoria">
                    <div class="info-cat">
                        <h4>${c.nome}</h4>
                    </div>
                    <div class="acoes">
                        <button class="btn-edit" onclick="prepararEdicao(${c.id}, '${c.nome}')">✏️</button>
                        <button class="btn-delete" onclick="removerCategoria(${c.id})">🗑️</button>
                    </div>
                </div>`).join('');

        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
            listaDiv.innerHTML = '<p style="color: red; text-align: center;">Erro ao conectar com o banco de dados.</p>';
        }
    }

    // --- PREPARAR EDIÇÃO ---
    window.prepararEdicao = (id, nome) => {
        idEdicao = id;
        nomeInput.value = nome;
        document.getElementById('modal-titulo').innerText = "Editar Categoria";
        modal.style.display = 'flex';
    };

    // --- REMOVER ---
    window.removerCategoria = async (id) => {
        if (confirm("Excluir esta categoria?")) {
            try {
                const response = await fetch('api/api_categorias.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                const resultado = await response.json();
                if (resultado.success) renderizar();
                else alert('Erro ao excluir: ' + resultado.error);
            } catch (error) {
                console.error('Erro ao excluir:', error);
            }
        }
    };

    buscaInput.oninput = renderizar;
    ordemSelect.onchange = renderizar;
    renderizar();
});

// Lógica de Tema (Mantida)
const btnTheme = document.getElementById('theme-toggle');
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    if (btnTheme) btnTheme.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
};
aplicarTema(localStorage.getItem('theme') || 'dark');
if (btnTheme) btnTheme.onclick = () => {
    const novo = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    aplicarTema(novo);
};
