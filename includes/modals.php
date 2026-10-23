<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Componentes de Modais Personalizados
 * ========================================================================
 * Substitui os diálogos padrão do navegador (alert() e confirm()) por
 * modais Tailwind estilizados, acessíveis e focados em Mobile First.
 */
?>

<!-- Modal Global de Confirmação (Substituto oficial do confirm()) -->
<div id="global-confirm-modal" class="cerbero-modal hidden fixed inset-0 z-50 overflow-y-auto items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="Cerbero.closeModal('global-confirm-modal')"></div>

    <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl modal-content-anim border border-slate-100 z-10">
        <div class="flex items-center space-x-3 mb-4">
            <div id="confirm-modal-icon-bg" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-base font-bold text-slate-900">Confirmar Ação</h3>
                <p class="text-xs text-slate-500">Esta operação requer validação</p>
            </div>
        </div>

        <p id="confirm-modal-message" class="text-sm text-slate-600 mb-6 leading-relaxed">
            Tem certeza que deseja prosseguir com esta operação?
        </p>

        <form id="confirm-modal-form" method="POST" action="">
            <div id="confirm-modal-hidden-fields"></div>

            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="Cerbero.closeModal('global-confirm-modal')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="confirm-modal-submit-btn" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition-colors">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Específico para Encerramento de Sessão (Logout) -->
<div id="logout-confirm-modal" class="cerbero-modal hidden fixed inset-0 z-50 overflow-y-auto items-center justify-center p-4">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="Cerbero.closeModal('logout-confirm-modal')"></div>

    <div class="relative bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl modal-content-anim border border-slate-100 z-10 text-center">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 shadow-inner">
            <i class="fa-solid fa-arrow-right-from-bracket text-xl"></i>
        </div>
        
        <h3 class="text-lg font-bold text-slate-900 mb-1">Encerrar Sessão?</h3>
        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
            Você precisará inserir suas credenciais novamente para acessar o sistema.
        </p>

        <div class="flex items-center justify-center space-x-3">
            <button type="button" onclick="Cerbero.closeModal('logout-confirm-modal')" class="w-1/2 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Cancelar
            </button>
            <a href="backend/logout.php" class="w-1/2 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md shadow-rose-600/20 text-center transition-colors">
                Sim, Sair
            </a>
        </div>
    </div>
</div>

<script>
    // Função auxiliar para acionar o modal de logout
    function confirmarLogout() {
        Cerbero.openModal('logout-confirm-modal');
    }
</script>
