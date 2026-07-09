<?php

/**
 * Modal Editar Perfil — Sprint Max
 * Inclua este arquivo antes de </body> em qualquer página que use o header.php.
 * Requer $usuario_logado definido pelo controller da página.
 */
?>

<!-- ═══════════════════════════════════════════════════════════
 MODAL — Editar Perfil
 ═══════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="profileModalBackdrop" onclick="handleProfileModalClick(event)">
    <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Editar perfil">

        <div class="modal-head">
            <h3><i class="fa-regular fa-user" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Perfil</h3>
            <button class="btn-close-modal" onclick="closeProfileModal()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">

            <div class="form-group">
                <label class="form-label" for="profileNome">Nome completo</label>
                <input type="text" id="profileNome" class="form-input"
                    value="<?= htmlspecialchars($usuario_logado['nome'] ?? '') ?>"
                    placeholder="Seu nome" maxlength="120">
            </div>

            <div class="form-group">
                <label class="form-label" for="profileEmail">E-mail</label>
                <input type="email" id="profileEmail" class="form-input"
                    value="<?= htmlspecialchars($usuario_logado['email'] ?? '') ?>"
                    placeholder="seu@email.com" maxlength="180">
            </div>

            <div class="form-group">
                <label class="form-label" for="profileSenha">
                    Nova senha
                    <span style="color:var(--text-dim);font-weight:400;text-transform:none">(deixe em branco para manter)</span>
                </label>
                <input type="password" id="profileSenha" class="form-input"
                    placeholder="••••••••" minlength="6" autocomplete="new-password">
                <span class="form-hint">Mínimo 6 caracteres.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="profileConfirmSenha">Confirmar nova senha</label>
                <input type="password" id="profileConfirmSenha" class="form-input"
                    placeholder="••••••••" minlength="6" autocomplete="new-password">
            </div>

        </div>

        <div class="modal-foot">
            <button type="button" class="btn-ghost" onclick="closeProfileModal()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="closeProfileModal()">
                <i class="fa-solid fa-floppy-disk"></i>
                Salvar alterações
            </button>
        </div>

    </div>
</div>

<script>
    function openProfileModal() {
        /* Fecha o dropdown de perfil se estiver aberto */
        const pd = document.getElementById('profileDropdown');
        const pb = document.getElementById('profileBtn');
        if (pd) pd.classList.remove('open');
        if (pb) {
            pb.classList.remove('open');
            pb.setAttribute('aria-expanded', false);
        }

     
        document.getElementById('profileModalBackdrop').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeProfileModal() {
        document.getElementById('profileModalBackdrop').classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleProfileModalClick(e) {
        if (e.target === document.getElementById('profileModalBackdrop')) {
            closeProfileModal();
        }
    }

    function previewProfilePhoto(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileAvatarImg').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>