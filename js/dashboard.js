document.addEventListener('DOMContentLoaded', () => {
    const dashMes = document.getElementById('dash-mes');
    const hoje = new Date();
    dashMes.value = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, '0')}`;

    async function atualizarDashboard() {
        const mesSelecionado = dashMes.value;

        try {
            // Buscamos os dados consolidados da nossa nova API
            const response = await fetch(`api/api_dashboard.php?mes=${mesSelecionado}`);
            const dados = await response.json();

            // 1. Atualizar Cards de Resumo
            document.getElementById('total-receitas').innerText = `R$ ${dados.resumo.receitas.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            document.getElementById('total-despesas').innerText = `R$ ${dados.resumo.despesas_nao_cartao.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            document.getElementById('total-cartao').innerText = `R$ ${dados.resumo.despesas_cartao.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            document.getElementById('total-saldo').innerText = `R$ ${dados.resumo.saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;

            // 2. Renderizar Gráfico de Pizza (Categorias)
            renderizarPizza(dados.categorias);

            // 3. Renderizar Gráfico de Evolução Não Cartão (Últimos 6 meses)
            renderizarEvolucaoNaoCartao(dados.evolucao_nao_cartao);

            // 4. Renderizar Gráfico de Evolução Cartão (Últimos 6 meses)
            renderizarEvolucaoCartao(dados.evolucao_cartao);

        } catch (error) {
            console.error("Erro ao carregar dados do dashboard:", error);
        }
    }

    function renderizarPizza(dadosCat) {
        // Destruir gráfico anterior se existir
        if (window.chartPizza) window.chartPizza.destroy();

        // Pegar cor computada do body (lê a variável CSS atual)
        const bodyStyle = window.getComputedStyle(document.body);
        const textColor = bodyStyle.color;

        // Recriar o canvas
        const container = document.getElementById('chartCategorias').parentElement;
        const oldCanvas = document.getElementById('chartCategorias');
        const newCanvas = document.createElement('canvas');
        newCanvas.id = 'chartCategorias';
        container.replaceChild(newCanvas, oldCanvas);

        // Renderizar novo gráfico com cor correta
        const ctx = newCanvas.getContext('2d');
        window.chartPizza = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: dadosCat.labels,
                datasets: [{
                    data: dadosCat.valores,
                    backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#e67e22'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 12 } } } }
            }
        });
    }

    function renderizarEvolucaoNaoCartao(dadosEvo) {
        // Destruir gráfico anterior se existir
        if (window.chartBarNaoCartao) window.chartBarNaoCartao.destroy();

        // Pegar cor computada do body (lê a variável CSS atual)
        const bodyStyle = window.getComputedStyle(document.body);
        const textColor = bodyStyle.color;

        // Recriar o canvas
        const container = document.getElementById('chartEvolucaoNaoCartao').parentElement;
        const oldCanvas = document.getElementById('chartEvolucaoNaoCartao');
        const newCanvas = document.createElement('canvas');
        newCanvas.id = 'chartEvolucaoNaoCartao';
        container.replaceChild(newCanvas, oldCanvas);

        // Renderizar novo gráfico com cor correta
        const ctx = newCanvas.getContext('2d');
        window.chartBarNaoCartao = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dadosEvo.labels,
                datasets: [{
                    label: 'Despesas Não Cartão (R$)',
                    data: dadosEvo.valores,
                    backgroundColor: '#e74c3c',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: textColor }
                    }
                }
            }
        });
    }

    function renderizarEvolucaoCartao(dadosEvo) {
        // Destruir gráfico anterior se existir
        if (window.chartBarCartao) window.chartBarCartao.destroy();

        // Pegar cor computada do body (lê a variável CSS atual)
        const bodyStyle = window.getComputedStyle(document.body);
        const textColor = bodyStyle.color;

        // Recriar o canvas
        const container = document.getElementById('chartEvolucaoCartao').parentElement;
        const oldCanvas = document.getElementById('chartEvolucaoCartao');
        const newCanvas = document.createElement('canvas');
        newCanvas.id = 'chartEvolucaoCartao';
        container.replaceChild(newCanvas, oldCanvas);

        // Renderizar novo gráfico com cor correta
        const ctx = newCanvas.getContext('2d');
        window.chartBarCartao = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dadosEvo.labels,
                datasets: [{
                    label: 'Despesas Cartão (R$)',
                    data: dadosEvo.valores,
                    backgroundColor: '#9b59b6',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { display: false },
                        ticks: { color: textColor }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: textColor }
                    }
                }
            }
        });
    }

    dashMes.onchange = atualizarDashboard;
    atualizarDashboard();
});

// Lógica de Tema
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
    
    // Recarregar gráficos quando tema muda (sem recarregar página)
    if (window.chartPizza || window.chartBarNaoCartao || window.chartBarCartao) {
        const mesSelecionado = document.getElementById('filtro-mes').value;
        
        fetch(`api/api_dashboard.php?mes=${mesSelecionado}`)
            .then(response => response.json())
            .then(dados => {
                // Renderizar gráficos com cores atualizadas
                renderizarPizza(dados.categorias);
                renderizarEvolucaoNaoCartao(dados.evolucao_nao_cartao);
                renderizarEvolucaoCartao(dados.evolucao_cartao);
            })
            .catch(error => console.error("Erro ao recarregar gráficos:", error));
    }
};

// Aplicar tema salvo ou padrão ao carregar
const temaSalvo = localStorage.getItem('theme') || 'dark';
aplicarTema(temaSalvo);

// Listener do botão
document.getElementById('theme-toggle').onclick = () => {
    const temaAtual = document.documentElement.getAttribute('data-theme');
    const novoTema = temaAtual === 'light' ? 'dark' : 'light';
    aplicarTema(novoTema);
};