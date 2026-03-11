// ========== ANIMAÇÕES GLOBAIS E TRANSIÇÕES ==========
// Arquivo leve com funções reutilizáveis para transições

/**
 * Mostra notificação com transição
 * @param {string} message - Mensagem a exibir
 * @param {string} type - Tipo: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duração em ms (padrão: 4000)
 */
function showNotification(message, type = 'info', duration = 4000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        z-index: 9999;
        animation: slideInUp 0.4s ease;
        font-weight: 500;
        max-width: 400px;
        word-wrap: break-word;
    `;

    // Cores por tipo
    const colors = {
        success: { bg: '#2ecc71', border: '#27ae60' },
        error: { bg: '#e74c3c', border: '#c0392b' },
        info: { bg: '#3498db', border: '#2980b9' },
        warning: { bg: '#f39c12', border: '#d68910' }
    };

    const color = colors[type] || colors.info;
    notification.style.backgroundColor = color.bg;
    notification.style.borderLeft = `4px solid ${color.border}`;

    document.body.appendChild(notification);

    // Remove após duração
    setTimeout(() => {
        notification.style.animation = 'slideOutDown 0.4s ease forwards';
        setTimeout(() => notification.remove(), 400);
    }, duration);
}

/**
 * Anima um número de um valor a outro
 * @param {HTMLElement} element - Elemento a animar
 * @param {number} start - Valor inicial
 * @param {number} end - Valor final
 * @param {number} duration - Duração em ms
 */
function animateValue(element, start, end, duration = 1000) {
    if (!element) return;
    
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString('pt-BR');
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    
    window.requestAnimationFrame(step);
}

/**
 * Anima um valor monetário
 * @param {HTMLElement} element - Elemento a animar
 * @param {number} start - Valor inicial
 * @param {number} end - Valor final
 * @param {number} duration - Duração em ms
 */
function animateCurrencyValue(element, start, end, duration = 1000) {
    if (!element) return;
    
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = progress * (end - start) + start;
        element.textContent = 'R$ ' + value.toLocaleString('pt-BR', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    
    window.requestAnimationFrame(step);
}

/**
 * Estado de carregamento em um botão/elemento
 * @param {HTMLElement} element - Elemento a colocar em loading
 * @returns {function} Função para restaurar
 */
function showLoadingState(element) {
    if (!element) return () => {};
    
    const originalContent = element.innerHTML;
    const originalDisabled = element.disabled;
    
    element.disabled = true;
    element.innerHTML = '<i class="fas fa-spinner" style="animation: spin 1s linear infinite;"></i> Carregando...';
    
    return () => {
        element.disabled = originalDisabled;
        element.innerHTML = originalContent;
    };
}

/**
 * Fade in/out de conteúdo
 * @param {HTMLElement} element - Elemento a fazer fade
 * @param {string} newContent - Novo HTML
 * @param {number} duration - Duração em ms
 */
function fadeContent(element, newContent, duration = 300) {
    if (!element) return;
    
    element.style.transition = `opacity ${duration}ms ease`;
    element.style.opacity = '0';
    
    setTimeout(() => {
        element.innerHTML = newContent;
        element.style.opacity = '1';
    }, duration);
}

/**
 * Expandir/Colapsar elemento
 * @param {HTMLElement} button - Botão que controla
 * @param {string} targetSelector - Seletor do elemento
 */
function toggleExpandable(button, targetSelector) {
    const target = document.querySelector(targetSelector);
    if (!target) return;
    
    if (target.classList.contains('open')) {
        target.classList.remove('open');
        target.style.maxHeight = '0';
    } else {
        target.classList.add('open');
        target.style.maxHeight = '500px';
    }
    
    if (button) button.classList.toggle('open');
}

// ========== ANIMAÇÕES CSS GLOBAIS ==========

// Adiciona estilos CSS para animações se não existirem
if (!document.querySelector('style[data-animations]')) {
    const styleElement = document.createElement('style');
    styleElement.setAttribute('data-animations', 'true');
    styleElement.textContent = `
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideOutDown {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(30px); }
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .notification {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        [data-tooltip] {
            cursor: help;
            border-bottom: 1px dotted;
        }
    `;
    document.head.appendChild(styleElement);
}

// Torna as funções globais disponíveis
window.showNotification = showNotification;
window.animateValue = animateValue;
window.animateCurrencyValue = animateCurrencyValue;
window.showLoadingState = showLoadingState;
window.fadeContent = fadeContent;
window.toggleExpandable = toggleExpandable;
