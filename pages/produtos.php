<?php require_once __DIR__ . '/../app/controller/produtosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Produtos</title>

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

            <!-- ── CARD PRODUTOS ─────────────────────────────────── -->
            <div class="card">

                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="fa-solid fa-box" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>Produtos</h2>
                        <p>Gerencie todos os produtos do sistema.</p>
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
                                placeholder="Pesquisar por nome ou categoria..."
                                autocomplete="off">
                        </div>
                        <button class="btn-primary" onclick="openProductModal()">
                            <i class="fa-solid fa-plus"></i>
                            Novo Produto
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php foreach ($produtos as $p):
                                    $searchable = strtolower($p['nome'] . ' ' . $p['categoria']);
                                    $pJson = htmlspecialchars(json_encode([
                                        'id'         => $p['id'],
                                        'nome'       => $p['nome'],
                                        'categoria'  => $p['categoria'],
                                        'preco'      => $p['preco'],
                                        'quantidade' => $p['quantidade'],
                                        'status'     => $p['status'],
                                    ]), ENT_QUOTES);
                                ?>
                                    <tr data-searchable="<?= htmlspecialchars($searchable) ?>">
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb">
                                                    <i class="fa-solid fa-box"></i>
                                                </div>
                                                <span class="product-name"><?= htmlspecialchars($p['nome']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                                        <td style="font-weight:600;color:var(--text-main)"><?= precoFormatado($p['preco']) ?></td>
                                        <td><?= (int)$p['quantidade'] ?></td>
                                        <td><?= statusBadgeProduto($p['status']) ?></td>
                                        <td>
                                            <div class="actions-cell">
                                                <button class="btn-icon edit"
                                                    title="Editar"
                                                    onclick='openEditDrawer(<?= $pJson ?>)'>
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir este produto?')">

                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">

                                                    <button class="btn-icon del" type="submit" title="Excluir">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>

                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Estado vazio (exibido pelo JS quando busca não encontra nada) -->
                                <tr id="emptyRow" style="display:none">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-box-open"></i>
                                            <h4>Nenhum produto encontrado</h4>
                                            <p>Tente outro termo de pesquisa.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div><!-- /card-body -->

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Mostrando <strong><span id="countVisible"><?= $total_produtos ?></span></strong>
                        de <strong><?= $total_produtos ?></strong> produto<?= $total_produtos !== 1 ? 's' : '' ?>
                    </div>
                    <div class="pagination-btns">
                        <button class="page-btn" disabled>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" disabled>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

            </div><!-- /card -->

        </main><!-- /page-content -->

    </div><!-- /main-wrapper -->


    <!-- ═══════════════════════════════════════════════════════════
     DRAWER — Editar Produto
     ═══════════════════════════════════════════════════════════ -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="closeEditDrawer()"></div>

    <div class="drawer" id="editDrawer" role="dialog" aria-modal="true" aria-label="Editar produto">

        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Produto</h3>
            <button class="btn-close-drawer" onclick="closeEditDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="/pages/produtos.php" novalidate>
            <input type="hidden" id="editId" name="id" value="">
            <input type="hidden" name="acao" value="editar">

            <div class="drawer-body">
                <div class="form-group">
                    <label class="form-label" for="editNome">Nome do produto</label>
                    <input type="text" id="editNome" name="nome" class="form-input" placeholder="Nome do produto" maxlength="70">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editCategoria">Categoria</label>
                    <select id="editCategoria" name="categoria" class="form-select">
                        <option value="" selected disabled>Selecione uma categoria</option>
                        <option value="Roupas Esportivas">Roupas Esportivas</option>
                        <option value="Calçados">Calçados</option>
                        <option value="Acessórios">Acessórios</option>
                        <option value="Equipamentos Fitness">Equipamentos Fitness</option>
                        <option value="Futebol">Futebol</option>
                        <option value="Basquete">Basquete</option>
                        <option value="Vôlei">Vôlei</option>
                        <option value="Corrida">Corrida</option>
                        <option value="Ciclismo">Ciclismo</option>
                        <option value="Natação">Natação</option>
                        <option value="Musculação">Musculação</option>
                        <option value="Camping e Aventura">Camping e Aventura</option>
                        <option value="Suplementos">Suplementos</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editPreco">Preço (R$)</label>
                    <input type="number" id="editPreco" name="preco" class="form-input" placeholder="0.00" step="0.01" min="0.01" max="999999.99">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstoque">Quantidade em estoque</label>
                    <input type="number" id="editEstoque" name="quantidade" class="form-input" placeholder="0" min="0" max="99999">
                </div>
            </div>

            <div class="drawer-foot">
                <button type="button" class="btn-cancel-drawer" onclick="closeEditDrawer()">Cancelar</button>
                <button type="button" class="btn-save-drawer">
                    <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Salvar Alterações
                </button>
            </div>
        </form>

    </div>


    <!-- ═══════════════════════════════════════════════════════════
     MODAL — Novo Produto
     ═══════════════════════════════════════════════════════════ -->
    <div class="modal-backdrop" id="productModalBackdrop" onclick="handleProductModalClick(event)">
        <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Novo produto">

            <div class="modal-head">
                <h3><i class="fa-solid fa-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Novo Produto</h3>
                <button class="btn-close-modal" onclick="closeProductModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="novoProdutoForm" method="POST" action="" novalidate>
                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label" for="criarNome">Nome *</label>
                        <input
                            type="text"
                            id="criarNome"
                            name="nome"
                            class="form-input"
                            placeholder="Nome do produto"
                            maxlength="70"
                            autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarCategoria">Categoria</label>
                        <select id="criarCategoria"
                            name="categoria"
                            class="form-select"
                            required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Roupas Esportivas">Roupas Esportivas</option>
                            <option value="Calçados">Calçados</option>
                            <option value="Acessórios">Acessórios</option>
                            <option value="Equipamentos Fitness">Equipamentos Fitness</option>
                            <option value="Futebol">Futebol</option>
                            <option value="Basquete">Basquete</option>
                            <option value="Vôlei">Vôlei</option>
                            <option value="Corrida">Corrida</option>
                            <option value="Ciclismo">Ciclismo</option>
                            <option value="Natação">Natação</option>
                            <option value="Musculação">Musculação</option>
                            <option value="Camping e Aventura">Camping e Aventura</option>
                            <option value="Suplementos">Suplementos</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarPreco">Preço (R$) *</label>
                        <input
                            type="number"
                            id="criarPreco"
                            name="preco"
                            class="form-input"
                            placeholder="0.00"
                            step="0.01"
                            min="1"
                            max="9999.99"
                            required
                            autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarEstoque">Quantidade em estoque</label>
                        <input
                            type="number"
                            id="criarEstoque"
                            name="quantidade"
                            class="form-input"
                            placeholder="0"
                            min="0"
                            max="9999"
                            value="1"
                            required
                            autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarDescricao">Descrição</label>
                        <textarea id="criarDescricao" name="descricao" class="form-input"
                            placeholder="Descrição do produto..." rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-foot">
                    <button type="button" class="btn-ghost" onclick="closeProductModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Salvar Produto
                    </button>
                </div>
            </form>

        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════════
     TOAST
     ═══════════════════════════════════════════════════════════ -->
    <div class="sp-toast" id="spToast">
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>

    <!-- PHP flash → JS toast -->
    <?php if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(
                    <?= json_encode($flash['message']) ?>,
                    <?= json_encode($flash['type'] === 'success' ? 'success' : 'error') ?>
                );
            });
        </script>
    <?php endif; ?>


    <!-- Modal Editar Perfil -->
    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteId">
    </form>


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

        /* ── Search (client-side) ────────────────────────────────── */
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');
        const emptyRow = document.getElementById('emptyRow');
        const countEl = document.getElementById('countVisible');
        let searchTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                const query = searchInput.value.toLowerCase().trim();
                const rows = tableBody.querySelectorAll('tr[data-searchable]');
                let visible = 0;
                rows.forEach(function(row) {
                    const match = !query || row.getAttribute('data-searchable').includes(query);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                emptyRow.style.display = visible === 0 ? '' : 'none';
                countEl.textContent = visible;
            }, 300);
        });

        /* ── Drawer (editar produto) ─────────────────────────────── */
        const drawerOverlay = document.getElementById('drawerOverlay');
        const editDrawer = document.getElementById('editDrawer');

        function openEditDrawer(produto) {
            document.getElementById('editId').value = produto.id;
            document.getElementById('editNome').value = produto.nome;
            document.getElementById('editCategoria').value = produto.categoria;
            document.getElementById('editPreco').value = produto.preco;
            document.getElementById('editEstoque').value = produto.quantidade;

            // Limpar erros anteriores
            var f = document.getElementById('editForm');
            f.querySelectorAll('.field-inline-error').forEach(function(el) {
                el.remove();
            });
            f.querySelectorAll('.form-input, .form-select').forEach(function(el) {
                el.style.borderColor = '';
                el.style.boxShadow = '';
            });

            drawerOverlay.classList.add('open');
            editDrawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeEditDrawer() {
            drawerOverlay.classList.remove('open');
            editDrawer.classList.remove('open');
            document.body.style.overflow = '';
        }

        /* ── Modal (novo produto) ────────────────────────────────── */
        const productModalBackdrop = document.getElementById('productModalBackdrop');

        function openProductModal() {
            var f = productModalBackdrop.querySelector('form');
            if (f) {
                f.reset();
                f.querySelectorAll('.field-inline-error').forEach(function(el) {
                    el.remove();
                });
                f.querySelectorAll('.form-input, .form-select').forEach(function(el) {
                    el.style.borderColor = '';
                    el.style.boxShadow = '';
                });
            }
            productModalBackdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(function() {
                document.getElementById('criarNome').focus();
            }, 120);
        }

        function closeProductModal() {
            productModalBackdrop.classList.remove('open');
            document.body.style.overflow = '';
        }

        function handleProductModalClick(e) {
            if (e.target === productModalBackdrop) closeProductModal();
        }


        /* ── Confirmar exclusão ──────────────────────────────────── */
        function confirmDeleteProduto(id, nome) {

            if (!confirm('Excluir o produto "' + nome + '"?\nEsta ação não pode ser desfeita.')) {
                return;
            }

            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }

        /* ── Fechar com ESC ──────────────────────────────────────── */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditDrawer();
                closeProductModal();
                if (typeof closeProfileModal === 'function') closeProfileModal();
            }
        });

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

        /* ── Validação — Novo Produto ────────────────────────────── */
        (function() {
            var form = document.getElementById('novoProdutoForm');
            var nomeFld = document.getElementById('criarNome');
            var catFld = document.getElementById('criarCategoria');
            var precoFld = document.getElementById('criarPreco');
            var qtdFld = document.getElementById('criarEstoque');

            function showErr(el, msg) {
                clearErr(el);
                el.style.borderColor = '#ef4444';
                el.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.18)';
                var span = document.createElement('span');
                span.className = 'field-inline-error';
                span.style.cssText = 'display:flex;align-items:center;gap:5px;' +
                    'color:#ef4444;font-size:0.74rem;margin-top:6px;font-weight:500;';
                span.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="font-size:0.7rem"></i> ' + msg;
                el.insertAdjacentElement('afterend', span);
            }

            function clearErr(el) {
                var next = el.nextElementSibling;
                if (next && next.classList.contains('field-inline-error')) next.remove();
                el.style.borderColor = '';
                el.style.boxShadow = '';
            }

            nomeFld.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-.'()&\/]/g, '');
                clearErr(this);
            });

            precoFld.addEventListener('input', function() {
                if (parseFloat(this.value) > 999999.99) this.value = '999999.99';
                clearErr(this);
            });

            qtdFld.addEventListener('input', function() {
                if (parseInt(this.value, 10) > 99999) this.value = '99999';
                clearErr(this);
            });

            catFld.addEventListener('change', function() {
                clearErr(this);
            });

            form.addEventListener('submit', function(e) {
                var ok = true;

                if (!nomeFld.value.trim()) {
                    showErr(nomeFld, 'Nome é obrigatório.');
                    ok = false;
                }

                if (!catFld.value) {
                    showErr(catFld, 'Selecione uma categoria.');
                    ok = false;
                }

                var pv = parseFloat(precoFld.value);
                if (precoFld.value === '' || isNaN(pv) || pv < 0.01) {
                    showErr(precoFld, 'Informe um preço válido (mínimo R$ 0,01).');
                    ok = false;
                }

                var ev = parseInt(qtdFld.value, 10);
                if (qtdFld.value === '' || isNaN(ev) || ev < 0) {
                    showErr(qtdFld, 'Informe uma quantidade válida.');
                    ok = false;
                }

                if (!ok) e.preventDefault();
            });
        })();

        /* ── Validação — Editar Produto (Drawer) ─────────────────── */
        (function() {
            var editNomeFld = document.getElementById('editNome');
            var editPrecoFld = document.getElementById('editPreco');
            var editQtdFld = document.getElementById('editEstoque');
            var saveBtn = document.querySelector('.btn-save-drawer');

            function showErr(el, msg) {
                clearErr(el);
                el.style.borderColor = '#ef4444';
                el.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.18)';
                var span = document.createElement('span');
                span.className = 'field-inline-error';
                span.style.cssText = 'display:flex;align-items:center;gap:5px;' +
                    'color:#ef4444;font-size:0.74rem;margin-top:6px;font-weight:500;';
                span.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="font-size:0.7rem"></i> ' + msg;
                el.insertAdjacentElement('afterend', span);
            }

            function clearErr(el) {
                var next = el.nextElementSibling;
                if (next && next.classList.contains('field-inline-error')) next.remove();
                el.style.borderColor = '';
                el.style.boxShadow = '';
            }

            editNomeFld.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ0-9\s\-.'()&\/]/g, '');
                clearErr(this);
            });

            editPrecoFld.addEventListener('input', function() {
                if (parseFloat(this.value) > 999999.99) this.value = '999999.99';
                clearErr(this);
            });

            editQtdFld.addEventListener('input', function() {
                if (parseInt(this.value, 10) > 99999) this.value = '99999';
                clearErr(this);
            });

            saveBtn.addEventListener('click', function() {
                [editNomeFld, editPrecoFld, editQtdFld].forEach(clearErr);
                var ok = true;

                if (!editNomeFld.value.trim()) {
                    showErr(editNomeFld, 'Nome é obrigatório.');
                    ok = false;
                }

                var pv = parseFloat(editPrecoFld.value);
                if (editPrecoFld.value === '' || isNaN(pv) || pv < 0.01) {
                    showErr(editPrecoFld, 'Informe um preço válido (mínimo R$ 0,01).');
                    ok = false;
                }

                var ev = parseInt(editQtdFld.value, 10);
                if (editQtdFld.value === '' || isNaN(ev) || ev < 0) {
                    showErr(editQtdFld, 'Informe uma quantidade válida.');
                    ok = false;
                }

                if (ok) document.getElementById('editForm').submit();
            });
        })();
    </script>

</body>

</html>