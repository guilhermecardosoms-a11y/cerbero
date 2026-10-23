<?php
/**
 * ========================================================================
 * SISTEMA CERBERO - Renderizador de Notificações Toast
 * ========================================================================
 * Captura notificações flash da sessão e aciona o framework Cerbero.toast()
 */
require_once __DIR__ . '/../config/auth.php';

$toast = obterToast();

// Suporte opcional a parâmetros GET para facilidade de teste
if (!$toast && isset($_GET['msg']) && isset($_GET['tipo'])) {
    $toast = [
        'tipo'     => $_GET['tipo'],
        'titulo'   => $_GET['titulo'] ?? 'Aviso do Sistema',
        'mensagem' => $_GET['msg']
    ];
}

if ($toast):
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Cerbero && typeof window.Cerbero.toast === 'function') {
            window.Cerbero.toast(
                <?= json_encode($toast['tipo'] ?? 'info') ?>,
                <?= json_encode($toast['titulo'] ?? 'Notificação') ?>,
                <?= json_encode($toast['mensagem'] ?? '') ?>
            );
        }
    });
</script>
<?php endif; ?>
