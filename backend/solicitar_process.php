<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Processamento de Nova Solicitação de Acesso
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../solicitar_acesso.php');
    exit;
}

$nome           = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$email          = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$departamento   = trim(filter_input(INPUT_POST, 'departamento', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$nivel_desejado = trim($_POST['nivel_desejado'] ?? 'Operador');
$motivo         = trim(filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

// Validações rigorosas no backend
if (mb_strlen($nome) < 3) {
    definirToast('erro', 'Nome Inválido', 'O nome completo deve conter no mínimo 3 caracteres.');
    header('Location: ../solicitar_acesso.php');
    exit;
}

if (!$email) {
    definirToast('erro', 'Email Inválido', 'Por favor, forneça um endereço de e-mail institucional válido.');
    header('Location: ../solicitar_acesso.php');
    exit;
}

if (empty($departamento)) {
    definirToast('erro', 'Departamento Obrigatório', 'Selecione ou informe o seu departamento.');
    header('Location: ../solicitar_acesso.php');
    exit;
}

if (!in_array($nivel_desejado, ['Administrador', 'Gerente', 'Operador'])) {
    $nivel_desejado = 'Operador';
}

if (mb_strlen($motivo) < 10) {
    definirToast('erro', 'Justificativa Insuficiente', 'Por favor, descreva detalhadamente o motivo da solicitação (mínimo 10 caracteres).');
    header('Location: ../solicitar_acesso.php');
    exit;
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Solicitação simulada com sucesso! (Banco de dados offline)');
    header('Location: ../login.php');
    exit;
}

try {
    // 1. Verifica se já existe usuário cadastrado com este e-mail
    $stmtCheckUser = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmtCheckUser->execute([':email' => $email]);
    if ($stmtCheckUser->fetch()) {
        definirToast('aviso', 'Email já Cadastrado', 'Já existe um usuário ativo cadastrado com este e-mail. Utilize a tela de login.');
        header('Location: ../solicitar_acesso.php');
        exit;
    }

    // 2. Verifica se já existe solicitação pendente para este e-mail
    $stmtCheckReq = $pdo->prepare("SELECT id FROM solicitacoes WHERE email = :email AND status = 'Pendente' LIMIT 1");
    $stmtCheckReq->execute([':email' => $email]);
    if ($stmtCheckReq->fetch()) {
        definirToast('info', 'Solicitação já em Análise', 'Já existe um pedido de acesso pendente para este e-mail. Aguarde a avaliação dos administradores.');
        header('Location: ../solicitar_acesso.php');
        exit;
    }

    // 3. Insere a nova solicitação de acesso
    $stmtInsert = $pdo->prepare("
        INSERT INTO solicitacoes (nome, email, motivo, departamento, nivel_desejado, status, criado_em)
        VALUES (:nome, :email, :motivo, :departamento, :nivel_desejado, 'Pendente', NOW())
    ");
    
    $stmtInsert->execute([
        ':nome'           => $nome,
        ':email'          => $email,
        ':motivo'         => $motivo,
        ':departamento'   => $departamento,
        ':nivel_desejado' => $nivel_desejado
    ]);

    definirToast('sucesso', 'Solicitação Enviada', 'Seu pedido de acesso foi registrado com sucesso e será analisado pela equipe de segurança.');
    header('Location: ../login.php');
    exit;
} catch (PDOException $e) {
    error_log("Erro ao salvar solicitacao: " . $e->getMessage());
    definirToast('erro', 'Erro no Envio', 'Houve um erro interno ao processar a solicitação. Tente novamente mais tarde.');
    header('Location: ../solicitar_acesso.php');
    exit;
}
