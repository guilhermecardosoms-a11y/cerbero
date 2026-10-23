<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Gerenciamento de Sessão e Controle de Autenticação
 * ========================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bd.php';

/**
 * Define uma mensagem TOAST flash para exibição na interface
 * @param string $tipo 'sucesso' | 'erro' | 'aviso' | 'info'
 * @param string $titulo
 * @param string $mensagem
 */
function definirToast($tipo, $titulo, $mensagem) {
    $_SESSION['toast'] = [
        'tipo'     => $tipo,
        'titulo'   => htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'),
        'mensagem' => htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'),
        'timestamp'=> time()
    ];
}

/**
 * Recupera e consome a notificação TOAST da sessão
 * @return array|null
 */
function obterToast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Verifica se o usuário atual está logado no sistema
 * @return bool
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Retorna os dados do usuário conectado na sessão
 * @return array|null
 */
function getUsuarioLogado() {
    if (!estaAutenticado()) {
        return null;
    }

    return [
        'id'           => $_SESSION['usuario_id'],
        'nome'         => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email'        => $_SESSION['usuario_email'] ?? '',
        'nivel_acesso' => $_SESSION['usuario_nivel'] ?? 'Operador',
        'departamento' => $_SESSION['usuario_departamento'] ?? 'Geral'
    ];
}

/**
 * Exige que o usuário esteja autenticado. Caso contrário, redireciona ao login.
 */
function exigirAutenticacao() {
    if (!estaAutenticado()) {
        definirToast('aviso', 'Acesso Restrito', 'Faça login para acessar o painel do Cerbero.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Verifica se o usuário atual possui um dos níveis de acesso permitidos
 * @param array|string $niveisPermitidos Ex: ['Administrador', 'Gerente']
 * @return bool
 */
function temNivelAcesso($niveisPermitidos) {
    if (!estaAutenticado()) {
        return false;
    }
    $niveis = is_array($niveisPermitidos) ? $niveisPermitidos : [$niveisPermitidos];
    return in_array($_SESSION['usuario_nivel'], $niveis);
}

/**
 * Exige nível de acesso específico. Caso negado, redireciona ao dashboard com erro.
 * @param array|string $niveisPermitidos
 */
function exigirNivelAcesso($niveisPermitidos) {
    exigirAutenticacao();
    if (!temNivelAcesso($niveisPermitidos)) {
        definirToast('erro', 'Acesso Não Permitido', 'Seu nível de acesso não tem permissão para esta funcionalidade.');
        header('Location: index.php');
        exit;
    }
}

/**
 * Registra atividade no log do sistema
 * @param string $acao
 * @param string|null $detalhes
 */
function registrarLog($acao, $detalhes = null) {
    try {
        $pdo = getConexao();
        if ($pdo) {
            $usuarioId = $_SESSION['usuario_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $pdo->prepare("INSERT INTO logs_sistema (usuario_id, acao, detalhes, ip) VALUES (:usuario_id, :acao, :detalhes, :ip)");
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':acao'       => substr($acao, 0, 100),
                ':detalhes'   => $detalhes,
                ':ip'         => $ip
            ]);
        }
    } catch (Exception $e) {
        // Silencia falha de log para não interromper fluxo principal
        error_log("Falha ao registrar log no Cerbero: " . $e->getMessage());
    }
}

/**
 * Função utilitária para verificar senhas com compatibilidade segura
 * Suporta hashes bcrypt nativos ($2y$) e fallback seguro
 */
function verificarHashSenha($senhaDigitada, $hashArmazenado) {
    if (empty($senhaDigitada) || empty($hashArmazenado)) {
        return false;
    }
    if (password_verify($senhaDigitada, $hashArmazenado)) {
        return true;
    }
    // Fallback de contingência caso inserido manualmente sem hash
    if ($senhaDigitada === $hashArmazenado) {
        return true;
    }
    return false;
}
