<?php
/**
 * Anti-flash: aplica o tema salvo (dark/light) antes da primeira
 * renderização, evitando o "flash" de tema errado ao carregar a página.
 *
 * Mantido inline (e não em /assets/js) de propósito: precisa executar
 * de forma síncrona no <head>, antes do CSS, sem uma requisição extra.
 * Incluído por todas as páginas para eliminar a duplicação do snippet.
 */
?>
<script>
    (function () {
        var t = localStorage.getItem('sprint-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
    })();
</script>
