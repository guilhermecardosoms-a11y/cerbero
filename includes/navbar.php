<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Barra Superior de Navegação (Navbar)
 * Exibe status em tempo real do sistema, notificações e perfil
 * ========================================================================
 */
require_once __DIR__ . '/../config/auth.php';

$usuario = getUsuarioLogado();
$statusDb = statusBancoDados();
?>

<header class="h-20 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between shadow-sm sticky top-0 z-30">
    
    <!-- Botão Hambúrguer Mobile + Título da Página -->
    <div class="flex items-center space-x-4">
        <button onclick="Cerbero.toggleSidebar()" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 focus:outline-none transition-colors">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight">
                <?= isset($pageHeading) ? htmlspecialchars($pageHeading) : 'Painel de Controle' ?>
            </h2>
            <p class="text-xs text-slate-500 hidden sm:block">
                <?= isset($pageSubtitle) ? htmlspecialchars($pageSubtitle) : 'Gerenciamento centralizado de acessos e registros' ?>
            </p>
        </div>
    </div>

    <!-- Indicadores de Status e Ações do Topo -->
    <div class="flex items-center space-x-3 sm:space-x-4">
        
        <!-- Indicador de Status do Sistema (Regra de Negócio) -->
        <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-semibold border <?= $statusDb ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $statusDb ? 'bg-emerald-400' : 'bg-amber-400' ?> opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 <?= $statusDb ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
            </span>
            <span class="hidden md:inline"><?= $statusDb ? 'Sistema Operacional' : 'Banco Local Offline' ?></span>
            <span class="md:hidden"><?= $statusDb ? 'Online' : 'Offline' ?></span>
        </div>

        <!-- Atalho de Solicitações Pendentes -->
        <a href="solicitacoes.php" title="Ver solicitações pendentes" class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
            <i class="fa-solid fa-bell text-base"></i>
            <?php if (isset($qtdSolicitacoesPendentes) && $qtdSolicitacoesPendentes > 0): ?>
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white shadow-sm animate-pulse">
                    <?= $qtdSolicitacoesPendentes ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Separador -->
        <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

        <!-- Perfil do Usuário Conectado -->
        <div class="flex items-center space-x-3">
            <div class="text-right hidden sm:block">
                <span class="block text-sm font-bold text-slate-800 leading-tight">
                    <?= htmlspecialchars($usuario['nome'] ?? 'Conectado') ?>
                </span>
                <span class="block text-[11px] font-medium text-slate-500">
                    <?= htmlspecialchars($usuario['nivel_acesso'] ?? 'Operador') ?>
                </span>
            </div>
            
            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-slate-900/10">
                <?= strtoupper(substr($usuario['nome'] ?? 'C', 0, 1)) ?>
            </div>
        </div>

    </div>
</header>
