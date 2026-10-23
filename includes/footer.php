<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Rodapé Global (Footer)
 * ========================================================================
 */
?>
        <!-- Rodapé do Conteúdo Principal -->
        <footer class="mt-auto py-6 px-4 sm:px-8 border-t border-slate-200/80 bg-white/60 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 space-y-2 sm:space-y-0">
            <div>
                <span class="font-bold text-slate-700">CERBERO</span> &copy; <?= date('Y') ?> &bull; Sistema de Gerenciamento &amp; Administração
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center space-x-1.5 text-slate-500">
                    <i class="fa-solid fa-lock text-[10px] text-emerald-600"></i>
                    <span>Sessão Segura &bull; PDO MySQL</span>
                </span>
                <span class="text-slate-300">|</span>
                <span>v2.4.0</span>
            </div>
        </footer>
    </main>

    <!-- Componentes Globais de Modais -->
    <?php require_once __DIR__ . '/modals.php'; ?>

    <!-- Biblioteca JS Cerbero -->
    <script src="assets/js/cerbero.js"></script>

    <!-- Disparador de Notificações Toast -->
    <?php require_once __DIR__ . '/toast.php'; ?>

</body>
</html>
