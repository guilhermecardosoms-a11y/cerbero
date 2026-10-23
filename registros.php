<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Controle de Registros & Ativos
 * ========================================================================
 */

$pageTitle = 'Registros & Cadastros';
$pageHeading = 'Registros & Cadastros';
$pageSubtitle = 'Inventário e cadastro de ativos, infraestrutura e registros de segurança';

require_once __DIR__ . '/config/auth.php';

// Exige autenticação
exigirAutenticacao();

$usuarioAtual = getUsuarioLogado();
$podeExcluir = in_array($usuarioAtual['nivel_acesso'], ['Administrador', 'Gerente']);
$pdo = getConexao();

$registros = [];
$totalRegistros = 0;
$termoBusca = trim($_GET['busca'] ?? '');
$categoriaFiltro = trim($_GET['categoria'] ?? '');

if ($pdo) {
    try {
        $sql = "
            SELECT r.*, u.nome AS autor_nome 
            FROM registros r 
            LEFT JOIN usuarios u ON r.criado_por = u.id 
            WHERE 1=1
        ";
        $params = [];

        if (!empty($termoBusca)) {
            $sql .= " AND (r.titulo LIKE :busca OR r.descricao LIKE :busca)";
            $params[':busca'] = "%{$termoBusca}%";
        }

        if (!empty($categoriaFiltro)) {
            $sql .= " AND r.categoria = :categoria";
            $params[':categoria'] = $categoriaFiltro;
        }

        $sql .= " ORDER BY r.criado_em DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();
        $totalRegistros = count($registros);
    } catch (PDOException $e) {
        error_log("Erro ao carregar registros: " . $e->getMessage());
    }
} else {
    // Fallback de demonstração
    $registros = [
        [
            'id' => 1,
            'titulo' => 'Servidor Principal Cerbero Node-01',
            'categoria' => 'Infraestrutura',
            'descricao' => 'Servidor dedicado para balanceamento de carga e gateway de autenticação.',
            'status' => 'Ativo',
            'prioridade' => 'Crítica',
            'autor_nome' => 'Administrador Cerbero',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'id' => 2,
            'titulo' => 'Firewall Fortinet 200F',
            'categoria' => 'Segurança',
            'descricao' => 'Configuração de regras de proteção contra DDoS e filtragem de portas sensíveis.',
            'status' => 'Ativo',
            'prioridade' => 'Alta',
            'autor_nome' => 'Administrador Cerbero',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'id' => 3,
            'titulo' => 'Certificado SSL Wildcard',
            'categoria' => 'Segurança',
            'descricao' => 'Renovação do certificado SSL TLSv1.3 com validade até outubro de 2027.',
            'status' => 'Ativo',
            'prioridade' => 'Média',
            'autor_nome' => 'Carlos Silva',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
    $totalRegistros = count($registros);
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Área de Conteúdo Principal -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-4 sm:p-8 space-y-6 flex-1">
        
        <!-- Barra de Ações Superiores (Busca + Filtros + Novo Registro) -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            
            <form method="GET" action="registros.php" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <!-- Campo de Busca -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar título ou descrição do registro..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all">
                </div>

                <!-- Filtro de Categoria -->
                <select name="categoria" onchange="this.form.submit()" class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Todas as Categorias</option>
                    <option value="Infraestrutura" <?= $categoriaFiltro === 'Infraestrutura' ? 'selected' : '' ?>>Infraestrutura</option>
                    <option value="Segurança" <?= $categoriaFiltro === 'Segurança' ? 'selected' : '' ?>>Segurança</option>
                    <option value="Backup" <?= $categoriaFiltro === 'Backup' ? 'selected' : '' ?>>Backup</option>
                    <option value="Auditoria" <?= $categoriaFiltro === 'Auditoria' ? 'selected' : '' ?>>Auditoria</option>
                    <option value="Equipamento" <?= $categoriaFiltro === 'Equipamento' ? 'selected' : '' ?>>Equipamento</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors">
                    Filtrar
                </button>

                <?php if (!empty($termoBusca) || !empty($categoriaFiltro)): ?>
                    <a href="registros.php" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold text-center transition-colors">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>

            <!-- Botão Novo Registro -->
            <button type="button" onclick="abrirModalNovoRegistro()" class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-md shadow-teal-600/20 active:scale-95 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Novo Registro</span>
            </button>
        </div>

        <!-- Tabela de Registros -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <h3 class="font-bold text-slate-800 text-base">Registros Cadastrados</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        <?= $totalRegistros ?> itens
                    </span>
                </div>
                <span class="text-xs text-slate-400">Ativos sob governança Cerbero</span>
            </div>

            <?php if (empty($registros)): ?>
                <div class="p-12 text-center text-slate-400">
                    <i class="fa-solid fa-folder-open text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-medium">Nenhum registro encontrado no momento.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/70 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Título & Descrição</th>
                                <th class="px-6 py-4">Categoria</th>
                                <th class="px-6 py-4">Prioridade</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Responsável</th>
                                <th class="px-6 py-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($registros as $r): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4 max-w-sm">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($r['titulo']) ?></div>
                                        <div class="text-xs text-slate-500 truncate" title="<?= htmlspecialchars($r['descricao']) ?>">
                                            <?= htmlspecialchars($r['descricao']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <span class="inline-block px-2.5 py-1 rounded-md bg-slate-100 font-semibold text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($r['categoria']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <?php
                                            $prioClass = 'text-slate-600 bg-slate-100';
                                            if ($r['prioridade'] === 'Crítica' || $r['prioridade'] === 'Alta') {
                                                $prioClass = 'text-rose-700 bg-rose-50 border border-rose-200';
                                            } elseif ($r['prioridade'] === 'Média') {
                                                $prioClass = 'text-amber-700 bg-amber-50 border border-amber-200';
                                            } else {
                                                $prioClass = 'text-emerald-700 bg-emerald-50 border border-emerald-200';
                                            }
                                        ?>
                                        <span class="px-2.5 py-1 rounded-md font-bold <?= $prioClass ?>">
                                            <?= htmlspecialchars($r['prioridade']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-semibold">
                                        <span class="inline-flex items-center space-x-1.5 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span><?= htmlspecialchars($r['status']) ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">
                                        <?= htmlspecialchars($r['autor_nome'] ?? 'Sistema') ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        <!-- Editar -->
                                        <button type="button" 
                                            onclick="editarRegistro(<?= htmlspecialchars(json_encode($r)) ?>)"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                                            title="Editar Registro">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>

                                        <!-- Excluir (se admin/gerente) -->
                                        <?php if ($podeExcluir): ?>
                                            <button type="button" 
                                                onclick="excluirRegistro(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['titulo'])) ?>')"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors"
                                                title="Excluir Registro">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Modal Customizado de Registro (Criar / Editar) -->
    <div id="modal-registro" class="cerbero-modal hidden fixed inset-0 z-50 overflow-y-auto items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="Cerbero.closeModal('modal-registro')"></div>

        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl modal-content-anim border border-slate-100 z-10">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 id="modal-registro-titulo" class="text-lg font-bold text-slate-800">Novo Registro</h3>
                <button type="button" onclick="Cerbero.closeModal('modal-registro')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="backend/registro_salvar.php" method="POST" class="mt-4 space-y-4">
                <input type="hidden" id="registro_id" name="id" value="">

                <div>
                    <label for="registro_titulo" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Título do Registro</label>
                    <input type="text" id="registro_titulo" name="titulo" required placeholder="Ex: Servidor de Aplicação AWS EC2"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="registro_categoria" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Categoria</label>
                        <select id="registro_categoria" name="categoria" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="Infraestrutura">Infraestrutura</option>
                            <option value="Segurança">Segurança</option>
                            <option value="Backup">Backup</option>
                            <option value="Auditoria">Auditoria</option>
                            <option value="Equipamento">Equipamento</option>
                        </select>
                    </div>

                    <div>
                        <label for="registro_prioridade" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Prioridade</label>
                        <select id="registro_prioridade" name="prioridade" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="Baixa">Baixa</option>
                            <option value="Média" selected>Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="registro_status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                    <select id="registro_status" name="status" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="Ativo" selected>Ativo</option>
                        <option value="Pendente">Pendente</option>
                        <option value="Concluído">Concluído</option>
                        <option value="Arquivado">Arquivado</option>
                    </select>
                </div>

                <div>
                    <label for="registro_descricao" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Descrição Detalhada</label>
                    <textarea id="registro_descricao" name="descricao" rows="3" required placeholder="Detalhes técnicos, parâmetros ou especificações..."
                        class="w-full p-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="Cerbero.closeModal('modal-registro')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-md transition-colors">
                        Salvar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    function abrirModalNovoRegistro() {
        document.getElementById('modal-registro-titulo').innerText = 'Novo Registro de Cadastro';
        document.getElementById('registro_id').value = '';
        document.getElementById('registro_titulo').value = '';
        document.getElementById('registro_categoria').value = 'Infraestrutura';
        document.getElementById('registro_prioridade').value = 'Média';
        document.getElementById('registro_status').value = 'Ativo';
        document.getElementById('registro_descricao').value = '';
        Cerbero.openModal('modal-registro');
    }

    function editarRegistro(r) {
        document.getElementById('modal-registro-titulo').innerText = 'Editar Registro: ' + r.titulo;
        document.getElementById('registro_id').value = r.id;
        document.getElementById('registro_titulo').value = r.titulo;
        document.getElementById('registro_categoria').value = r.categoria;
        document.getElementById('registro_prioridade').value = r.prioridade;
        document.getElementById('registro_status').value = r.status;
        document.getElementById('registro_descricao').value = r.descricao;
        Cerbero.openModal('modal-registro');
    }

    function excluirRegistro(id, titulo) {
        Cerbero.confirmModal({
            title: 'Excluir Registro',
            message: `Tem certeza que deseja excluir permanentemente o registro "${titulo}"?`,
            confirmText: 'Sim, Excluir',
            confirmColor: 'red',
            formAction: 'backend/registro_excluir.php',
            hiddenInputs: { id: id }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
