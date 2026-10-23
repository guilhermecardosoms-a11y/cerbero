<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Exclusão de Registro
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

// Apenas Administradores e Gerentes podem excluir registros
exigirNivelAcesso(['Administrador', 'Gerente']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registros.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    definirToast('erro', 'ID Inválido', 'Nenhum registro selecionado para exclusão.');
    header('Location: ../registros.php');
    exit;
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Exclusão simulada com sucesso.');
    header('Location: ../registros.php');
    exit;
}

try {
    $stmtFind = $pdo->prepare("SELECT titulo FROM registros WHERE id = :id LIMIT 1");
    $stmtFind->execute([':id' => $id]);
    $registro = $stmtFind->fetch();

    if (!$registro) {
        definirToast('erro', 'Não Encontrado', 'O registro solicitado não existe.');
        header('Location: ../registros.php');
        exit;
    }

    $stmtDel = $pdo->prepare("DELETE FROM registros WHERE id = :id");
    $stmtDel->execute([':id' => $id]);

    registrarLog('REGISTRO_EXCLUIDO', "Registro #$id ({$registro['titulo']}) foi excluído.");
    definirToast('sucesso', 'Registro Excluído', "O registro \"{$registro['titulo']}\" foi removido com sucesso.");

    header('Location: ../registros.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao excluir registro: " . $e->getMessage());
    definirToast('erro', 'Erro no Banco', 'Falha ao excluir o registro.');
    header('Location: ../registros.php');
    exit;
}
