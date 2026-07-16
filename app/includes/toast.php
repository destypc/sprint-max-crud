<?php
/**
 * Toast flutuante reutilizável. O JS (painel.js) lê os data-attributes.
 * Aceita $flash ou $mensagem_flash, com chaves 'message'/'type' ou 'msg'/'tipo'.
 */
$flashToast = $flash ?? ($mensagem_flash ?? null);
$toastTexto = $flashToast['message'] ?? $flashToast['msg'] ?? '';
$toastTipo  = $flashToast['type'] ?? $flashToast['tipo'] ?? '';
$toastTipo  = in_array($toastTipo, ['success', 'sucesso'], true) ? 'success' : 'error';
?>
<div class="sp-toast" id="spToast" role="status" aria-live="polite"
    <?php if ($flashToast): ?> data-flash-msg="<?= htmlspecialchars($toastTexto) ?>" data-flash-type="<?= $toastTipo ?>" <?php endif; ?>>
    <i class="fa-solid fa-circle-check" id="toastIcon"></i>
    <span id="toastMsg"></span>
</div>
