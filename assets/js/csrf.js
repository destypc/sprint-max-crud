/**
 * Proteção CSRF no cliente.
 * Lê o token da <meta name="csrf-token"> (definida em head.php) e o injeta
 * automaticamente em todos os formulários POST da página, além de expor
 * window.CSRF_TOKEN para requisições fetch/AJAX.
 */
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;

    var token = meta.getAttribute('content') || '';
    window.CSRF_TOKEN = token;

    function injetarNosFormularios() {
        var forms = document.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            var metodo = (form.getAttribute('method') || '').toLowerCase();
            if (metodo !== 'post') continue;
            if (form.querySelector('input[name="csrf_token"]')) continue;

            var campo = document.createElement('input');
            campo.type = 'hidden';
            campo.name = 'csrf_token';
            campo.value = token;
            form.appendChild(campo);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injetarNosFormularios);
    } else {
        injetarNosFormularios();
    }
})();
