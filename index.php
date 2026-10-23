<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Painel Administrativo Principal (Dashboard)
 * ========================================================================
 * Atende a todas as Regras de Negócio (CORE):
 * - Exibir a quantidade total de usuários cadastrados.
 * - Exibir a quantidade de registros cadastrados. Mostrar o status do sistema.
 * - Exibir solicitações de acesso pendentes.
 * - Apresentar uma mensagem de boas-vindas ao usuário.
 * - Identificar o nome e o nível de acesso do usuário conectado.
 */

$pageTitle = 'Dashboard Administrativo';
$pageHeading = 'Dashboard';
$pageSubtitle = 'Visão geral de usuários, registros e segurança do sistema';

require_once __DIR__ . '/config/auth.php';

// Exige autenticação para acessar o painel
exigirAutenticacao();

$usuario = getUsuarioLogado();
$pdo = getConexao();

// Valores padrão caso o banco esteja indisponível
$totalUsuarios = 0;
$totalRegistros = 0;
$totalSolicitacoesPendentes = 0;
$solicitacoesPendentes = [];
$ultimosRegistros = [];
$statusDb = statusBancoDados();

if ($pdo) {
    try {
        // 1. Quantidade total de usuários cadastrados
        $stmtUsers = $pdo->query("SELECT COUNT(*) FROM usuarios");
        $totalUsuarios = (int) $stmtUsers->fetchColumn();

        // 2. Quantidade total de registros cadastrados
        $stmtRegs = $pdo->query("SELECT COUNT(*) FROM registros");
        $totalRegistros = (int) $stmtRegs->fetchColumn();

        // 3. Quantidade de solicitações de acesso pendentes
        $stmtReqs = $pdo->query("SELECT COUNT(*) FROM solicitacoes WHERE status = 'Pendente'");
        $totalSolicitacoesPendentes = (int) $stmtReqs->fetchColumn();

        // Lista de solicitações pendentes para ação rápida
        $stmtListReq = $pdo->query("SELECT * FROM solicitacoes WHERE status = 'Pendente' ORDER BY criado_em DESC LIMIT 5");
        $solicitacoesPendentes = $stmtListReq->fetchAll();

        // Lista dos últimos registros cadastrados
        $stmtListReg = $pdo->query("
            SELECT r.*, u.nome AS autor_nome 
            FROM registros r 
            LEFT JOIN usuarios u ON r.criado_por = u.id 
            ORDER BY r.criado_em DESC LIMIT 5
        ");
        $ultimosRegistros = $stmtListReg->fetchAll();

    } catch (PDOException $e) {
        error_log("Erro ao carregar métricas do dashboard: " . $e->getMessage());
    }
} else {
    // Dados de demonstração se banco local estiver offline
    $totalUsuarios = 3;
    $totalRegistros = 5;
    $totalSolicitacoesPendentes = 2;
    $solicitacoesPendentes = [
        [
            'id' => 1,
            'nome' => 'Mariana Costa',
            'email' => 'mariana.costa@empresa.com',
            'departamento' => 'Compliance',
            'nivel_desejado' => 'Gerente',
            'motivo' => 'Auditoria trimestral de governança de TI.',
            'criado_em' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'nome' => 'Lucas Rocha',
            'email' => 'lucas.rocha@empresa.com',
            'departamento' => 'Suporte Técnico',
            'nivel_desejado' => 'Operador',
            'motivo' => 'Monitoramento operacional e triagem de chamados.',
            'criado_em' => date('Y-m-d H:i:s')
        ]
    ];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Área de Conteúdo Principal -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-4 sm:p-8 space-y-8 flex-1">
        
        <!-- CARD DE BOAS-VINDAS E IDENTIFICAÇÃO DO USUÁRIO CONECTADO (CORE) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 p-6 sm:p-8 text-white shadow-xl border border-slate-700/50">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <!-- Saudação Dinâmica -->
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-3">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Central de Administração</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Bem-vindo(a) de volta, <?= htmlspecialchars($usuario['nome']) ?>!
                    </h1>
                    
                    <p class="mt-2 text-sm sm:text-base text-slate-300 max-w-2xl leading-relaxed">
                        Você está autenticado no Sistema Cerbero. Gerencie novos usuários, atenda solicitações de acesso pendentes e monitore os registros cadastrados.
                    </p>
                </div>

                <!-- Identificação do Usuário e Nível de Acesso (CORE) -->
                <div class="flex items-center space-x-4 bg-slate-800/80 backdrop-blur-md p-4 rounded-2xl border border-slate-700 shadow-inner flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center font-black text-white text-lg shadow-md">
                        <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium">Usuário Conectado</div>
                        <div class="text-sm font-bold text-white"><?= htmlspecialchars($usuario['nome']) ?></div>
                        <div class="mt-1">
                            <?php
                                $badgeStyle = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
                                if ($usuario['nivel_acesso'] === 'Administrador') {
                                    $badgeStyle = 'bg-purple-500/20 text-purple-300 border-purple-500/30';
                                } elseif ($usuario['nivel_acesso'] === 'Gerente') {
                                    $badgeStyle = 'bg-sky-500/20 text-sky-300 border-sky-500/30';
                                }
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border <?= $badgeStyle ?>">
                                <i class="fa-solid fa-id-badge mr-1.5 text-[10px]"></i>
                                <?= htmlspecialchars($usuario['nivel_acesso']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Efeito decorativo de fundo -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- CARDS DE MÉTRICAS E REGRAS DE NEGÓCIO (CORE) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: Quantidade total de usuários cadastrados (CORE) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total de Usuários</p>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2"><?= $totalUsuarios ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Controle de acessos</span>
                    <a href="usuarios.php" class="font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center space-x-1">
                        <span>Gerenciar</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Quantidade de registros cadastrados (CORE) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Registros Ativos</p>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2"><?= $totalRegistros ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Itens e Ativos</span>
                    <a href="registros.php" class="font-semibold text-teal-600 hover:text-teal-800 inline-flex items-center space-x-1">
                        <span>Ver lista</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Solicitações de acesso pendentes (CORE) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Solicitações Pendentes</p>
                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2"><?= $totalSolicitacoesPendentes ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl <?= $totalSolicitacoesPendentes > 0 ? 'bg-rose-50 text-rose-600 animate-pulse' : 'bg-slate-100 text-slate-500' ?> flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="<?= $totalSolicitacoesPendentes > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500' ?>">
                        <?= $totalSolicitacoesPendentes > 0 ? 'Aguardando validação' : 'Sem pendências' ?>
                    </span>
                    <a href="solicitacoes.php" class="font-semibold text-rose-600 hover:text-rose-800 inline-flex items-center space-x-1">
                        <span>Avaliar</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 4: Status do sistema (CORE) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Status do Sistema</p>
                        <h3 class="text-xl font-extrabold text-slate-800 mt-2 flex items-center space-x-2">
                            <span class="inline-block w-3 h-3 rounded-full <?= $statusDb ? 'bg-emerald-500 animate-ping' : 'bg-amber-500' ?>"></span>
                            <span><?= $statusDb ? 'Operacional' : 'Local Standby' ?></span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">PHP <?= phpversion() ?> &bull; MySQL</span>
                    <span class="text-emerald-600 font-semibold">100% Ativo</span>
                </div>
            </div>

        </div>

        <!-- SEÇÃO: SOLICITAÇÕES PENDENTES DE ACESSO (CORE) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="fa-solid fa-envelope-open-text text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Solicitações de Acesso Pendentes</h3>
                        <p class="text-xs text-slate-500">Colaboradores que solicitaram credenciais no sistema</p>
                    </div>
                </div>

                <a href="solicitacoes.php" class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    <span>Ver Todas as Solicitações</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <?php if (empty($solicitacoesPendentes)): ?>
                <div class="p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-circle-check text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-700">Tudo limpo por aqui!</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Não existem solicitações de acesso aguardando aprovação no momento.
                    </p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/70 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Solicitante</th>
                                <th class="px-6 py-4">Departamento</th>
                                <th class="px-6 py-4">Nível Desejado</th>
                                <th class="px-6 py-4">Motivo / Justificativa</th>
                                <th class="px-6 py-4 text-right">Ações Rápidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($solicitacoesPendentes as $solic): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($solic['nome']) ?></div>
                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($solic['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        <?= htmlspecialchars($solic['departamento']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($solic['nivel_desejado']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($solic['motivo']) ?>">
                                        <?= htmlspecialchars($solic['motivo']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        <!-- Botão Aprovar aciona Modal Customizado -->
                                        <button type="button" 
                                            onclick="aprovarSolicitacao(<?= $solic['id'] ?>, '<?= addslashes(htmlspecialchars($solic['nome'])) ?>')"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-colors">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                            <span>Aprovar</span>
                                        </button>
                                        
                                        <!-- Botão Rejeitar aciona Modal Customizado -->
                                        <button type="button" 
                                            onclick="rejeitarSolicitacao(<?= $solic['id'] ?>, '<?= addslashes(htmlspecialchars($solic['nome'])) ?>')"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold border border-rose-200 transition-colors">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                            <span>Rejeitar</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- SEÇÃO: ÚLTIMOS REGISTROS CADASTRADOS -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Registros e Ativos Recentes</h3>
                        <p class="text-xs text-slate-500">Últimos itens cadastrados na base do Cerbero</p>
                    </div>
                </div>

                <a href="registros.php" class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Novo Registro</span>
                </a>
            </div>

            <?php if (empty($ultimosRegistros)): ?>
                <div class="p-10 text-center text-slate-400 text-sm">
                    Nenhum registro cadastrado ainda.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/70 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Título do Registro</th>
                                <th class="px-6 py-4">Categoria</th>
                                <th class="px-6 py-4">Prioridade</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($ultimosRegistros as $reg): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        <?= htmlspecialchars($reg['titulo']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                        <span class="inline-block px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($reg['categoria']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <?php
                                            $prioClass = 'text-slate-600 bg-slate-100';
                                            if ($reg['prioridade'] === 'Crítica' || $reg['prioridade'] === 'Alta') {
                                                $prioClass = 'text-rose-700 bg-rose-50 border border-rose-200';
                                            } elseif ($reg['prioridade'] === 'Média') {
                                                $prioClass = 'text-amber-700 bg-amber-50 border border-amber-200';
                                            } else {
                                                $prioClass = 'text-emerald-700 bg-emerald-50 border border-emerald-200';
                                            }
                                        ?>
                                        <span class="px-2.5 py-1 rounded-md font-semibold <?= $prioClass ?>">
                                            <?= htmlspecialchars($reg['prioridade']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <span class="inline-flex items-center space-x-1.5 font-semibold text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span><?= htmlspecialchars($reg['status']) ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400">
                                        <?= date('d/m/Y H:i', strtotime($reg['criado_em'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

<script>
    /**
     * Aciona o modal customizado para aprovação da solicitação (substitui confirm() nativo)
     */
    function aprovarSolicitacao(id, nome) {
        Cerbero.confirmModal({
            title: 'Aprovar Acesso',
            message: `Deseja realmente aprovar a solicitação de acesso para ${nome}? Uma nova credencial de usuário será gerada automaticamente.`,
            confirmText: 'Sim, Aprovar Acesso',
            confirmColor: 'emerald',
            formAction: 'backend/solicitacao_acao.php',
            hiddenInputs: {
                id: id,
                acao: 'aprovar',
                origem: 'index.php'
            }
        });
    }

    /**
     * Aciona o modal customizado para rejeição da solicitação (substitui confirm() nativo)
     */
    function rejeitarSolicitacao(id, nome) {
        Cerbero.confirmModal({
            title: 'Rejeitar Solicitação',
            message: `Tem certeza que deseja rejeitar o pedido de acesso de ${nome}?`,
            confirmText: 'Sim, Rejeitar',
            confirmColor: 'red',
            formAction: 'backend/solicitacao_acao.php',
            hiddenInputs: {
                id: id,
                acao: 'rejeitar',
                origem: 'index.php'
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
