<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Cabeçalho HTML / Header
 * ========================================================================
 */
require_once __DIR__ . '/../config/auth.php';

$usuarioLogado = getUsuarioLogado();
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Cerbero' : 'Cerbero - Painel Administrativo' ?></title>
    
    <!-- Meta Tags para Segurança e SEO -->
    <meta name="description" content="Sistema de Gerenciamento e Administração Cerbero">
    <meta name="theme-color" content="#0f172a">

    <!-- Tailwind CSS (CDN oficial) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cerbero: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#15803d',
                            600: '#166534',
                            800: '#14532d',
                            900: '#052e16',
                            dark: '#090d16',
                            surface: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 (Ícones Modernos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Estilos Customizados Cerbero -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="h-full text-slate-800 antialiased flex flex-col md:flex-row bg-slate-100">

    <!-- Container Flutuante de Notificações Toast -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col space-y-3 pointer-events-none w-full max-w-sm px-4 md:px-0"></div>
