<?php
?>

<div class="fundo-modal" id="profileModalBackdrop" onclick="handleProfileModalClick(event)">
    <div class="modal modal-rolavel" role="dialog" aria-modal="true" aria-label="Editar perfil">

        <div class="cabecalho-modal">
            <h3><i class="fa-regular fa-user" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Perfil</h3>
            <button class="botao-fechar-modal" onclick="closeProfileModal()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="/app/controller/perfilController.php">
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" name="redirect"
                value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>">

            <div class="corpo-modal">

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="profileNome">Nome completo</label>
                    <input type="text" id="profileNome" name="nome" class="entrada-formulario"
                        value="<?= htmlspecialchars($usuario_logado['nome'] ?? '') ?>"
                        placeholder="Seu nome" maxlength="120" required>
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario">E-mail</label>
                    <input type="email" class="entrada-formulario"
                        value="<?= htmlspecialchars($usuario_logado['email'] ?? '') ?>"
                        disabled>
                </div>

                <hr style="border:none;border-top:1px solid var(--border);margin:4px 0">

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="profileSenhaAtual">
                        Senha atual
                        <span style="color:var(--text-dim);font-weight:400;text-transform:none">(apenas ao trocar a senha)</span>
                    </label>
                    <input type="password" id="profileSenhaAtual" name="senha_atual"
                        class="entrada-formulario" placeholder="••••••••" autocomplete="current-password">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="profileSenha">
                        Nova senha
                        <span style="color:var(--text-dim);font-weight:400;text-transform:none">(deixe em branco para manter)</span>
                    </label>
                    <input type="password" id="profileSenha" name="nova_senha" class="entrada-formulario"
                        placeholder="••••••••" minlength="6" autocomplete="new-password">
                    <span class="form-hint">Mínimo 6 caracteres.</span>
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="profileConfirmSenha">Confirmar nova senha</label>
                    <input type="password" id="profileConfirmSenha" name="confirmar_senha"
                        class="entrada-formulario" placeholder="••••••••" minlength="6" autocomplete="new-password">
                </div>

            </div>

            <div class="rodape-modal">
                <button type="button" class="botao-secundario" onclick="closeProfileModal()">Cancelar</button>
                <button type="submit" class="botao-primario">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Salvar alterações
                </button>
            </div>
        </form>

    </div>
</div>

<script src="/assets/js/theme.js"></script>
<script src="/assets/js/painel.js"></script>
