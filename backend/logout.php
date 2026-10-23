<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Encerramento Seguro de Sessão (Logout)
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

if (estaAutenticado()) {
    registrarLog('LOGOUT', 'Usuário encerrou a sessão com segurança.');
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Invalida o cookie da sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Inicia nova sessão limpa apenas para exibir o Toast de despedida
session_start();
definirToast('info', 'Sessão Encerrada', 'Você saiu com segurança do Sistema Cerbero.');

header('Location: ../login.php');
exit;
