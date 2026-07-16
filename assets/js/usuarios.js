/* ============================================================
   Sprint Max — Usuários (admin)
   Busca, drawer de edição, modais (criar / visualizar) e exclusão.
   O shell (sidebar, perfil, toast) vem de painel.js.
   ============================================================ */

(function () {
    'use strict';

    function $(id) {
        return document.getElementById(id);
    }

    /* ── Drawer editar (global) ────────────────────────────────── */

    function openDrawer(user) {
        $('editId').value = user.id;
        $('editNome').value = user.nome;
        $('editEmail').value = user.email;
        $('editSenha').value = '';
        $('drawerAvatar').src = user.avatar;
        $('drawerUsername').textContent = user.nome;
        $('editTipo').value = user.tipo || 'usuario';
        $('editStatus').value = user.status || 'ativo';

        $('drawerOverlay').classList.add('open');
        $('editDrawer').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        var ov = $('drawerOverlay');
        var dr = $('editDrawer');
        if (ov) ov.classList.remove('open');
        if (dr) dr.classList.remove('open');
        document.body.style.overflow = '';
    }

    /* ── Modal criar (global) ──────────────────────────────────── */

    function openModal() {
        $('createForm').reset();
        $('modalBackdrop').classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
            var n = $('criarNome');
            if (n) n.focus();
        }, 120);
    }

    function closeModal() {
        var backdrop = $('modalBackdrop');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleModalClick(e) {
        if (e.target === $('modalBackdrop')) closeModal();
    }

    /* ── Modal visualizar (global) ─────────────────────────────── */

    function openViewModal(user) {
        $('viewAvatar').src = user.avatar;
        $('viewNome').textContent = user.nome;
        $('viewId').textContent = '#' + user.id;
        $('viewEmail').textContent = user.email;

        // Reaproveita as mesmas classes das badges da tabela (definidas no CSS).
        var tipoBadge = $('viewTipoBadge');
        tipoBadge.innerHTML = user.tipo === 'admin'
            ? '<span class="badge-tipo admin">Administrador</span>'
            : '<span class="badge-tipo usuario">Usuário</span>';

        var statusBadge = $('viewStatusBadge');
        statusBadge.innerHTML = user.status === 'ativo'
            ? '<span class="badge badge-green">Ativo</span>'
            : '<span class="badge badge-orange">Inativo</span>';

        var createdRow = $('viewCreatedRow');
        if (user.created_at) {
            var d = new Date(user.created_at);
            $('viewCreated').textContent = d.toLocaleDateString('pt-BR', {
                day: '2-digit', month: 'long', year: 'numeric'
            });
            createdRow.style.display = '';
        } else {
            createdRow.style.display = 'none';
        }

        $('viewEditBtn').onclick = function () {
            closeViewModal();
            openDrawer(user);
        };

        $('viewModalBackdrop').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
        var backdrop = $('viewModalBackdrop');
        if (backdrop) backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleViewModalClick(e) {
        if (e.target === $('viewModalBackdrop')) closeViewModal();
    }

    /* ── Exclusão (global) ─────────────────────────────────────── */

    function confirmDelete(id, nome) {
        abrirModalExclusao({
            titulo: 'Excluir usuário?',
            alvo: nome,
            aoConfirmar: function () {
                $('deleteId').value = id;
                $('deleteForm').submit();
            }
        });
    }

    window.openDrawer = openDrawer;
    window.closeDrawer = closeDrawer;
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.handleModalClick = handleModalClick;
    window.openViewModal = openViewModal;
    window.closeViewModal = closeViewModal;
    window.handleViewModalClick = handleViewModalClick;
    window.confirmDelete = confirmDelete;

    /* ── Inicialização ─────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {

        /* Busca com debounce (redireciona com ?busca=) */
        var searchInput = $('searchInput');
        if (searchInput) {
            var searchTimer;
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    var val = searchInput.value.trim();
                    window.location.href = val ? '?busca=' + encodeURIComponent(val) : '?';
                }, 420);
            });
        }

        /* ESC fecha drawers/modais desta página */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDrawer();
                closeModal();
                closeViewModal();
            }
        });
    });
})();
