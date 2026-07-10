<?php
?>

<div class="modal-backdrop" id="profileModalBackdrop" onclick="handleProfileModalClick(event)">
    <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Editar perfil">

        <div class="modal-head">
            <h3><i class="fa-regular fa-user" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Perfil</h3>
            <button class="btn-close-modal" onclick="closeProfileModal()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="/app/controller/perfilController.php">
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" name="redirect"
                value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>">

            <div class="modal-body">

                <div class="form-group">
                    <label class="form-label" for="profileNome">Nome completo</label>
                    <input type="text" id="profileNome" name="nome" class="form-input"
                        value="<?= htmlspecialchars($usuario_logado['nome'] ?? '') ?>"
                        placeholder="Seu nome" maxlength="120" required>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-input"
                        value="<?= htmlspecialchars($usuario_logado['email'] ?? '') ?>"
                        disabled>
                </div>

                <hr style="border:none;border-top:1px solid var(--border);margin:4px 0">

                <div class="form-group">
                    <label class="form-label" for="profileSenhaAtual">
                        Senha atual
                        <span style="color:var(--text-dim);font-weight:400;text-transform:none">(apenas ao trocar a senha)</span>
                    </label>
                    <input type="password" id="profileSenhaAtual" name="senha_atual"
                        class="form-input" placeholder="••••••••" autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="profileSenha">
                        Nova senha
                        <span style="color:var(--text-dim);font-weight:400;text-transform:none">(deixe em branco para manter)</span>
                    </label>
                    <input type="password" id="profileSenha" name="nova_senha" class="form-input"
                        placeholder="••••••••" minlength="6" autocomplete="new-password">
                    <span class="form-hint">Mínimo 6 caracteres.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="profileConfirmSenha">Confirmar nova senha</label>
                    <input type="password" id="profileConfirmSenha" name="confirmar_senha"
                        class="form-input" placeholder="••••••••" minlength="6" autocomplete="new-password">
                </div>

            </div>

            <div class="modal-foot">
                <button type="button" class="btn-ghost" onclick="closeProfileModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Salvar alterações
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openProfileModal() {
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
</script>

<script src="/assets/js/theme.js"></script>