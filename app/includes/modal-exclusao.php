<?php
/**
 * Modal de confirmação de exclusão — reutilizável.
 * Controlado pelas funções globais de painel.js:
 * abrirModalExclusao(), fecharModalExclusao() e confirmarExclusao().
 * Basta incluir este arquivo na página e chamar abrirModalExclusao({...}).
 */
?>
<div class="fundo-modal" id="modalExclusaoBackdrop" onclick="if(event.target===this)fecharModalExclusao()">
    <div class="modal" style="max-width:400px" role="dialog" aria-modal="true" aria-label="Confirmar exclusão">

        <div class="cabecalho-modal" style="border-bottom:none;padding-bottom:8px">
            <div style="flex:1"></div>
            <button class="botao-fechar-modal" onclick="fecharModalExclusao()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="corpo-modal" style="align-items:center;text-align:center;gap:14px;padding-top:0">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);display:flex;align-items:center;justify-content:center;margin:0 auto">
                <i class="fa-solid fa-trash" style="font-size:1.4rem;color:var(--red)"></i>
            </div>
            <div>
                <h3 id="exclusaoTitulo" style="font-size:1.05rem;font-weight:700;color:var(--text-main);margin-bottom:8px">Confirmar exclusão?</h3>
                <p style="font-size:.85rem;color:var(--text-sub);line-height:1.65">
                    <span id="exclusaoMensagem">Você está prestes a excluir</span><br>
                    <strong id="exclusaoAlvo" style="color:var(--text-main)"></strong>.<br>
                    <span style="color:var(--red);font-size:.78rem;font-weight:500">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right:3px"></i>
                        Esta ação não pode ser desfeita.
                    </span>
                </p>
            </div>
        </div>

        <div class="rodape-modal">
            <button type="button" class="botao-secundario" style="flex:1" onclick="fecharModalExclusao()">
                Cancelar
            </button>
            <button type="button" id="btnConfirmarExclusao" onclick="confirmarExclusao()"
                style="flex:1;padding:10px;background:var(--red);border:none;border-radius:var(--radius-sm);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all var(--transition)">
                <i class="fa-solid fa-trash" style="margin-right:6px"></i>
                Excluir
            </button>
        </div>

    </div>
</div>
