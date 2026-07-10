<?php require_once __DIR__ . '/../app/controller/produtosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <!-- Anti-flash: aplica tema antes da primeira renderizacao -->
    <script>
        (function() {
            var t = localStorage.getItem('sprint-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
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
    <link rel="stylesheet" href="/assets/css/theme.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="main-wrapper">

        <!-- TOPBAR -->
        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="page-content">

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
                                    $searchable = strtolower($p['nome'] . ' ' . $p['categoria'] . ' ' . ($p['marca'] ?? '') . ' ' . ($p['cor'] ?? ''));
                                    $pJson = htmlspecialchars(json_encode([
                                        'id'         => $p['id'],
                                        'nome'       => $p['nome'],
                                        'marca'      => $p['marca']      ?? '',
                                        'cor'        => $p['cor']        ?? '',
                                        'categoria'  => $p['categoria'],
                                        'preco'      => $p['preco'],
                                        'quantidade' => $p['quantidade'],
                                        'status'     => $p['status'],
                                        'imagem'     => $p['imagem']     ?? '',
                                    ]), ENT_QUOTES);
                                ?>
                                    <tr data-searchable="<?= htmlspecialchars($searchable) ?>">
                                        <td>
                                            <div class="product-cell">
                                                <?php if (!empty($p['imagem'])): ?>
                                                    <img class="product-thumb-img"
                                                        src="<?= htmlspecialchars($p['imagem']) ?>"
                                                        alt="">
                                                <?php else: ?>
                                                    <div class="product-thumb">
                                                        <i class="fa-solid fa-box"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="product-name"><?= htmlspecialchars($p['nome']) ?></span>
                                                    <?php if (!empty($p['marca']) || !empty($p['cor'])): ?>
                                                        <div style="font-size:.72rem;color:var(--text-dim);margin-top:2px">
                                                            <?= htmlspecialchars(implode(' · ', array_filter([$p['marca'] ?? '', $p['cor'] ?? '']))) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['tags'])): ?>
                                                        <div class="prod-tags">
                                                            <?php foreach (array_slice(explode(',', $p['tags']), 0, 3) as $tag): ?>
                                                                <?php if (trim($tag) !== ''): ?>
                                                                    <span class="prod-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                                        <td style="font-weight:600;color:var(--text-main)"><?= precoFormatado($p['preco']) ?></td>
                                        <td><?= (int)$p['quantidade'] ?></td>
                                        <td><?= statusBadgeProduto($p['status']) ?></td>
                                        <td>
                                            <div class="actions-cell">
                                                <!-- Toggle visibilidade -->
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="acao" value="toggle_visivel">
                                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                    <button type="submit" class="btn-icon"
                                                        title="<?= ($p['visivel'] ?? 1) ? 'Ocultar da loja' : 'Tornar visível' ?>">
                                                        <i class="fa-solid <?= ($p['visivel'] ?? 1) ? 'fa-eye' : 'fa-eye-slash' ?>"
                                                            style="color:<?= ($p['visivel'] ?? 1) ? 'var(--green)' : 'var(--text-dim)' ?>"></i>
                                                    </button>
                                                </form>
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


    <div class="drawer-overlay" id="drawerOverlay" onclick="closeEditDrawer()"></div>

    <div class="drawer" id="editDrawer" role="dialog" aria-modal="true" aria-label="Editar produto">

        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Produto</h3>
            <button class="btn-close-drawer" onclick="closeEditDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="/pages/produtos.php" enctype="multipart/form-data" novalidate>
            <input type="hidden" id="editId" name="id" value="">
            <input type="hidden" name="acao" value="editar">

            <div class="drawer-body">
                <div class="form-group">
                    <label class="form-label" for="editNome">Nome do produto</label>
                    <input type="text" id="editNome" name="nome" class="form-input" placeholder="Nome do produto" maxlength="70">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editMarca">Marca</label>
                    <input type="text" id="editMarca" name="marca" class="form-input"
                        placeholder="Ex: Nike, Adidas, Puma..." maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editCor">Cor</label>
                    <input type="text" id="editCor" name="cor" class="form-input"
                        placeholder="Ex: Preto, Branco, Azul..." maxlength="60">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editCategoria">Categoria</label>
                    <select id="editCategoria" name="categoria" class="form-select">
                        <option value="" selected disabled>Selecione uma categoria</option>
                        <option value="Tênis Esportivo">Tênis Esportivo</option>
                        <option value="Chuteira">Chuteira</option>
                        <option value="Bola">Bola</option>
                        <option value="Camiseta Esportiva">Camiseta Esportiva</option>
                        <option value="Shorts / Calção">Shorts / Calção</option>
                        <option value="Meias">Meias</option>
                        <option value="Luvas">Luvas</option>
                        <option value="Capacete">Capacete</option>
                        <option value="Óculos Esportivo">Óculos Esportivo</option>
                        <option value="Raquete">Raquete</option>
                        <option value="Equipamentos de Musculação">Equipamentos de Musculação</option>
                        <option value="Suplementos">Suplementos</option>
                        <option value="Acessórios">Acessórios</option>
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

                <div class="form-group">
                    <label class="form-label">Imagem do produto</label>
                    <div class="upload-area" id="editUploadArea">
                        <input type="file" id="editImagem" name="imagem"
                            accept="image/jpeg,image/png,image/webp"
                            style="display:none">
                        <!-- Sem imagem -->
                        <div id="editUploadVazio">
                            <i class="fa-regular fa-image upload-icon"></i>
                            <span class="upload-titulo">Clique para adicionar imagem</span>
                            <span class="upload-dica">JPG, PNG ou WEBP — máx. 5 MB</span>
                        </div>
                        <!-- Com imagem -->
                        <div id="editUploadPreview" style="display:none">
                            <img id="editImgPreview" src="" alt="Preview">
                            <div class="upload-arquivo">
                                <i class="fa-solid fa-circle-check" style="color:var(--green);flex-shrink:0"></i>
                                <span id="editUploadNome">Imagem atual</span>
                                <button type="button" id="editUploadRemover" aria-label="Remover">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <span class="form-hint">Deixe sem alterar para manter a imagem atual.</span>
                </div>

                <!-- Tags do produto -->
                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tag-picker" id="editTagPicker">
                        <button type="button" class="tag-chip" data-tag="Promoção">Promoção</button>
                        <button type="button" class="tag-chip" data-tag="Lançamento">Lançamento</button>
                        <button type="button" class="tag-chip" data-tag="Exclusivo">Exclusivo</button>
                        <button type="button" class="tag-chip" data-tag="Mais Vendido">Mais Vendido</button>
                        <button type="button" class="tag-chip" data-tag="Novidade">Novidade</button>
                        <button type="button" class="tag-chip" data-tag="Oferta">Oferta</button>
                        <button type="button" class="tag-chip" data-tag="Kit">Kit</button>
                        <button type="button" class="tag-chip" data-tag="Edição Limitada">Edição Limitada</button>
                    </div>
                    <input type="hidden" id="editTagsInput" name="tags">
                </div>

                <!-- Visibilidade -->
                <div class="form-group">
                    <label class="form-label" for="editVisivel">Visibilidade na loja</label>
                    <select id="editVisivel" name="visivel" class="form-select">
                        <option value="1">Visível — aparece na loja</option>
                        <option value="0">Oculto — não aparece na loja</option>
                    </select>
                    <span class="form-hint">Produtos ocultos só o admin pode ver.</span>
                </div>

                <div class="drawer-foot">
                    <button type="button" class="btn-cancel-drawer" onclick="closeEditDrawer()">Cancelar</button>
                    <button type="button" class="btn-save-drawer">
                        <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Salvar Alterações
                    </button>
                </div>
        </form>

    </div>


    <div class="modal-backdrop" id="productModalBackdrop" onclick="handleProductModalClick(event)">
        <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Novo produto">

            <div class="modal-head">
                <h3><i class="fa-solid fa-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Novo Produto</h3>
                <button class="btn-close-modal" onclick="closeProductModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="novoProdutoForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                <div class="modal-body">

                    <!-- IMAGEM (primeiro campo) -->
                    <div class="form-group">
                        <label class="form-label">Imagem do produto</label>
                        <div class="upload-area" id="criarUploadArea">
                            <input type="file" id="criarImagem" name="imagem"
                                accept="image/jpeg,image/png,image/webp"
                                style="display:none">
                            <!-- placeholder -->
                            <div id="criarUploadVazio">
                                <i class="fa-regular fa-image upload-icon"></i>
                                <span class="upload-titulo">Clique ou arraste uma imagem</span>
                                <span class="upload-dica">JPG, PNG ou WEBP — máx. 5 MB (opcional)</span>
                            </div>
                            <!-- preview -->
                            <div id="criarUploadPreview" style="display:none">
                                <img id="criarImgPreview" src="" alt="Preview">
                                <div class="upload-arquivo">
                                    <i class="fa-solid fa-circle-check" style="color:var(--green);flex-shrink:0"></i>
                                    <span id="criarUploadNome"></span>
                                    <button type="button" id="criarUploadRemover"
                                        aria-label="Remover imagem">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        <label class="form-label" for="criarMarca">Marca</label>
                        <input type="text" id="criarMarca" name="marca" class="form-input"
                            placeholder="Ex: Nike, Adidas, Puma..." maxlength="100" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarCor">Cor</label>
                        <input type="text" id="criarCor" name="cor" class="form-input"
                            placeholder="Ex: Preto, Branco, Azul..." maxlength="60" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="criarCategoria">Categoria</label>
                        <select id="criarCategoria"
                            name="categoria"
                            class="form-select"
                            required>
                            <option value="">Selecione uma categoria</option>
                            <option value="Tênis Esportivo">Tênis Esportivo</option>
                            <option value="Chuteira">Chuteira</option>
                            <option value="Bola">Bola</option>
                            <option value="Camiseta Esportiva">Camiseta Esportiva</option>
                            <option value="Shorts / Calção">Shorts / Calção</option>
                            <option value="Meias">Meias</option>
                            <option value="Luvas">Luvas</option>
                            <option value="Capacete">Capacete</option>
                            <option value="Óculos Esportivo">Óculos Esportivo</option>
                            <option value="Raquete">Raquete</option>
                            <option value="Equipamentos de Musculação">Equipamentos de Musculação</option>
                            <option value="Suplementos">Suplementos</option>
                            <option value="Acessórios">Acessórios</option>
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

                    <!-- Tags do produto -->
                    <div class="form-group">
                        <label class="form-label">Tags</label>
                        <div class="tag-picker" id="criarTagPicker">
                            <button type="button" class="tag-chip" data-tag="Promoção">Promoção</button>
                            <button type="button" class="tag-chip" data-tag="Lançamento">Lançamento</button>
                            <button type="button" class="tag-chip" data-tag="Exclusivo">Exclusivo</button>
                            <button type="button" class="tag-chip" data-tag="Mais Vendido">Mais Vendido</button>
                            <button type="button" class="tag-chip" data-tag="Novidade">Novidade</button>
                            <button type="button" class="tag-chip" data-tag="Oferta">Oferta</button>
                            <button type="button" class="tag-chip" data-tag="Kit">Kit</button>
                            <button type="button" class="tag-chip" data-tag="Edição Limitada">Edição Limitada</button>
                        </div>
                        <input type="hidden" id="criarTagsInput" name="tags">
                    </div>

                    <!-- Visibilidade -->
                    <div class="form-group">
                        <label class="form-label" for="criarVisivel">Visibilidade na loja</label>
                        <select id="criarVisivel" name="visivel" class="form-select">
                            <option value="1">Visível — aparece na loja</option>
                            <option value="0">Oculto — não aparece na loja</option>
                        </select>
                        <span class="form-hint">Produtos ocultos não são exibidos para clientes.</span>
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

        /* search (client-side) */
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

        /* drawer (editar produto) */
        const drawerOverlay = document.getElementById('drawerOverlay');
        const editDrawer = document.getElementById('editDrawer');

        function openEditDrawer(produto) {
            document.getElementById('editId').value = produto.id;
            document.getElementById('editNome').value = produto.nome;
            document.getElementById('editMarca').value = produto.marca || '';
            document.getElementById('editCor').value = produto.cor || '';
            document.getElementById('editCategoria').value = produto.categoria;
            document.getElementById('editPreco').value = produto.preco;
            document.getElementById('editEstoque').value = produto.quantidade;

            // Tags e visibilidade
            setTagPicker('editTagPicker', 'editTagsInput', produto.tags || '');
            var editVis = document.getElementById('editVisivel');
            if (editVis) editVis.value = produto.visivel === 0 ? '0' : '1';

            // Upload area do drawer — mostra imagem atual se houver
            var editArea = document.getElementById('editUploadArea');
            var editVazio = document.getElementById('editUploadVazio');
            var editPrev = document.getElementById('editUploadPreview');
            var editImg = document.getElementById('editImgPreview');
            var editNome = document.getElementById('editUploadNome');
            var editFile = document.getElementById('editImagem');

            if (editFile) editFile.value = '';

            if (produto.imagem) {
                editImg.src = produto.imagem;
                editNome.textContent = 'Imagem atual';
                editVazio.style.display = 'none';
                editPrev.style.display = 'block';
                editArea.classList.add('tem-arquivo');
            } else {
                editImg.src = '';
                editVazio.style.display = 'flex';
                editPrev.style.display = 'none';
                editArea.classList.remove('tem-arquivo');
            }

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

        /* modal (novo produto) */
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
                // Limpar área de upload
                var area = document.getElementById('criarUploadArea');
                if (area) {
                    area.classList.remove('tem-arquivo');
                    document.getElementById('criarUploadVazio').style.display = 'flex';
                    document.getElementById('criarUploadPreview').style.display = 'none';
                    document.getElementById('criarImgPreview').src = '';
                    document.getElementById('criarUploadNome').textContent = '';
                }
                // Reset tags e visibilidade
                if (typeof setTagPicker === 'function') {
                    setTagPicker('criarTagPicker', 'criarTagsInput', '');
                }
                var vis = document.getElementById('criarVisivel');
                if (vis) vis.value = '1';
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


        /* fechar com ESC */
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

        /* validação — novo produto */
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

        /* validação — editar produto (drawer) */
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

        /* ── Upload de imagem — Novo Produto ──────────────── */
        (function() {
            var area = document.getElementById('criarUploadArea');
            var input = document.getElementById('criarImagem');
            var vazio = document.getElementById('criarUploadVazio');
            var prev = document.getElementById('criarUploadPreview');
            var img = document.getElementById('criarImgPreview');
            var nome = document.getElementById('criarUploadNome');
            var btnRem = document.getElementById('criarUploadRemover');

            // Clique na área abre o seletor
            area.addEventListener('click', function() {
                if (!area.classList.contains('tem-arquivo')) input.click();
            });

            // Drag & drop
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!area.classList.contains('tem-arquivo')) area.classList.add('drag-over');
            });
            area.addEventListener('dragleave', function() {
                area.classList.remove('drag-over');
            });
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('drag-over');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    var dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    input.files = dt.files;
                    mostrarPreview(e.dataTransfer.files[0]);
                }
            });

            // Arquivo selecionado
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) mostrarPreview(this.files[0]);
            });

            function mostrarPreview(arquivo) {
                var r = new FileReader();
                r.onload = function(e) {
                    img.src = e.target.result;
                    nome.textContent = arquivo.name;
                    vazio.style.display = 'none';
                    prev.style.display = 'block';
                    area.classList.add('tem-arquivo');
                };
                r.readAsDataURL(arquivo);
            }

            // Remover imagem
            btnRem.addEventListener('click', function(e) {
                e.stopPropagation();
                input.value = '';
                img.src = '';
                nome.textContent = '';
                prev.style.display = 'none';
                vazio.style.display = 'flex';
                area.classList.remove('tem-arquivo');
            });
        })();

        /* upload imagem — editar produto (drawer) */
        (function() {
            var area = document.getElementById('editUploadArea');
            var input = document.getElementById('editImagem');
            var vazio = document.getElementById('editUploadVazio');
            var prev = document.getElementById('editUploadPreview');
            var img = document.getElementById('editImgPreview');
            var nome = document.getElementById('editUploadNome');
            var btnRem = document.getElementById('editUploadRemover');

            area.addEventListener('click', function() {
                if (!area.classList.contains('tem-arquivo')) input.click();
            });

            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (!area.classList.contains('tem-arquivo')) area.classList.add('drag-over');
            });
            area.addEventListener('dragleave', function() {
                area.classList.remove('drag-over');
            });
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                area.classList.remove('drag-over');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    var dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    input.files = dt.files;
                    mostrarPreviewEdit(e.dataTransfer.files[0]);
                }
            });

            input.addEventListener('change', function() {
                if (this.files && this.files[0]) mostrarPreviewEdit(this.files[0]);
            });

            function mostrarPreviewEdit(arquivo) {
                var r = new FileReader();
                r.onload = function(e) {
                    img.src = e.target.result;
                    nome.textContent = arquivo.name;
                    vazio.style.display = 'none';
                    prev.style.display = 'block';
                    area.classList.add('tem-arquivo');
                };
                r.readAsDataURL(arquivo);
            }

            btnRem.addEventListener('click', function(e) {
                e.stopPropagation();
                input.value = '';
                img.src = '';
                nome.textContent = '';
                prev.style.display = 'none';
                vazio.style.display = 'flex';
                area.classList.remove('tem-arquivo');
            });
        })();

        /* ── Tag Picker ─────────────────────────────────── */
        function initTagPicker(pickerId, inputId) {
            var picker = document.getElementById(pickerId);
            if (!picker) return;
            picker.querySelectorAll('.tag-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    this.classList.toggle('ativo');
                    syncTagInput(pickerId, inputId);
                });
            });
        }

        function setTagPicker(pickerId, inputId, tagsStr) {
            var picker = document.getElementById(pickerId);
            if (!picker) return;
            var tags = tagsStr ? tagsStr.split(',').map(function(t) {
                return t.trim();
            }) : [];
            picker.querySelectorAll('.tag-chip').forEach(function(chip) {
                chip.classList.toggle('ativo', tags.indexOf(chip.dataset.tag) !== -1);
            });
            syncTagInput(pickerId, inputId);
        }

        function syncTagInput(pickerId, inputId) {
            var selected = [];
            document.querySelectorAll('#' + pickerId + ' .tag-chip.ativo').forEach(function(c) {
                selected.push(c.dataset.tag);
            });
            var inp = document.getElementById(inputId);
            if (inp) inp.value = selected.join(',');
        }

        // Inicializa pickers
        initTagPicker('criarTagPicker', 'criarTagsInput');
        initTagPicker('editTagPicker', 'editTagsInput');
    </script>

</body>

</html>