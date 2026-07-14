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

    /* ESC fecha o modal de perfil */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeProfileModal();
    });

    /* Bootstrap de flash: lê data-attributes do #spToast */
    var toastEl = document.getElementById('spToast');
    if (toastEl && toastEl.dataset.flashMsg) {
        showToast(toastEl.dataset.flashMsg, toastEl.dataset.flashType || 'success');
    }
});
