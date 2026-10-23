<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Gestão de Solicitações de Acesso
 * ========================================================================
 */

$pageTitle = 'Solicitações de Acesso';
$pageHeading = 'Solicitações de Acesso';
$pageSubtitle = 'Triagem, aprovação e controle de pedidos de acesso ao sistema';

require_once __DIR__ . '/config/auth.php';

// Exige autenticação
exigirAutenticacao();

$usuarioAtual = getUsuarioLogado();
$podeGerenciar = in_array($usuarioAtual['nivel_acesso'], ['Administrador', 'Gerente']);
$pdo = getConexao();

$statusFiltro = trim($_GET['status'] ?? 'Pendente');
$solicitacoes = [];
$totais = [
    'Pendente'  => 0,
    'Aprovada'  => 0,
    'Rejeitada' => 0
];

if ($pdo) {
    try {
        // Contagens por status
        $stmtC = $pdo->query("SELECT status, COUNT(*) AS qtd FROM solicitacoes GROUP BY status");
        while ($row = $stmtC->fetch()) {
            if (isset($totais[$row['status']])) {
                $totais[$row['status']] = (int) $row['qtd'];
            }
        }

        // Busca com filtro
        $sql = "
            SELECT s.*, u.nome AS analisador_nome 
            FROM solicitacoes s 
            LEFT JOIN usuarios u ON s.analisado_por = u.id 
        ";

        if (in_array($statusFiltro, ['Pendente', 'Aprovada', 'Rejeitada'])) {
            $sql .= " WHERE s.status = :status";
            $sql .= " ORDER BY s.criado_em DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':status' => $statusFiltro]);
        } else {
            $sql .= " ORDER BY s.criado_em DESC";
            $stmt = $pdo->query($sql);
        }

        $solicitacoes = $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Erro ao carregar solicitações: " . $e->getMessage());
    }
} else {
    // Fallback de demonstração
    $totais['Pendente'] = 2;
    $totais['Aprovada'] = 1;
    $totais['Rejeitada'] = 1;
    $solicitacoes = [
        [
            'id' => 1,
            'nome' => 'Mariana Costa',
            'email' => 'mariana.costa@empresa.com',
            'departamento' => 'Compliance',
            'nivel_desejado' => 'Gerente',
            'motivo' => 'Necessito de acesso ao painel para auditoria trimestral de conformidade.',
            'status' => 'Pendente',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'id' => 2,
            'nome' => 'Lucas Rocha',
            'email' => 'lucas.rocha@empresa.com',
            'departamento' => 'Suporte Técnico',
            'nivel_desejado' => 'Operador',
            'motivo' => 'Novo colaborador da equipe de suporte técnico e monitoramento.',
            'status' => 'Pendente',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ]
    ];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Área de Conteúdo Principal -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-4 sm:p-8 space-y-6 flex-1">
        
        <!-- Abas / Filtros de Status (Mobile First) -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4">
            <a href="solicitacoes.php?status=Pendente" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-bold transition-all <?= ($statusFiltro === 'Pendente') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                <i class="fa-solid fa-clock"></i>
                <span>Pendentes</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs <?= ($statusFiltro === 'Pendente') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' ?>">
                    <?= $totais['Pendente'] ?>
                </span>
            </a>

            <a href="solicitacoes.php?status=Aprovada" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-bold transition-all <?= ($statusFiltro === 'Aprovada') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span>Aprovadas</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs <?= ($statusFiltro === 'Aprovada') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' ?>">
                    <?= $totais['Aprovada'] ?>
                </span>
            </a>

            <a href="solicitacoes.php?status=Rejeitada" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-bold transition-all <?= ($statusFiltro === 'Rejeitada') ? 'bg-slate-800 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>Rejeitadas</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs <?= ($statusFiltro === 'Rejeitada') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' ?>">
                    <?= $totais['Rejeitada'] ?>
                </span>
            </a>

            <a href="solicitacoes.php?status=todas" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-bold transition-all <?= ($statusFiltro === 'todas') ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                <i class="fa-solid fa-list"></i>
                <span>Todas</span>
            </a>
        </div>

        <!-- Lista de Solicitações -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">
                        <?= $statusFiltro === 'Pendente' ? 'Solicitações Aguardando Avaliação' : 'Histórico de Solicitações' ?>
                    </h3>
                    <p class="text-xs text-slate-400">Gerenciamento de credenciais requeridas</p>
                </div>
            </div>

            <?php if (empty($solicitacoes)): ?>
                <div class="p-12 text-center text-slate-400">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-inbox text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Nenhuma solicitação encontrada</p>
                    <p class="text-xs text-slate-400 mt-1">Não há registros com o status selecionado.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100">
                    <?php foreach ($solicitacoes as $s): ?>
                        <div class="p-6 hover:bg-slate-50/60 transition-colors flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            
                            <!-- Dados do Solicitante -->
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-bold text-slate-800"><?= htmlspecialchars($s['nome']) ?></h4>
                                    
                                    <!-- Badge de Status -->
                                    <?php if ($s['status'] === 'Pendente'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock text-[10px] mr-1.5 animate-pulse"></i>
                                            Pendente
                                        </span>
                                    <?php elseif ($s['status'] === 'Aprovada'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-check text-[10px] mr-1.5"></i>
                                            Aprovada
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-xmark text-[10px] mr-1.5"></i>
                                            Rejeitada
                                        </span>
                                    <?php endif; ?>

                                    <!-- Nível Desejado -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        Nível: <?= htmlspecialchars($s['nivel_desejado']) ?>
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <span><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> <?= htmlspecialchars($s['email']) ?></span>
                                    <span><i class="fa-solid fa-building mr-1 text-slate-400"></i> <?= htmlspecialchars($s['departamento']) ?></span>
                                    <span><i class="fa-solid fa-calendar mr-1 text-slate-400"></i> <?= date('d/m/Y H:i', strtotime($s['criado_em'])) ?></span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed">
                                    <span class="font-bold text-slate-600 block mb-0.5">Motivo Informado:</span>
                                    <?= nl2br(htmlspecialchars($s['motivo'])) ?>
                                </div>

                                <?php if (!empty($s['resposta_observacao'])): ?>
                                    <div class="text-xs text-slate-500 italic">
                                        Obs. da Análise: "<?= htmlspecialchars($s['resposta_observacao']) ?>" 
                                        <?= !empty($s['analisador_nome']) ? "por {$s['analisador_nome']}" : '' ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Ações (Aprovar / Rejeitar) -->
                            <?php if ($s['status'] === 'Pendente' && $podeGerenciar): ?>
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <button type="button" 
                                        onclick="aprovarSolicitacao(<?= $s['id'] ?>, '<?= addslashes(htmlspecialchars($s['nome'])) ?>')"
                                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 active:scale-95 transition-all flex items-center space-x-2">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Aprovar Acesso</span>
                                    </button>

                                    <button type="button" 
                                        onclick="rejeitarSolicitacao(<?= $s['id'] ?>, '<?= addslashes(htmlspecialchars($s['nome'])) ?>')"
                                        class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200 active:scale-95 transition-all flex items-center space-x-2">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>Rejeitar</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>

<script>
    function aprovarSolicitacao(id, nome) {
        Cerbero.confirmModal({
            title: 'Aprovar Solicitação de Acesso',
            message: `Você está prestes a aprovar o acesso para ${nome}. A conta de usuário será criada e ativada imediatamente.`,
            confirmText: 'Confirmar Aprovação',
            confirmColor: 'emerald',
            formAction: 'backend/solicitacao_acao.php',
            hiddenInputs: {
                id: id,
                acao: 'aprovar',
                origem: 'solicitacoes.php'
            }
        });
    }

    function rejeitarSolicitacao(id, nome) {
        Cerbero.confirmModal({
            title: 'Rejeitar Solicitação de Acesso',
            message: `Confirma a rejeição do pedido de acesso de ${nome}?`,
            confirmText: 'Confirmar Rejeição',
            confirmColor: 'red',
            formAction: 'backend/solicitacao_acao.php',
            hiddenInputs: {
                id: id,
                acao: 'rejeitar',
                origem: 'solicitacoes.php'
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
