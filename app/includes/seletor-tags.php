<?php
/**
 * Seletor de tags reutilizável (usado nos formulários de criar e editar produto).
 * Espera as variáveis:
 *   $seletorId       — id do container de chips (ex.: 'criarTagPicker')
 *   $inputId         — id do input oculto que recebe a lista final (ex.: 'criarTagsInput')
 *   $tagsDisponiveis — array de tags do catálogo [['nome' => ...], ...]
 */
$tagsDisponiveis = $tagsDisponiveis ?? [];
?>
<div class="tag-picker" id="<?= htmlspecialchars($seletorId) ?>">
    <?php foreach ($tagsDisponiveis as $tag): ?>
    <button type="button" class="tag-chip" data-tag="<?= htmlspecialchars($tag['nome']) ?>">
        <?= htmlspecialchars($tag['nome']) ?>
    </button>
    <?php endforeach; ?>
</div>

<div class="tag-criar">
    <input type="text" class="tag-criar-input" placeholder="Criar nova tag..." maxlength="50"
        data-alvo-picker="<?= htmlspecialchars($seletorId) ?>" data-alvo-input="<?= htmlspecialchars($inputId) ?>">
    <button type="button" class="tag-criar-btn"
        data-alvo-picker="<?= htmlspecialchars($seletorId) ?>" data-alvo-input="<?= htmlspecialchars($inputId) ?>">
        <i class="fa-solid fa-plus"></i> Criar
    </button>
</div>

<input type="hidden" id="<?= htmlspecialchars($inputId) ?>" name="tags">
