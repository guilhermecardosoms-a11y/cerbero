<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Processamento de Login
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');

// Validações do Backend
if (!$email) {
    definirToast('erro', 'Email Inválido', 'Por favor, informe um endereço de e-mail válido.');
    header('Location: ../login.php');
    exit;
}

if (empty($senha)) {
    definirToast('erro', 'Senha Obrigatória', 'Por favor, digite sua senha de acesso.');
    header('Location: ../login.php');
    exit;
}

$pdo = getConexao();

if (!$pdo) {
    // Modo de demonstração / fallback caso o MySQL local não esteja rodando
    // Permite login como administrador para avaliação das interfaces e requisitos
    if ($email === 'admin@cerbero.com' && $senha === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['usuario_id']           = 1;
        $_SESSION['usuario_nome']         = 'Administrador Cerbero';
        $_SESSION['usuario_email']        = 'admin@cerbero.com';
        $_SESSION['usuario_nivel']        = 'Administrador';
        $_SESSION['usuario_departamento'] = 'Segurança da Informação';
        
        definirToast('sucesso', 'Autenticação Bem-Sucedida', 'Bem-vindo de volta, Administrador Cerbero!');
        header('Location: ../index.php');
        exit;
    }

    definirToast('erro', 'Falha de Conexão', 'Não foi possível conectar ao banco de dados MySQL. Verifique as credenciais no bd.php.');
    header('Location: ../login.php');
    exit;
}

try {
    // Consulta preparada contra SQL Injection
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel_acesso, status, departamento FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && verificarHashSenha($senha, $usuario['senha'])) {
        // Verifica se a conta está ativa
        if ($usuario['status'] !== 'Ativo') {
            definirToast('aviso', 'Conta Inativa', 'Sua conta de acesso está inativa. Contate o administrador do sistema.');
            registrarLog('LOGIN_BLOQUEADO_INATIVO', "Tentativa em conta inativa: $email");
            header('Location: ../login.php');
            exit;
        }

        // Regeneração de ID da sessão para proteção contra fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id']           = (int) $usuario['id'];
        $_SESSION['usuario_nome']         = $usuario['nome'];
        $_SESSION['usuario_email']        = $usuario['email'];
        $_SESSION['usuario_nivel']        = $usuario['nivel_acesso'];
        $_SESSION['usuario_departamento'] = $usuario['departamento'] ?? 'Tecnologia';

        // Atualiza carimbo de último login
        $updateStmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $usuario['id']]);

        registrarLog('LOGIN_SUCESSO', "Usuário {$usuario['nome']} conectou-se com sucesso");
        definirToast('sucesso', 'Bem-vindo(a)!', "Olá, {$usuario['nome']}! Sessão iniciada com sucesso.");
        
        header('Location: ../index.php');
        exit;
    } else {
        registrarLog('LOGIN_FALHA', "Tentativa falha com o e-mail: $email");
        definirToast('erro', 'Acesso Negado', 'Email ou senha incorretos. Verifique suas credenciais.');
        header('Location: ../login.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro no login Cerbero: " . $e->getMessage());
    definirToast('erro', 'Erro Interno', 'Ocorreu uma falha no processamento do login. Tente novamente.');
    header('Location: ../login.php');
    exit;
}
