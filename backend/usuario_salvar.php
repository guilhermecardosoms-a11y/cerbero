<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Criação e Edição de Usuários
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

// Apenas Administradores podem gerenciar usuários
exigirNivelAcesso('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../usuarios.php');
    exit;
}

$id           = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome         = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$email        = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha        = trim($_POST['senha'] ?? '');
$nivel_acesso = trim($_POST['nivel_acesso'] ?? 'Operador');
$status       = trim($_POST['status'] ?? 'Ativo');
$departamento = trim(filter_input(INPUT_POST, 'departamento', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Geral');

// Validações
if (mb_strlen($nome) < 3) {
    definirToast('erro', 'Nome Inválido', 'O nome deve possuir ao menos 3 caracteres.');
    header('Location: ../usuarios.php');
    exit;
}

if (!$email) {
    definirToast('erro', 'Email Inválido', 'Informe um endereço de e-mail válido.');
    header('Location: ../usuarios.php');
    exit;
}

if (!in_array($nivel_acesso, ['Administrador', 'Gerente', 'Operador'])) {
    $nivel_acesso = 'Operador';
}

if (!in_array($status, ['Ativo', 'Inativo'])) {
    $status = 'Ativo';
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Operação simulada com sucesso (banco offline).');
    header('Location: ../usuarios.php');
    exit;
}

try {
    // 1. Checa duplicidade de e-mail
    if ($id) {
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
        $stmtCheck->execute([':email' => $email, ':id' => $id]);
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmtCheck->execute([':email' => $email]);
    }

    if ($stmtCheck->fetch()) {
        definirToast('erro', 'Email Duplicado', 'Já existe outro usuário cadastrado com este e-mail.');
        header('Location: ../usuarios.php');
        exit;
    }

    if ($id) {
        // Atualização de Usuário Existente
        if (!empty($senha)) {
            if (mb_strlen($senha) < 6) {
                definirToast('erro', 'Senha Curta', 'A nova senha deve possuir ao menos 6 caracteres.');
                header('Location: ../usuarios.php');
                exit;
            }
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nome = :nome, email = :email, senha = :senha, nivel_acesso = :nivel, status = :status, departamento = :dep 
                WHERE id = :id
            ");
            $stmt->execute([
                ':nome'   => $nome,
                ':email'  => $email,
                ':senha'  => $hash,
                ':nivel'  => $nivel_acesso,
                ':status' => $status,
                ':dep'    => $departamento,
                ':id'     => $id
            ]);
        } else {
            // Mantém a senha atual
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nome = :nome, email = :email, nivel_acesso = :nivel, status = :status, departamento = :dep 
                WHERE id = :id
            ");
            $stmt->execute([
                ':nome'   => $nome,
                ':email'  => $email,
                ':nivel'  => $nivel_acesso,
                ':status' => $status,
                ':dep'    => $departamento,
                ':id'     => $id
            ]);
        }

        registrarLog('USUARIO_ATUALIZADO', "Usuário #$id ($nome) atualizado.");
        definirToast('sucesso', 'Usuário Atualizado', "Os dados de $nome foram salvos com sucesso.");
    } else {
        // Criação de Novo Usuário
        if (mb_strlen($senha) < 6) {
            definirToast('erro', 'Senha Obrigatória', 'Para novo usuário, a senha deve conter pelo menos 6 caracteres.');
            header('Location: ../usuarios.php');
            exit;
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, status, departamento, criado_em)
            VALUES (:nome, :email, :senha, :nivel, :status, :dep, NOW())
        ");
        $stmt->execute([
            ':nome'   => $nome,
            ':email'  => $email,
            ':senha'  => $hash,
            ':nivel'  => $nivel_acesso,
            ':status' => $status,
            ':dep'    => $departamento
        ]);

        $novoId = $pdo->lastInsertId();
        registrarLog('USUARIO_CRIADO', "Novo usuário #$novoId ($nome) criado.");
        definirToast('sucesso', 'Usuário Criado', "O usuário $nome foi cadastrado com sucesso.");
    }

    header('Location: ../usuarios.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao salvar usuário: " . $e->getMessage());
    definirToast('erro', 'Erro no Banco', 'Falha ao salvar dados do usuário.');
    header('Location: ../usuarios.php');
    exit;
}
