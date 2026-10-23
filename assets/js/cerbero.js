/**
 * ========================================================================
 * SISTEMA CERBERO - Framework Front-end de Toasts e Modais Customizados
 * ========================================================================
 * Substitui completamente alertas e confirmações nativas do navegador
 * por componentes acessíveis, responsivos e estilizados em Tailwind CSS.
 */

window.Cerbero = {
    /**
     * Exibe um Toast flutuante moderno na interface
     * @param {string} tipo 'sucesso' | 'erro' | 'aviso' | 'info'
     * @param {string} titulo Título curto em destaque
     * @param {string} mensagem Descrição da notificação
     * @param {number} duracao Tempo em ms antes do auto-dismiss (padrão: 4500ms)
     */
    toast: function(tipo, titulo, mensagem, duracao = 4500) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Configuração de cores e ícones conforme o tipo
        const configs = {
            sucesso: {
                bg: 'bg-emerald-50 dark:bg-emerald-950/80',
                border: 'border-emerald-300 dark:border-emerald-700',
                iconBg: 'bg-emerald-500 text-white',
                icon: 'fa-check',
                titleColor: 'text-emerald-900 dark:text-emerald-200',
                textColor: 'text-emerald-700 dark:text-emerald-300'
            },
            erro: {
                bg: 'bg-rose-50 dark:bg-rose-950/80',
                border: 'border-rose-300 dark:border-rose-700',
                iconBg: 'bg-rose-500 text-white',
                icon: 'fa-circle-xmark',
                titleColor: 'text-rose-900 dark:text-rose-200',
                textColor: 'text-rose-700 dark:text-rose-300'
            },
            aviso: {
                bg: 'bg-amber-50 dark:bg-amber-950/80',
                border: 'border-amber-300 dark:border-amber-700',
                iconBg: 'bg-amber-500 text-white',
                icon: 'fa-triangle-exclamation',
                titleColor: 'text-amber-900 dark:text-amber-200',
                textColor: 'text-amber-700 dark:text-amber-300'
            },
            info: {
                bg: 'bg-indigo-50 dark:bg-indigo-950/80',
                border: 'border-indigo-300 dark:border-indigo-700',
                iconBg: 'bg-indigo-500 text-white',
                icon: 'fa-circle-info',
                titleColor: 'text-indigo-900 dark:text-indigo-200',
                textColor: 'text-indigo-700 dark:text-indigo-300'
            }
        };

        const cfg = configs[tipo] || configs.info;
        const toastEl = document.createElement('div');
        toastEl.className = `toast-enter pointer-events-auto flex items-start w-full max-w-sm p-4 rounded-xl shadow-xl border ${cfg.bg} ${cfg.border} backdrop-blur-md transition-all duration-300`;

        toastEl.innerHTML = `
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg ${cfg.iconBg} shadow-sm">
                <i class="fa-solid ${cfg.icon} text-sm"></i>
            </div>
            <div class="ml-3 flex-1 text-sm">
                <h4 class="font-bold ${cfg.titleColor}">${titulo}</h4>
                <p class="mt-0.5 ${cfg.textColor}">${mensagem}</p>
            </div>
            <button type="button" class="ml-2 inline-flex items-center justify-center w-6 h-6 text-slate-400 hover:text-slate-600 rounded-md hover:bg-black/5 focus:outline-none" aria-label="Fechar">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        `;

        const closeBtn = toastEl.querySelector('button');
        const dismiss = () => {
            toastEl.classList.remove('toast-enter');
            toastEl.classList.add('toast-leave');
            setTimeout(() => {
                if (toastEl.parentNode) {
                    toastEl.parentNode.removeChild(toastEl);
                }
            }, 250);
        };

        closeBtn.addEventListener('click', dismiss);
        container.appendChild(toastEl);

        if (duracao > 0) {
            setTimeout(dismiss, duracao);
        }
    },

    /**
     * Abre um modal por ID com animação suave
     * @param {string} modalId
     */
    openModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    },

    /**
     * Fecha um modal por ID
     * @param {string} modalId
     */
    closeModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        
        // Verifica se não há outros modais abertos antes de restaurar o scroll do body
        const anyOpen = document.querySelectorAll('.cerbero-modal:not(.hidden)');
        if (anyOpen.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    },

    /**
     * Exibe um modal de confirmação personalizado que substitui window.confirm()
     * @param {Object} options 
     * {
     *   title: string,
     *   message: string,
     *   confirmText: string,
     *   confirmColor: 'red' | 'indigo' | 'emerald',
     *   formAction: string,
     *   hiddenInputs: Object,
     *   onConfirm: Function
     * }
     */
    confirmModal: function(options) {
        const modal = document.getElementById('global-confirm-modal');
        if (!modal) return;

        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl = document.getElementById('confirm-modal-message');
        const formEl = document.getElementById('confirm-modal-form');
        const btnSubmit = document.getElementById('confirm-modal-submit-btn');
        const hiddenContainer = document.getElementById('confirm-modal-hidden-fields');

        if (titleEl) titleEl.innerText = options.title || 'Confirmar Ação';
        if (msgEl) msgEl.innerText = options.message || 'Tem certeza que deseja prosseguir com esta ação?';
        if (btnSubmit) {
            btnSubmit.innerText = options.confirmText || 'Confirmar';
            btnSubmit.className = 'px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors focus:ring-2 focus:ring-offset-2 ' + 
                (options.confirmColor === 'red' ? 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500' : 
                 options.confirmColor === 'emerald' ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' : 
                 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500');
        }

        if (hiddenContainer) {
            hiddenContainer.innerHTML = '';
            if (options.hiddenInputs) {
                for (const [key, val] of Object.entries(options.hiddenInputs)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = val;
                    hiddenContainer.appendChild(input);
                }
            }
        }

        if (formEl) {
            if (options.formAction) {
                formEl.action = options.formAction;
                formEl.method = options.formMethod || 'POST';
                formEl.onsubmit = null;
            } else if (typeof options.onConfirm === 'function') {
                formEl.action = '';
                formEl.onsubmit = function(e) {
                    e.preventDefault();
                    options.onConfirm();
                    Cerbero.closeModal('global-confirm-modal');
                };
            }
        }

        Cerbero.openModal('global-confirm-modal');
    },

    /**
     * Alterna a visibilidade do menu lateral no mobile
     */
    toggleSidebar: function() {
        const sidebar = document.getElementById('sidebar-menu');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;

        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
        }
    }
};

// Eventos Globais: fechar modais com tecla ESC ou clique fora do conteúdo
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.cerbero-modal:not(.hidden)');
        openModals.forEach(m => Cerbero.closeModal(m.id));
    }
});
