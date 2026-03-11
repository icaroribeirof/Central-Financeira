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
        const ctx = document.getElementById('chartCategorias').getContext('2d');
        if (window.chartPizza) window.chartPizza.destroy();

        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-main').trim();

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
        const ctx = document.getElementById('chartEvolucaoNaoCartao').getContext('2d');
        if (window.chartBarNaoCartao) window.chartBarNaoCartao.destroy();

        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-main').trim();

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
        const ctx = document.getElementById('chartEvolucaoCartao').getContext('2d');
        if (window.chartBarCartao) window.chartBarCartao.destroy();

        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-main').trim();

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

// Lógica de Tema (Mantida e Melhorada)
const aplicarTema = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = theme === 'light' ? '🌙 Modo Escuro' : '☀️ Modo Claro';
    
    // Recarregar gráficos quando tema muda para atualizar cores
    if (window.chartPizza || window.chartBarNaoCartao || window.chartBarCartao) {
        atualizarDashboard();
    }
};
aplicarTema(localStorage.getItem('theme') || 'dark');
document.getElementById('theme-toggle').onclick = () => {
    const novo = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    aplicarTema(novo);
};