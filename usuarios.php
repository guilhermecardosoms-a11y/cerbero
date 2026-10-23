<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Gerenciamento de Usuários
 * ========================================================================
 */

$pageTitle = 'Controle de Usuários';
$pageHeading = 'Gerenciamento de Usuários';
$pageSubtitle = 'Cadastro, controle de credenciais e níveis de acesso';

require_once __DIR__ . '/config/auth.php';

// Exige autenticação
exigirAutenticacao();

$usuarioAtual = getUsuarioLogado();
$isAdmin = ($usuarioAtual['nivel_acesso'] === 'Administrador');
$pdo = getConexao();

$usuarios = [];
$totalUsuarios = 0;
$termoBusca = trim($_GET['busca'] ?? '');

if ($pdo) {
    try {
        if (!empty($termoBusca)) {
            $stmt = $pdo->prepare("
                SELECT * FROM usuarios 
                WHERE nome LIKE :b1 OR email LIKE :b2 OR departamento LIKE :b3 
                ORDER BY nome ASC
            ");
            $like = "%{$termoBusca}%";
            $stmt->execute([':b1' => $like, ':b2' => $like, ':b3' => $like]);
            $usuarios = $stmt->fetchAll();
        } else {
            $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
            $usuarios = $stmt->fetchAll();
        }
        $totalUsuarios = count($usuarios);
    } catch (PDOException $e) {
        error_log("Erro ao buscar usuários: " . $e->getMessage());
    }
} else {
    // Fallback de demonstração
    $usuarios = [
        [
            'id' => 1,
            'nome' => 'Administrador Cerbero',
            'email' => 'admin@cerbero.com',
            'nivel_acesso' => 'Administrador',
            'status' => 'Ativo',
            'departamento' => 'Segurança da Informação',
            'ultimo_login' => date('Y-m-d H:i:s'),
            'criado_em' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'nome' => 'Carlos Silva',
            'email' => 'carlos@cerbero.com',
            'nivel_acesso' => 'Gerente',
            'status' => 'Ativo',
            'departamento' => 'Operações & Infraestrutura',
            'ultimo_login' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'criado_em' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'id' => 3,
            'nome' => 'Ana Martins',
            'email' => 'ana@cerbero.com',
            'nivel_acesso' => 'Operador',
            'status' => 'Ativo',
            'departamento' => 'Atendimento ao Cliente',
            'ultimo_login' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'criado_em' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ]
    ];
    $totalUsuarios = count($usuarios);
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Área de Conteúdo Principal -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-4 sm:p-8 space-y-6 flex-1">
        
        <!-- Barra de Ações Superiores (Busca + Novo Usuário) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            
            <!-- Formulário de Busca -->
            <form method="GET" action="usuarios.php" class="flex-1 max-w-md flex items-center space-x-2">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" name="busca" value="<?= htmlspecialchars($termoBusca) ?>" placeholder="Buscar por nome, e-mail ou setor..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors">
                    Filtrar
                </button>
                <?php if (!empty($termoBusca)): ?>
                    <a href="usuarios.php" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>

            <!-- Botão Novo Usuário (se admin) -->
            <?php if ($isAdmin): ?>
                <button type="button" onclick="abrirModalNovoUsuario()" class="inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/20 active:scale-95 transition-all">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Novo Usuário</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Tabela de Usuários -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <h3 class="font-bold text-slate-800 text-base">Usuários Registrados</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <?= $totalUsuarios ?>
                    </span>
                </div>
                <span class="text-xs text-slate-400">Total cadastrado no banco</span>
            </div>

            <?php if (empty($usuarios)): ?>
                <div class="p-12 text-center text-slate-400">
                    <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-medium">Nenhum usuário localizado com os critérios informados.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/70 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Usuário</th>
                                <th class="px-6 py-4">Departamento</th>
                                <th class="px-6 py-4">Nível de Acesso</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Último Login</th>
                                <?php if ($isAdmin): ?>
                                    <th class="px-6 py-4 text-right">Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center flex-shrink-0">
                                                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800"><?= htmlspecialchars($u['nome']) ?></div>
                                                <div class="text-xs text-slate-400"><?= htmlspecialchars($u['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        <?= htmlspecialchars($u['departamento'] ?? 'Geral') ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            if ($u['nivel_acesso'] === 'Administrador') {
                                                $badgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                                            } elseif ($u['nivel_acesso'] === 'Gerente') {
                                                $badgeClass = 'bg-sky-50 text-sky-700 border-sky-200';
                                            }
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border <?= $badgeClass ?>">
                                            <?= htmlspecialchars($u['nivel_acesso']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <?php if ($u['status'] === 'Ativo'): ?>
                                            <span class="inline-flex items-center space-x-1.5 font-semibold text-emerald-700">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span>Ativo</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center space-x-1.5 font-semibold text-rose-600">
                                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                                <span>Inativo</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400">
                                        <?= !empty($u['ultimo_login']) ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca conectou' ?>
                                    </td>
                                    <?php if ($isAdmin): ?>
                                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                            <!-- Editar -->
                                            <button type="button" 
                                                onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                                                title="Editar Usuário">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </button>

                                            <!-- Excluir (apenas se não for o próprio usuário) -->
                                            <?php if ((int)$u['id'] !== (int)$usuarioAtual['id']): ?>
                                                <button type="button" 
                                                    onclick="excluirUsuario(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['nome'])) ?>')"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors"
                                                    title="Excluir Usuário">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Modal Customizado de Cadastro / Edição de Usuário (Substitui prompts) -->
    <div id="modal-usuario" class="cerbero-modal hidden fixed inset-0 z-50 overflow-y-auto items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm modal-backdrop-anim" onclick="Cerbero.closeModal('modal-usuario')"></div>

        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl modal-content-anim border border-slate-100 z-10">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 id="modal-usuario-titulo" class="text-lg font-bold text-slate-800">Novo Usuário</h3>
                <button type="button" onclick="Cerbero.closeModal('modal-usuario')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="backend/usuario_salvar.php" method="POST" class="mt-4 space-y-4" onsubmit="return validarFormUsuario(event)">
                <input type="hidden" id="usuario_id" name="id" value="">

                <div>
                    <label for="usuario_nome" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nome Completo</label>
                    <input type="text" id="usuario_nome" name="nome" required placeholder="Ex: Felipe Ramos"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="usuario_email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">E-mail</label>
                    <input type="email" id="usuario_email" name="email" required placeholder="felipe@cerbero.com"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="usuario_nivel" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nível de Acesso</label>
                        <select id="usuario_nivel" name="nivel_acesso" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Operador">Operador</option>
                            <option value="Gerente">Gerente</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>

                    <div>
                        <label for="usuario_status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                        <select id="usuario_status" name="status" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Ativo">Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="usuario_departamento" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Departamento</label>
                    <input type="text" id="usuario_departamento" name="departamento" placeholder="Ex: TI / Segurança"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="usuario_senha" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        <span id="label-senha">Senha</span> 
                        <span id="hint-senha" class="text-[10px] text-slate-400 font-normal hidden">(deixe em branco para não alterar)</span>
                    </label>
                    <input type="password" id="usuario_senha" name="senha" placeholder="Mínimo 6 caracteres"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="Cerbero.closeModal('modal-usuario')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition-colors">
                        Salvar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    function abrirModalNovoUsuario() {
        document.getElementById('modal-usuario-titulo').innerText = 'Novo Usuário';
        document.getElementById('usuario_id').value = '';
        document.getElementById('usuario_nome').value = '';
        document.getElementById('usuario_email').value = '';
        document.getElementById('usuario_nivel').value = 'Operador';
        document.getElementById('usuario_status').value = 'Ativo';
        document.getElementById('usuario_departamento').value = 'Tecnologia da Informação';
        document.getElementById('usuario_senha').value = '';
        document.getElementById('usuario_senha').required = true;
        document.getElementById('hint-senha').classList.add('hidden');
        Cerbero.openModal('modal-usuario');
    }

    function editarUsuario(u) {
        document.getElementById('modal-usuario-titulo').innerText = 'Editar Usuário: ' + u.nome;
        document.getElementById('usuario_id').value = u.id;
        document.getElementById('usuario_nome').value = u.nome;
        document.getElementById('usuario_email').value = u.email;
        document.getElementById('usuario_nivel').value = u.nivel_acesso;
        document.getElementById('usuario_status').value = u.status;
        document.getElementById('usuario_departamento').value = u.departamento || '';
        document.getElementById('usuario_senha').value = '';
        document.getElementById('usuario_senha').required = false;
        document.getElementById('hint-senha').classList.remove('hidden');
        Cerbero.openModal('modal-usuario');
    }

    function excluirUsuario(id, nome) {
        Cerbero.confirmModal({
            title: 'Excluir Usuário',
            message: `Tem certeza que deseja remover o usuário ${nome}? Esta ação é permanente e revogará todos os acessos.`,
            confirmText: 'Sim, Excluir',
            confirmColor: 'red',
            formAction: 'backend/usuario_excluir.php',
            hiddenInputs: { id: id }
        });
    }

    function validarFormUsuario(e) {
        const nome = document.getElementById('usuario_nome').value.trim();
        const email = document.getElementById('usuario_email').value.trim();
        const id = document.getElementById('usuario_id').value;
        const senha = document.getElementById('usuario_senha').value;

        if (nome.length < 3) {
            e.preventDefault();
            Cerbero.toast('erro', 'Nome Curto', 'O nome deve ter pelo menos 3 caracteres.');
            return false;
        }

        if (!id && senha.length < 6) {
            e.preventDefault();
            Cerbero.toast('erro', 'Senha Curta', 'Para novos usuários, a senha deve ter ao menos 6 dígitos.');
            return false;
        }

        return true;
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
