<?php require_once __DIR__ . '/../app/controller/usuariosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Usuários</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>

    <!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
     ═══════════════════════════════════════════════════════════ -->
    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <!-- ═══════════════════════════════════════════════════════════
     MAIN WRAPPER
     ═══════════════════════════════════════════════════════════ -->
    <div class="main-wrapper">

        <!-- TOPBAR -->
        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <!-- ── CONTENT ──────────────────────────────────────────── -->
        <main class="page-content">

            <!-- ── CARD USUÁRIOS ─────────────────────────────────── -->
            <div class="card">

                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="fa-solid fa-users" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>Usuários</h2>
                        <p>Gerencie todos os usuários do sistema.</p>
                    </div>
                </div>

                <div class="card-body">

                    <!-- Toolbar -->
                    <div class="toolbar">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text"
                                id="searchInput"
                                class="search-input"
                                placeholder="Pesquisar por nome, e-mail ou tipo..."
                                value="<?= htmlspecialchars($busca) ?>"
                                autocomplete="off">
                        </div>
                        <button class="btn-primary" onclick="openModal()">
                            <i class="fa-solid fa-plus"></i>
                            Novo Usuário
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fa-solid fa-users-slash"></i>
                                                <h4>Nenhum usuário encontrado</h4>
                                                <p><?= $busca ? 'Tente outro termo de pesquisa.' : 'Cadastre o primeiro usuário.' ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $u):
                                        $status_val = !empty($u['status']) ? $u['status'] : 'ativo';
                                        $uAvatar    = avatarUrl($u['nome']);
                                        $isSelf     = (int)$u['id'] === (int)$usuario_logado['id'];
                                        $uJson      = htmlspecialchars(json_encode([
                                            'id'     => $u['id'],
                                            'nome'   => $u['nome'],
                                            'email'  => $u['email'],
                                            'tipo'   => $u['tipo'],
                                            'status' => $status_val,
                                            'avatar' => $uAvatar,
                                        ]), ENT_QUOTES);
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <img class="user-avatar"
                                                        src="<?= $uAvatar ?>"
                                                        alt="Avatar de <?= htmlspecialchars($u['nome']) ?>">
                                                    <div>
                                                        <div class="user-name"><?= htmlspecialchars($u['nome']) ?></div>
                                                        <div class="user-email">#<?= $u['id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= tipoBadge($u['tipo']) ?></td>
                                            <td><?= statusBadge($status_val) ?></td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-icon edit"
                                                        title="Editar"
                                                        onclick='openDrawer(<?= $uJson ?>)'>
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <?php if (!$isSelf): ?>
                                                        <button class="btn-icon del"
                                                            title="Excluir"
                                                            onclick="confirmDelete(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nome'])) ?>')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn-icon del" title="Não pode excluir a si mesmo" disabled style="opacity:.3;cursor:not-allowed">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div><!-- /card-body -->

                <!-- Pagination -->
                <?php if ($total > 0): ?>
                    <div class="pagination">
                        <div class="pagination-info">
                            Mostrando <strong><?= $inicio ?>–<?= $fim ?></strong> de <strong><?= $total ?></strong> usuários
                        </div>
                        <div class="pagination-btns">
                            <?php
                            // Botão anterior
                            $prevDisabled = $pagina <= 1 ? 'disabled' : '';
                            $prevHref     = '?pagina=' . ($pagina - 1) . ($busca ? '&busca=' . urlencode($busca) : '');
                            ?>
                            <button class="page-btn" <?= $prevDisabled ?>
                                <?= !$prevDisabled ? "onclick=\"location.href='{$prevHref}'\"" : '' ?>>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <?php
                            // Páginas numeradas (máx 5)
                            $start = max(1, min($pagina - 2, $total_paginas - 4));
                            $end   = min($total_paginas, $start + 4);
                            for ($p = $start; $p <= $end; $p++):
                                $href    = '?pagina=' . $p . ($busca ? '&busca=' . urlencode($busca) : '');
                                $active  = $p === $pagina ? 'active' : '';
                            ?>
                                <button class="page-btn <?= $active ?>"
                                    <?= !$active ? "onclick=\"location.href='{$href}'\"" : '' ?>>
                                    <?= $p ?>
                                </button>
                            <?php endfor; ?>

                            <?php
                            // Botão próximo
                            $nextDisabled = $pagina >= $total_paginas ? 'disabled' : '';
                            $nextHref     = '?pagina=' . ($pagina + 1) . ($busca ? '&busca=' . urlencode($busca) : '');
                            ?>
                            <button class="page-btn" <?= $nextDisabled ?>
                                <?= !$nextDisabled ? "onclick=\"location.href='{$nextHref}'\"" : '' ?>>
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /card -->

        </main><!-- /page-content -->

    </div><!-- /main-wrapper -->


    <!-- ═══════════════════════════════════════════════════════════
     DRAWER — Editar Usuário
     ═══════════════════════════════════════════════════════════ -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

    <div class="drawer" id="editDrawer" role="dialog" aria-modal="true" aria-label="Editar usuário">

        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Usuário</h3>
            <button class="btn-close-drawer" onclick="closeDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="drawer-body">
            <div class="drawer-avatar-wrap">
                <img class="drawer-avatar" id="drawerAvatar" src="" alt="Avatar">
                <span class="drawer-username" id="drawerUsername"></span>
            </div>

            <form id="editForm" method="POST" action="/app/controller/usuariosController.php">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="editId">

                <div class="form-group">
                    <label class="form-label" for="editNome">Nome completo</label>
                    <input type="text" id="editNome" name="nome" class="form-input"
                        placeholder="Nome do usuário" required maxlength="120">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEmail">E-mail</label>
                    <input type="email" id="editEmail" name="email" class="form-input"
                        placeholder="email@exemplo.com" required maxlength="180">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editTipo">Tipo / Cargo</label>
                    <select id="editTipo" name="tipo" class="form-select">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editStatus">Status</label>
                    <select id="editStatus" name="status" class="form-select">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSenha">Nova senha <span style="color:var(--text-dim);font-weight:400;text-transform:none">(deixe em branco para manter)</span></label>
                    <input type="password" id="editSenha" name="senha" class="form-input"
                        placeholder="••••••••" minlength="6" autocomplete="new-password">
                    <span class="form-hint">Mínimo 6 caracteres.</span>
                </div>
        </div>

        <div class="drawer-foot">
            <button type="button" class="btn-cancel-drawer" onclick="closeDrawer()">Cancelar</button>
            <button type="submit" class="btn-save-drawer" form="editForm">
                <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Salvar Alterações
            </button>
        </div>

        </form>
    </div>


    <!-- ═══════════════════════════════════════════════════════════
     MODAL — Novo Usuário
     ═══════════════════════════════════════════════════════════ -->
    <div class="modal-backdrop" id="modalBackdrop" onclick="handleModalClick(event)">
        <div class="modal" role="dialog" aria-modal="true" aria-label="Novo usuário">

            <div class="modal-head">
                <h3><i class="fa-solid fa-user-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Novo Usuário</h3>
                <button class="btn-close-modal" onclick="closeModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="/app/controller/usuariosController.php" id="createForm">
                <input type="hidden" name="acao" value="criar">

                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label" for="criarNome">Nome completo *</label>
                        <input type="text" id="criarNome" name="nome" class="form-input"
                            placeholder="Nome do usuário" required maxlength="120">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarEmail">E-mail *</label>
                        <input type="email" id="criarEmail" name="email" class="form-input"
                            placeholder="email@exemplo.com" required maxlength="180">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarSenha">Senha *</label>
                        <input type="password" id="criarSenha" name="senha" class="form-input"
                            placeholder="••••••••" required minlength="6" autocomplete="new-password">
                        <span class="form-hint">Mínimo 6 caracteres.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarTipo">Tipo</label>
                        <select id="criarTipo" name="tipo" class="form-select">
                            <option value="usuario">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarStatus">Status</label>
                        <select id="criarStatus" name="status" class="form-select">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                </div>

                <div class="modal-foot">
                    <button type="button" class="btn-ghost" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Criar Usuário
                    </button>
                </div>
            </form>

        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════════
     FORM — Excluir (hidden)
     ═══════════════════════════════════════════════════════════ -->
    <form id="deleteForm" method="POST" action="/app/controller/usuariosController.php" style="display:none">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteId">
    </form>


    <!-- ═══════════════════════════════════════════════════════════
     TOAST
     ═══════════════════════════════════════════════════════════ -->
    <div class="sp-toast" id="spToast">
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>


    <!-- ─── PHP flash → JS toast ─────────────────────────────── -->
    <?php if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(
                    <?= json_encode($flash['msg']) ?>,
                    <?= json_encode($flash['tipo'] === 'sucesso' ? 'success' : 'error') ?>
                );
            });
        </script>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════ -->
    <script>
        /* ── Sidebar toggle (mobile) ─────────────────────────────── */
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function openSidebar() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('open');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        }

        if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        /* ── Profile dropdown ────────────────────────────────────── */
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.toggle('open');
            profileBtn.classList.toggle('open', isOpen);
            profileBtn.setAttribute('aria-expanded', isOpen);
        });

        document.addEventListener('click', function() {
            profileDropdown.classList.remove('open');
            profileBtn.classList.remove('open');
            profileBtn.setAttribute('aria-expanded', false);
        });

        /* ── Search com debounce ─────────────────────────────────── */
        const searchInput = document.getElementById('searchInput');
        let searchTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                const val = searchInput.value.trim();
                const url = val ? '?busca=' + encodeURIComponent(val) : '?';
                window.location.href = url;
            }, 420);
        });

        /* ── Drawer (editar) ─────────────────────────────────────── */
        const drawerOverlay = document.getElementById('drawerOverlay');
        const editDrawer = document.getElementById('editDrawer');

        function openDrawer(user) {
            document.getElementById('editId').value = user.id;
            document.getElementById('editNome').value = user.nome;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editSenha').value = '';
            document.getElementById('drawerAvatar').src = user.avatar;
            document.getElementById('drawerUsername').textContent = user.nome;

            const tipoSel = document.getElementById('editTipo');
            const statusSel = document.getElementById('editStatus');
            tipoSel.value = user.tipo || 'usuario';
            statusSel.value = user.status || 'ativo';

            drawerOverlay.classList.add('open');
            editDrawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDrawer() {
            drawerOverlay.classList.remove('open');
            editDrawer.classList.remove('open');
            document.body.style.overflow = '';
        }

        /* ── Modal (criar) ───────────────────────────────────────── */
        const modalBackdrop = document.getElementById('modalBackdrop');

        function openModal() {
            document.getElementById('createForm').reset();
            modalBackdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(function() {
                document.getElementById('criarNome').focus();
            }, 120);
        }

        function closeModal() {
            modalBackdrop.classList.remove('open');
            document.body.style.overflow = '';
        }

        function handleModalClick(e) {
            if (e.target === modalBackdrop) closeModal();
        }

        /* Fechar com ESC */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDrawer();
                closeModal();
                if (typeof closeProfileModal === 'function') closeProfileModal();
            }
        });

        /* ── Confirmar exclusão ──────────────────────────────────── */
        function confirmDelete(id, nome) {
            if (!confirm('Excluir o usuário "' + nome + '"?\nEsta ação não pode ser desfeita.')) return;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }

        /* ── Toast ───────────────────────────────────────────────── */
        function showToast(msg, type) {
            const toast = document.getElementById('spToast');
            const iconEl = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');

            toast.className = 'sp-toast ' + type;
            iconEl.className = type === 'success' ?
                'fa-solid fa-circle-check' :
                'fa-solid fa-circle-exclamation';
            msgEl.textContent = msg;

            toast.classList.add('show');
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3800);
        }
    </script>

    <!-- Modal Editar Perfil -->
    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>