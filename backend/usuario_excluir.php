<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Exclusão de Usuário
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

// Apenas Administradores
exigirNivelAcesso('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../usuarios.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definirToast('erro', 'ID Inválido', 'Nenhum usuário especificado para exclusão.');
    header('Location: ../usuarios.php');
    exit;
}

// Impede que o usuário logado exclua a si mesmo
if ($id === (int) $_SESSION['usuario_id']) {
    definirToast('erro', 'Ação Proibida', 'Você não pode excluir a sua própria conta conectada.');
    header('Location: ../usuarios.php');
    exit;
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Exclusão simulada com sucesso.');
    header('Location: ../usuarios.php');
    exit;
}

try {
    // Busca informações antes de excluir para o log
    $stmtFind = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = :id LIMIT 1");
    $stmtFind->execute([':id' => $id]);
    $usuario = $stmtFind->fetch();

    if (!$usuario) {
        definirToast('erro', 'Não Encontrado', 'Usuário não localizado no sistema.');
        header('Location: ../usuarios.php');
        exit;
    }

    $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmtDel->execute([':id' => $id]);

    registrarLog('USUARIO_EXCLUIDO', "Usuário #$id ({$usuario['nome']}) foi excluído.");
    definirToast('sucesso', 'Usuário Removido', "O usuário {$usuario['nome']} foi excluído com sucesso.");
    
    header('Location: ../usuarios.php');
    exit;
} catch (PDOException $e) {
    error_log("Erro ao excluir usuário: " . $e->getMessage());
    definirToast('erro', 'Erro ao Excluir', 'Não foi possível remover o usuário devido a dependências ou integridade referencial.');
    header('Location: ../usuarios.php');
    exit;
}
