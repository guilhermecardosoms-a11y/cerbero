<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Formulário Público de Solicitação de Acesso
 * ========================================================================
 */
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Solicitar Acesso ao Sistema';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?> | Cerbero</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Estilos Customizados Cerbero -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="min-h-full antialiased flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-slate-100">

    <!-- Container Flutuante de Toasts -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col space-y-3 pointer-events-none w-full max-w-sm px-4 md:px-0"></div>

    <div class="w-full max-w-lg py-8">
        
        <!-- Logotipo Cerbero -->
        <div class="text-center mb-6">
            <a href="login.php" class="inline-flex items-center space-x-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-shield-halved text-white text-xl"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-2xl font-extrabold tracking-wider text-white">CERBERO</h1>
                    <p class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold">Solicitação de Acesso</p>
                </div>
            </a>
        </div>

        <!-- Card do Formulário -->
        <div class="bg-slate-900/85 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">Pedir Credenciais de Acesso</h2>
                <p class="text-xs text-slate-400 mt-1">
                    Preencha os dados abaixo. Sua requisição será avaliada pela equipe administrativa de segurança.
                </p>
            </div>

            <form action="backend/solicitar_process.php" method="POST" class="space-y-4" onsubmit="return validarSolicitacao(event)">
                
                <!-- Nome Completo -->
                <div>
                    <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nome Completo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <input type="text" id="nome" name="nome" required placeholder="Ex: Roberto Fernandes"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- E-mail Institucional -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">E-mail Corporativo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </div>
                        <input type="email" id="email" name="email" required placeholder="colaborador@empresa.com"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Departamento -->
                    <div>
                        <label for="departamento" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Departamento</label>
                        <select id="departamento" name="departamento" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            <option value="">Selecione...</option>
                            <option value="Tecnologia da Informação">TI / Infraestrutura</option>
                            <option value="Segurança da Informação">Segurança & SecOps</option>
                            <option value="Compliance & Auditoria">Compliance</option>
                            <option value="Operações & Logística">Operações</option>
                            <option value="Atendimento ao Cliente">Atendimento / Suporte</option>
                            <option value="Recursos Humanos">Recursos Humanos</option>
                        </select>
                    </div>

                    <!-- Nível Desejado -->
                    <div>
                        <label for="nivel_desejado" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Nível Desejado</label>
                        <select id="nivel_desejado" name="nivel_desejado" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            <option value="Operador" selected>Operador (Padrão)</option>
                            <option value="Gerente">Gerente</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                </div>

                <!-- Justificativa / Motivo -->
                <div>
                    <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Justificativa / Motivo do Acesso</label>
                    <textarea id="motivo" name="motivo" rows="3" required placeholder="Explique brevemente por que você necessita de acesso a este sistema..."
                        class="w-full p-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>

                <!-- Botão de Envio -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-emerald-900/40 hover:shadow-emerald-900/60 active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Enviar Solicitação de Acesso</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                <a href="login.php" class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Voltar para o Login</span>
                </a>
            </div>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Cerbero &copy; <?= date('Y') ?> &bull; Sistema Seguro de Gestão
        </p>

    </div>

    <!-- Framework Cerbero JS -->
    <script src="assets/js/cerbero.js"></script>

    <!-- Disparo de Toasts -->
    <?php require_once __DIR__ . '/includes/toast.php'; ?>

    <script>
        function validarSolicitacao(e) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const motivo = document.getElementById('motivo').value.trim();
            const departamento = document.getElementById('departamento').value;

            if (nome.length < 3) {
                e.preventDefault();
                Cerbero.toast('erro', 'Nome Curto', 'O nome deve conter ao menos 3 caracteres.');
                return false;
            }

            if (!departamento) {
                e.preventDefault();
                Cerbero.toast('erro', 'Departamento', 'Selecione o seu departamento.');
                return false;
            }

            if (motivo.length < 10) {
                e.preventDefault();
                Cerbero.toast('erro', 'Motivo Insuficiente', 'Por favor forneça uma justificativa com pelo menos 10 caracteres.');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
