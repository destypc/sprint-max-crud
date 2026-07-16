/* ============================================================
   Sprint Max — Painel (shell comum a todas as páginas)
   Carregado uma única vez via app/includes/modal-perfil.php.
   Consolida: sidebar mobile, dropdown de perfil, dropdown de
   notificações, modal "Editar Perfil", toast e bootstrap de flash.
   ============================================================ */

/* ── Funções globais (chamadas por onclick inline no HTML) ──── */

function openProfileModal() {
    var pd = document.getElementById('profileDropdown');
    var pb = document.getElementById('profileBtn');
    if (pd) pd.classList.remove('open');
    if (pb) {
        pb.classList.remove('open');
        pb.setAttribute('aria-expanded', false);
    }
    var backdrop = document.getElementById('profileModalBackdrop');
    if (backdrop) backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeProfileModal() {
    var backdrop = document.getElementById('profileModalBackdrop');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
}

function handleProfileModalClick(e) {
    if (e.target === document.getElementById('profileModalBackdrop')) {
        closeProfileModal();
    }
}

/* ── Modal de exclusão reutilizável ─────────────────────────── */
/* Depende do include app/includes/modal-exclusao.php na página.   */

var _exclusaoConfig = null;

/**
 * Abre o modal de confirmação de exclusão.
 * opcoes: { titulo, mensagem, alvo, aoConfirmar, formId }
 * - titulo/mensagem: textos exibidos (opcionais, têm padrão).
 * - alvo: nome destacado do item que será excluído.
 * - aoConfirmar: função executada ao confirmar (tem prioridade).
 * - formId: alternativa a aoConfirmar — envia o formulário com esse id.
 */
function abrirModalExclusao(opcoes) {
    _exclusaoConfig = opcoes || {};

    var backdrop = document.getElementById('modalExclusaoBackdrop');
    if (!backdrop) return;

    var titulo = document.getElementById('exclusaoTitulo');
    var mensagem = document.getElementById('exclusaoMensagem');
    var alvo = document.getElementById('exclusaoAlvo');
    if (titulo) titulo.textContent = _exclusaoConfig.titulo || 'Confirmar exclusão?';
    if (mensagem) mensagem.textContent = _exclusaoConfig.mensagem || 'Você está prestes a excluir';
    if (alvo) alvo.textContent = _exclusaoConfig.alvo || '';

    var btn = document.getElementById('btnConfirmarExclusao');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-trash" style="margin-right:6px"></i>Excluir';
    }

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function fecharModalExclusao() {
    var backdrop = document.getElementById('modalExclusaoBackdrop');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
}

function confirmarExclusao() {
    var cfg = _exclusaoConfig || {};

    var btn = document.getElementById('btnConfirmarExclusao');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i>Excluindo...';
    }

    if (typeof cfg.aoConfirmar === 'function') {
        cfg.aoConfirmar();
    } else if (cfg.formId) {
        var form = document.getElementById(cfg.formId);
        if (form) form.submit();
    }
}

/**
 * Toast flutuante. Usa o elemento fixo #spToast presente nas páginas.
 * No-op seguro quando o elemento não existe.
 */
function showToast(msg, type) {
    var toast = document.getElementById('spToast');
    if (!toast) return;
    var iconEl = document.getElementById('toastIcon');
    var msgEl = document.getElementById('toastMsg');
    toast.className = 'sp-toast ' + type;
    if (iconEl) {
        iconEl.className = type === 'success'
            ? 'fa-solid fa-circle-check'
            : 'fa-solid fa-circle-exclamation';
    }
    if (msgEl) msgEl.textContent = msg;
    toast.classList.add('show');
    setTimeout(function () {
        toast.classList.remove('show');
    }, 3800);
}

/* ── Inicialização ──────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {

    /* Sidebar (mobile) */
    var sidebar = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('open');
        });
    }
    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        });
    }

    /* Dropdown de perfil */
    var profileBtn = document.getElementById('profileBtn');
    var profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = profileDropdown.classList.toggle('open');
            profileBtn.classList.toggle('open', isOpen);
            profileBtn.setAttribute('aria-expanded', isOpen);
        });
        document.addEventListener('click', function () {
            profileDropdown.classList.remove('open');
            profileBtn.classList.remove('open');
            profileBtn.setAttribute('aria-expanded', false);
        });
    }

    /* Dropdown de notificações */
    var notifBtn = document.getElementById('notifBtn');
    var notifDropdown = document.getElementById('notifDropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                notifDropdown.classList.remove('open');
            }
        });
    }

    /* ESC fecha o modal de perfil e o de exclusão */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeProfileModal();
            fecharModalExclusao();
        }
    });

    /* Bootstrap de flash: lê data-attributes do #spToast */
    var toastEl = document.getElementById('spToast');
    if (toastEl && toastEl.dataset.flashMsg) {
        showToast(toastEl.dataset.flashMsg, toastEl.dataset.flashType || 'success');
    }
});
