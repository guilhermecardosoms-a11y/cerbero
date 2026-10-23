<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Ação em Solicitação de Acesso (Aprovação / Rejeição)
 * ========================================================================
 */

require_once __DIR__ . '/../config/auth.php';

// Apenas Administradores e Gerentes podem avaliar solicitações
exigirNivelAcesso(['Administrador', 'Gerente']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../solicitacoes.php');
    exit;
}

$id         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$acao       = trim($_POST['acao'] ?? '');
$observacao = trim(filter_input(INPUT_POST, 'observacao', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

if (!$id || !in_array($acao, ['aprovar', 'rejeitar'])) {
    definirToast('erro', 'Ação Inválida', 'Parâmetros de solicitação incorretos.');
    header('Location: ../solicitacoes.php');
    exit;
}

$pdo = getConexao();

if (!$pdo) {
    definirToast('aviso', 'Modo Demonstração', 'Ação simulada com sucesso no modo local.');
    header('Location: ../solicitacoes.php');
    exit;
}

try {
    // Busca a solicitação
    $stmt = $pdo->prepare("SELECT * FROM solicitacoes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $solicitacao = $stmt->fetch();

    if (!$solicitacao) {
        definirToast('erro', 'Não Encontrada', 'Solicitação de acesso não localizada no sistema.');
        header('Location: ../solicitacoes.php');
        exit;
    }

    if ($solicitacao['status'] !== 'Pendente') {
        definirToast('aviso', 'Já Avaliada', 'Esta solicitação já foi ' . strtolower($solicitacao['status']) . ' anteriormente.');
        header('Location: ../solicitacoes.php');
        exit;
    }

    $usuarioLogado = getUsuarioLogado();
    $adminId = $usuarioLogado['id'];

    if ($acao === 'aprovar') {
        // Transação para consistência
        $pdo->beginTransaction();

        // 1. Atualiza status da solicitação
        $stmtUp = $pdo->prepare("
            UPDATE solicitacoes 
            SET status = 'Aprovada', 
                analisado_por = :admin_id, 
                analisado_em = NOW(), 
                resposta_observacao = :obs 
            WHERE id = :id
        ");
        $stmtUp->execute([
            ':admin_id' => $adminId,
            ':obs'      => $observacao ?: 'Solicitação de acesso aprovada pela administração.',
            ':id'       => $id
        ]);

        // 2. Cria o usuário ativo no sistema se ainda não existir
        $checkUser = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $checkUser->execute([':email' => $solicitacao['email']]);
        
        if (!$checkUser->fetch()) {
            $senhaPadrao = password_hash('cerbero123', PASSWORD_BCRYPT);
            $stmtUser = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, nivel_acesso, status, departamento, criado_em)
                VALUES (:nome, :email, :senha, :nivel, 'Ativo', :departamento, NOW())
            ");
            $stmtUser->execute([
                ':nome'         => $solicitacao['nome'],
                ':email'        => $solicitacao['email'],
                ':senha'        => $senhaPadrao,
                ':nivel'        => $solicitacao['nivel_desejado'],
                ':departamento' => $solicitacao['departamento']
            ]);
        }

        $pdo->commit();

        registrarLog('SOLICITACAO_APROVADA', "Solicitação #$id de {$solicitacao['nome']} foi aprovada.");
        definirToast('sucesso', 'Acesso Aprovado!', "A solicitação de {$solicitacao['nome']} foi aprovada e a conta de usuário foi criada.");
    } else {
        // Rejeitar solicitação
        $stmtUp = $pdo->prepare("
            UPDATE solicitacoes 
            SET status = 'Rejeitada', 
                analisado_por = :admin_id, 
                analisado_em = NOW(), 
                resposta_observacao = :obs 
            WHERE id = :id
        ");
        $stmtUp->execute([
            ':admin_id' => $adminId,
            ':obs'      => $observacao ?: 'Solicitação rejeitada pela administração.',
            ':id'       => $id
        ]);

        registrarLog('SOLICITACAO_REJEITADA', "Solicitação #$id de {$solicitacao['nome']} foi rejeitada.");
        definirToast('info', 'Solicitação Rejeitada', "A solicitação de {$solicitacao['nome']} foi rejeitada.");
    }

    // Redireciona para de onde veio ou para a tela de solicitações
    $origem = $_POST['origem'] ?? 'solicitacoes.php';
    header('Location: ../' . (in_array($origem, ['index.php', 'solicitacoes.php']) ? $origem : 'solicitacoes.php'));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao processar acao de solicitacao: " . $e->getMessage());
    definirToast('erro', 'Erro Interno', 'Falha ao processar solicitação no banco de dados.');
    header('Location: ../solicitacoes.php');
    exit;
}
