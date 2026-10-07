/**
 * Componente Reutilizável de Modal de Confirmação de Exclusão (ConfirmDeleteModal)
 * Central Financeira
 */

const ConfirmDeleteModal = {
    /**
     * Exibe o modal de confirmação de exclusão moderno.
     * Retorna uma Promise<boolean> que resolve para true se confirmado e false se cancelado.
     * 
     * @param {Object} options
     * @param {string} [options.title='Confirmar Exclusão'] - Título do modal
     * @param {string} [options.message='Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.'] - Mensagem contextualizada
     * @param {string} [options.confirmText='Excluir'] - Texto do botão de confirmação
     * @param {string} [options.cancelText='Cancelar'] - Texto do botão de cancelamento
     * @param {string} [options.iconClass='ph ph-trash'] - Ícone Phosphor
     * @param {Function} [options.onConfirm] - Callback executado ao confirmar
     * @param {Function} [options.onCancel] - Callback executado ao cancelar
     * @returns {Promise<boolean>}
     */
    open({
        title = 'Confirmar Exclusão',
        message = 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.',
        confirmText = 'Excluir',
        cancelText = 'Cancelar',
        iconClass = 'ph ph-trash',
        onConfirm = null,
        onCancel = null
    } = {}) {
        return new Promise((resolve) => {
            // Obter ou criar o container do modal
            let overlay = document.getElementById('confirm-delete-modal-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'confirm-delete-modal-overlay';
                overlay.className = 'confirm-modal-overlay';
                document.body.appendChild(overlay);
            }

            // Injetar HTML do modal
            overlay.innerHTML = `
                <div class="confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
                    <button type="button" class="confirm-modal-close" aria-label="Fechar">&times;</button>
                    <div class="confirm-modal-icon-container">
                        <div class="confirm-modal-icon-bg">
                            <i class="${iconClass}"></i>
                        </div>
                    </div>
                    <div class="confirm-modal-body">
                        <h3 id="confirm-modal-title" class="confirm-modal-title">${this.escapeHtml(title)}</h3>
                        <p class="confirm-modal-message">${this.escapeHtml(message)}</p>
                    </div>
                    <div class="confirm-modal-footer">
                        <button type="button" class="btn btn-secondary confirm-btn-cancel">${this.escapeHtml(cancelText)}</button>
                        <button type="button" class="btn btn-danger-solid confirm-btn-delete">
                            <i class="${iconClass}"></i> ${this.escapeHtml(confirmText)}
                        </button>
                    </div>
                </div>
            `;

            const card = overlay.querySelector('.confirm-modal-card');
            
            // Função para fechar o modal com animação
            let closed = false;
            const closeModal = (result) => {
                if (closed) return;
                closed = true;

                card.style.animation = 'modalPopOut 0.2s ease-in forwards';
                overlay.style.opacity = '0';
                
                setTimeout(() => {
                    overlay.classList.remove('active');
                    overlay.style.display = 'none';
                    overlay.style.opacity = '';
                    
                    if (result) {
                        if (typeof onConfirm === 'function') onConfirm();
                        resolve(true);
                    } else {
                        if (typeof onCancel === 'function') onCancel();
                        resolve(false);
                    }
                }, 200);
            };

            // Exibir modal com fade in
            overlay.style.display = 'flex';
            // Trigger reflow para aplicar transição
            void overlay.offsetWidth;
            overlay.classList.add('active');

            // Event Listeners
            const btnCancel = overlay.querySelector('.confirm-btn-cancel');
            const btnDelete = overlay.querySelector('.confirm-btn-delete');
            const btnClose  = overlay.querySelector('.confirm-modal-close');

            btnCancel.onclick = () => closeModal(false);
            btnDelete.onclick = () => closeModal(true);
            btnClose.onclick  = () => closeModal(false);

            // Fechar ao clicar no backdrop
            overlay.onclick = (e) => {
                if (e.target === overlay) closeModal(false);
            };

            // Fechar ao pressionar a tecla ESC
            const handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleKeyDown);
                    closeModal(false);
                }
            };
            document.addEventListener('keydown', handleKeyDown);

            // Foco inicial no botão de confirmação para acessibilidade
            setTimeout(() => {
                btnDelete.focus();
            }, 50);
        });
    },

    /**
     * Sanitização de strings para inserção segura no HTML
     */
    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
};

window.ConfirmDeleteModal = ConfirmDeleteModal;
