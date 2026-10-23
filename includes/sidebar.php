<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Menu Lateral de Navegação (Sidebar)
 * Mobile First com suporte a colapso e identificação visual do usuário
 * ========================================================================
 */
require_once __DIR__ . '/../config/auth.php';

$usuario = getUsuarioLogado();
$pagina = basename($_SERVER['PHP_SELF']);

// Contagem rápida de solicitações pendentes para o badge dinâmico
$qtdSolicitacoesPendentes = 0;
$pdo = getConexao();
if ($pdo) {
    try {
        $stmtP = $pdo->query("SELECT COUNT(*) FROM solicitacoes WHERE status = 'Pendente'");
        $qtdSolicitacoesPendentes = (int) $stmtP->fetchColumn();
    } catch (Exception $e) {
        $qtdSolicitacoesPendentes = 0;
    }
}
?>

<!-- Backdrop para Mobile -->
<div id="sidebar-backdrop" onclick="Cerbero.toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm hidden md:hidden transition-opacity"></div>

<!-- Barra Lateral / Sidebar -->
<aside id="sidebar-menu" class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-900 text-white flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static md:inset-auto transition-transform duration-300 ease-in-out shadow-2xl border-r border-slate-800">
    
    <div>
        <!-- Cabeçalho do Brand / Logotipo -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/40">
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-900/40 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-shield-halved text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-wider bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">CERBERO</h1>
                    <p class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold">Security & Control</p>
                </div>
            </a>
            <button onclick="Cerbero.toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-2 focus:outline-none">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Perfil Resumido do Usuário Conectado -->
        <div class="p-5 border-b border-slate-800/60 bg-slate-800/20">
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-full bg-slate-700/80 border-2 border-emerald-500/40 flex items-center justify-center font-bold text-white shadow-inner">
                    <?= strtoupper(substr($usuario['nome'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($usuario['nome'] ?? 'Usuário') ?></p>
                    <div class="mt-1 flex items-center">
                        <?php
                            $nivel = $usuario['nivel_acesso'] ?? 'Operador';
                            $badgeClass = 'bg-emerald-950 text-emerald-300 border-emerald-600/40';
                            if ($nivel === 'Administrador') {
                                $badgeClass = 'bg-purple-950 text-purple-300 border-purple-600/40';
                            } elseif ($nivel === 'Gerente') {
                                $badgeClass = 'bg-sky-950 text-sky-300 border-sky-600/40';
                            }
                        ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border <?= $badgeClass ?>">
                            <i class="fa-solid fa-circle text-[6px] mr-1.5 animate-pulse"></i>
                            <?= htmlspecialchars($nivel) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Links de Navegação -->
        <nav class="p-4 space-y-1.5 text-sm font-medium">
            <p class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Principal</p>
            
            <!-- Dashboard -->
            <a href="index.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?= ($pagina === 'index.php') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard</span>
                </div>
                <i class="fa-solid fa-chevron-right text-xs opacity-60"></i>
            </a>

            <!-- Usuários -->
            <a href="usuarios.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?= ($pagina === 'usuarios.php') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Usuários</span>
                </div>
                <i class="fa-solid fa-chevron-right text-xs opacity-60"></i>
            </a>

            <!-- Registros / Cadastros -->
            <a href="registros.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?= ($pagina === 'registros.php') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-database w-5 text-center"></i>
                    <span>Registros & Ativos</span>
                </div>
                <i class="fa-solid fa-chevron-right text-xs opacity-60"></i>
            </a>

            <p class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Segurança & Acessos</p>

            <!-- Solicitações de Acesso -->
            <a href="solicitacoes.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-colors <?= ($pagina === 'solicitacoes.php') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' ?>">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-user-shield w-5 text-center"></i>
                    <span>Solicitações</span>
                </div>
                <?php if ($qtdSolicitacoesPendentes > 0): ?>
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-rose-500 rounded-full animate-bounce">
                        <?= $qtdSolicitacoesPendentes ?>
                    </span>
                <?php else: ?>
                    <i class="fa-solid fa-chevron-right text-xs opacity-60"></i>
                <?php endif; ?>
            </a>
        </nav>
    </div>

    <!-- Rodapé da Barra Lateral com Logout Seguro -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/30">
        <button onclick="confirmarLogout()" class="w-full flex items-center justify-center space-x-2.5 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-rose-600/90 text-slate-200 hover:text-white transition-all duration-200 text-sm font-semibold shadow-sm group">
            <i class="fa-solid fa-arrow-right-from-bracket group-hover:translate-x-0.5 transition-transform"></i>
            <span>Encerrar Sessão</span>
        </button>
    </div>
</aside>
