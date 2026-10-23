<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Tela de Autenticação / Login
 * ========================================================================
 */
require_once __DIR__ . '/config/auth.php';

// Se já estiver logado, redireciona ao painel principal
if (estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Login de Acesso';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $pageTitle ?> | Cerbero</title>
    
    <!-- Tailwind CSS CDN oficial -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Estilos Customizados Cerbero -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="h-full antialiased flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-slate-100">

    <!-- Container Flutuante de Toasts -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col space-y-3 pointer-events-none w-full max-w-sm px-4 md:px-0"></div>

    <div class="w-full max-w-md">
        
        <!-- Logotipo e Cabeçalho Cerbero -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-2xl shadow-emerald-500/30 mb-4 transform hover:scale-105 transition-transform">
                <i class="fa-solid fa-shield-halved text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-wider bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">CERBERO</h1>
            <p class="text-xs uppercase tracking-widest text-emerald-400 font-semibold mt-1">Sistema de Gerenciamento & Administração</p>
        </div>

        <!-- Card de Autenticação -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">Acesse sua conta</h2>
                <p class="text-xs text-slate-400 mt-1">Informe suas credenciais para acessar o painel</p>
            </div>

            <form action="backend/login_process.php" method="POST" class="space-y-5" onsubmit="return validarLogin(event)">
                
                <!-- Campo E-mail -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">E-mail Institucional</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </div>
                        <input type="email" id="email" name="email" required placeholder="admin@cerbero.com"
                            class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Campo Senha -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="senha" class="block text-xs font-bold uppercase tracking-wider text-slate-300">Senha de Acesso</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="senha" name="senha" required placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <button type="button" onclick="toggleSenha()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition-colors">
                            <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Botão de Conectar -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-emerald-900/40 hover:shadow-emerald-900/60 active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Entrar no Sistema</span>
                </button>
            </form>

            <!-- Acesso Rápido para Avaliação / Demonstração -->
            <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Credenciais de Teste:</p>
                <div class="flex flex-wrap gap-2 justify-center">
                    <button type="button" onclick="preencherCredenciais('admin@cerbero.com', 'admin123')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 text-xs font-mono transition-colors">
                        admin@cerbero.com
                    </button>
                    <button type="button" onclick="preencherCredenciais('carlos@cerbero.com', 'carlos123')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 border border-slate-700 text-xs font-mono transition-colors">
                        carlos@cerbero.com
                    </button>
                    <button type="button" onclick="preencherCredenciais('ana@cerbero.com', 'ana123')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-teal-400 border border-slate-700 text-xs font-mono transition-colors">
                        ana@cerbero.com
                    </button>
                </div>
            </div>

            <!-- Link de Solicitação de Acesso -->
            <div class="mt-5 text-center">
                <p class="text-xs text-slate-400">
                    Ainda não tem acesso? 
                    <a href="solicitar_acesso.php" class="font-bold text-emerald-400 hover:text-emerald-300 hover:underline transition-colors">
                        Solicitar Acesso aqui
                    </a>
                </p>
            </div>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Cerbero &copy; <?= date('Y') ?> &bull; Controle de Usuários e Registros
        </p>

    </div>

    <!-- Framework Cerbero JS -->
    <script src="assets/js/cerbero.js"></script>
    
    <!-- Disparo de Toasts -->
    <?php require_once __DIR__ . '/includes/toast.php'; ?>

    <script>
        function toggleSenha() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function preencherCredenciais(email, senha) {
            document.getElementById('email').value = email;
            document.getElementById('senha').value = senha;
            Cerbero.toast('info', 'Preenchido!', `Credencial de ${email} aplicada.`);
        }

        function validarLogin(e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value.trim();
            if (!email || !senha) {
                e.preventDefault();
                Cerbero.toast('erro', 'Campos Vazios', 'Por favor, preencha todos os campos.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
