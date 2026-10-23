<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Salvar Registro de Cadastro (Criar / Editar)
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

// Todos os usuários autenticados podem cadastrar ou editar registros
exigirAutenticacao();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registros.php');
    exit;
}

$id         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$titulo     = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$categoria  = trim(filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$descricao  = trim(filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$status     = trim($_POST['status'] ?? 'Ativo');
$prioridade = trim($_POST['prioridade'] ?? 'Média');

if (mb_strlen($titulo) < 3) {
    definirToast('erro', 'Título Inválido', 'O título do registro deve conter no mínimo 3 caracteres.');
    header('Location: ../registros.php');
    exit;
}

if (empty($categoria)) {
    definirToast('erro', 'Categoria Obrigatória', 'Informe uma categoria válida para o registro.');
    header('Location: ../registros.php');
    exit;
}

if (!in_array($status, ['Ativo', 'Pendente', 'Concluído', 'Arquivado'])) {
    $status = 'Ativo';
}

if (!in_array($prioridade, ['Baixa', 'Média', 'Alta', 'Crítica'])) {
    $prioridade = 'Média';
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Registro processado em modo local.');
    header('Location: ../registros.php');
    exit;
}

try {
    $usuarioId = $_SESSION['usuario_id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE registros 
            SET titulo = :titulo, categoria = :categoria, descricao = :descricao, status = :status, prioridade = :prioridade
            WHERE id = :id
        ");
        $stmt->execute([
            ':titulo'     => $titulo,
            ':categoria'  => $categoria,
            ':descricao'  => $descricao,
            ':status'     => $status,
            ':prioridade' => $prioridade,
            ':id'         => $id
        ]);

        registrarLog('REGISTRO_ATUALIZADO', "Registro #$id ($titulo) atualizado.");
        definirToast('sucesso', 'Registro Atualizado', "O registro \"$titulo\" foi salvo com sucesso.");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO registros (titulo, categoria, descricao, status, prioridade, criado_por, criado_em)
            VALUES (:titulo, :categoria, :descricao, :status, :prioridade, :criado_por, NOW())
        ");
        $stmt->execute([
            ':titulo'     => $titulo,
            ':categoria'  => $categoria,
            ':descricao'  => $descricao,
            ':status'     => $status,
            ':prioridade' => $prioridade,
            ':criado_por' => $usuarioId
        ]);

        $novoId = $pdo->lastInsertId();
        registrarLog('REGISTRO_CRIADO', "Novo registro #$novoId ($titulo) cadastrado.");
        definirToast('sucesso', 'Registro Criado', "O registro \"$titulo\" foi incluído com sucesso no sistema.");
    }

    header('Location: ../registros.php');
    exit;

} catch (PDOException $e) {
    error_log("Erro ao salvar registro: " . $e->getMessage());
    definirToast('erro', 'Erro no Banco', 'Falha ao processar o registro no banco de dados.');
    header('Location: ../registros.php');
    exit;
}
