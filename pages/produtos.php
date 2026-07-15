<?php require_once __DIR__ . '/../app/controller/produtosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/../app/includes/theme-init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Produtos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <div class="card">

                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="fa-solid fa-box" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>Produtos</h2>
                        <p>Gerencie todos os produtos do sistema.</p>
                    </div>
                </div>

                <div class="card-body">

                    <div class="toolbar">
                        <div class="caixa-busca">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" class="entrada-busca"
                                placeholder="Pesquisar por nome ou categoria..." autocomplete="off">
                        </div>
                        <button class="botao-primario" onclick="openProductModal()">
                            <i class="fa-solid fa-plus"></i>
                            Novo Produto
                        </button>
                    </div>

                    <div class="envoltorio-tabela">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Quantidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php foreach ($produtos as $p): ?>
                                <?php
                                    $searchable = strtolower($p['nome'] . ' ' . $p['categoria'] . ' ' . ($p['marca'] ?? '') . ' ' . ($p['cor'] ?? ''));
                                    $pJson = htmlspecialchars(json_encode([
                                        'id'         => $p['id'],
                                        'nome'       => $p['nome'],
                                        'marca'      => $p['marca']      ?? '',
                                        'cor'        => $p['cor']        ?? '',
                                        'categoria'  => $p['categoria'],
                                        'preco'      => $p['preco'],
                                        'quantidade' => $p['quantidade'],
                                        'imagem'     => $p['imagem']     ?? '',
                                    ]), ENT_QUOTES);
                                ?>
                                <tr data-searchable="<?= htmlspecialchars($searchable) ?>">
                                    <td>
                                        <div class="product-cell">
                                            <?php if (!empty($p['imagem'])): ?>
                                            <img class="product-thumb-img" src="<?= htmlspecialchars($p['imagem']) ?>" alt="">
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
                                    <td>
                                        <div class="actions-cell">
                                            <!-- Alterna visibilidade do produto na loja -->
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="acao" value="toggle_visivel">
                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                <button type="submit" class="btn-icon"
                                                    title="<?= ($p['visivel'] ?? 1) ? 'Ocultar da loja' : 'Tornar visível' ?>">
                                                    <i class="fa-solid <?= ($p['visivel'] ?? 1) ? 'fa-eye' : 'fa-eye-slash' ?>"
                                                        style="color:<?= ($p['visivel'] ?? 1) ? 'var(--green)' : 'var(--text-dim)' ?>"></i>
                                                </button>
                                            </form>
                                            <button class="btn-icon edit" title="Editar" onclick='openEditDrawer(<?= $pJson ?>)'>
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn-icon del" title="Excluir"
                                                onclick="openDeleteModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['nome']), ENT_QUOTES) ?>)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <!-- Estado vazio exibido via JS quando a busca não retorna nada -->
                                <tr id="emptyRow" style="display:none">
                                    <td colspan="5">
                                        <div class="estado-vazio">
                                            <i class="fa-solid fa-box-open"></i>
                                            <h4>Nenhum produto encontrado</h4>
                                            <p>Tente outro termo de pesquisa.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

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

            </div>

        </main>

    </div>

    <div class="drawer-overlay" id="drawerOverlay" onclick="closeEditDrawer()"></div>

    <!-- Drawer: editar produto -->
    <div class="drawer" id="editDrawer" role="dialog" aria-modal="true" aria-label="Editar produto">

        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Produto</h3>
            <button class="btn-close-drawer" onclick="closeEditDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="/pages/produtos.php" enctype="multipart/form-data" novalidate
            style="flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden">
            <input type="hidden" id="editId" name="id" value="">
            <input type="hidden" name="acao" value="editar">

            <div class="drawer-body">
                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editNome">Nome do produto</label>
                    <input type="text" id="editNome" name="nome" class="entrada-formulario" placeholder="Nome do produto" maxlength="70">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editMarca">Marca</label>
                    <input type="text" id="editMarca" name="marca" class="entrada-formulario"
                        placeholder="Ex: Nike, Adidas, Puma..." maxlength="100">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editCor">Cor</label>
                    <input type="text" id="editCor" name="cor" class="entrada-formulario"
                        placeholder="Ex: Preto, Branco, Azul..." maxlength="60">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editCategoria">Categoria</label>
                    <select id="editCategoria" name="categoria" class="selecao-formulario">
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

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editPreco">Preço (R$)</label>
                    <input type="number" id="editPreco" name="preco" class="entrada-formulario" placeholder="0.00" step="0.01" min="0.01" max="999999.99">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editEstoque">Quantidade em estoque</label>
                    <input type="number" id="editEstoque" name="quantidade" class="entrada-formulario" placeholder="0" min="0" max="99999">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario">Imagem do produto</label>
                    <div class="upload-area" id="editUploadArea">
                        <input type="file" id="editImagem" name="imagem" accept="image/jpeg,image/png,image/webp" style="display:none">
                        <div id="editUploadVazio">
                            <i class="fa-regular fa-image upload-icon"></i>
                            <span class="upload-titulo">Clique para adicionar imagem</span>
                            <span class="upload-dica">JPG, PNG ou WEBP — máx. 5 MB</span>
                        </div>
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

                <div class="grupo-formulario">
                    <label class="rotulo-formulario">Tags</label>
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

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editVisivel">Visibilidade na loja</label>
                    <select id="editVisivel" name="visivel" class="selecao-formulario">
                        <option value="1">Visível — aparece na loja</option>
                        <option value="0">Oculto — não aparece na loja</option>
                    </select>
                    <span class="form-hint">Produtos ocultos só o admin pode ver.</span>
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

    <!-- Modal: novo produto -->
    <div class="fundo-modal" id="productModalBackdrop" onclick="handleProductModalClick(event)">
        <div class="modal modal-rolavel" role="dialog" aria-modal="true" aria-label="Novo produto">

            <div class="cabecalho-modal">
                <h3><i class="fa-solid fa-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Novo Produto</h3>
                <button class="botao-fechar-modal" onclick="closeProductModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="novoProdutoForm" method="POST" action="" enctype="multipart/form-data" novalidate>
                <div class="corpo-modal">

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario">Imagem do produto</label>
                        <div class="upload-area" id="criarUploadArea">
                            <input type="file" id="criarImagem" name="imagem" accept="image/jpeg,image/png,image/webp" style="display:none">
                            <div id="criarUploadVazio">
                                <i class="fa-regular fa-image upload-icon"></i>
                                <span class="upload-titulo">Clique ou arraste uma imagem</span>
                                <span class="upload-dica">JPG, PNG ou WEBP — máx. 5 MB (opcional)</span>
                                <span class="upload-dica">Mínimo 300px — Máximo 4000px</span>
                            </div>
                            <div id="criarUploadPreview" style="display:none">
                                <img id="criarImgPreview" src="" alt="Preview">
                                <div class="upload-arquivo">
                                    <i class="fa-solid fa-circle-check" style="color:var(--green);flex-shrink:0"></i>
                                    <span id="criarUploadNome"></span>
                                    <button type="button" id="criarUploadRemover" aria-label="Remover imagem">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarNome">Nome *</label>
                        <input type="text" id="criarNome" name="nome" class="entrada-formulario"
                            placeholder="Nome do produto" maxlength="70" autocomplete="off">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarMarca">Marca</label>
                        <input type="text" id="criarMarca" name="marca" class="entrada-formulario"
                            placeholder="Ex: Nike, Adidas, Puma..." maxlength="100" autocomplete="off">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarCor">Cor</label>
                        <input type="text" id="criarCor" name="cor" class="entrada-formulario"
                            placeholder="Ex: Preto, Branco, Azul..." maxlength="60" autocomplete="off">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarCategoria">Categoria</label>
                        <select id="criarCategoria" name="categoria" class="selecao-formulario" required>
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

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarPreco">Preço (R$) *</label>
                        <input type="number" id="criarPreco" name="preco" class="entrada-formulario"
                            placeholder="0.00" step="0.01" min="1" max="9999.99" required autocomplete="off">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarEstoque">Quantidade em estoque</label>
                        <input type="number" id="criarEstoque" name="quantidade" class="entrada-formulario"
                            placeholder="0" min="0" max="9999" value="1" required autocomplete="off">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarDescricao">Descrição</label>
                        <textarea id="criarDescricao" name="descricao" class="entrada-formulario"
                            placeholder="Descrição do produto..." rows="3"></textarea>
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario">Tags</label>
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

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarVisivel">Visibilidade na loja</label>
                        <select id="criarVisivel" name="visivel" class="selecao-formulario">
                            <option value="1">Visível — aparece na loja</option>
                            <option value="0">Oculto — não aparece na loja</option>
                        </select>
                        <span class="form-hint">Produtos ocultos não são exibidos para clientes.</span>
                    </div>

                </div>

                <div class="rodape-modal">
                    <button type="button" class="botao-secundario" onclick="closeProductModal()">Cancelar</button>
                    <button type="submit" class="botao-primario">
                        <i class="fa-solid fa-plus"></i>
                        Salvar Produto
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Form oculto para excluir produto -->
    <form id="deleteProductForm" method="POST" action="/pages/produtos.php" style="display:none">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteProductId">
    </form>

    <!-- Modal: confirmação de exclusão -->
    <div class="fundo-modal" id="deleteModalBackdrop" onclick="handleDeleteModalClick(event)">
        <div class="modal" style="max-width:400px" onclick="event.stopPropagation()" role="dialog" aria-modal="true" aria-label="Confirmar exclusão">

            <div class="cabecalho-modal" style="border-bottom:none;padding-bottom:8px">
                <div style="flex:1"></div>
                <button class="botao-fechar-modal" onclick="closeDeleteModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="corpo-modal" style="align-items:center;text-align:center;gap:14px;padding-top:0">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);display:flex;align-items:center;justify-content:center;margin:0 auto">
                    <i class="fa-solid fa-trash" style="font-size:1.4rem;color:var(--red)"></i>
                </div>
                <div>
                    <h3 style="font-size:1.05rem;font-weight:700;color:var(--text-main);margin-bottom:8px">Excluir produto?</h3>
                    <p style="font-size:.85rem;color:var(--text-sub);line-height:1.65">
                        Você está prestes a excluir<br>
                        <strong id="deleteProductNome" style="color:var(--text-main)"></strong>.<br>
                        <span style="color:var(--red);font-size:.78rem;font-weight:500">
                            <i class="fa-solid fa-triangle-exclamation" style="margin-right:3px"></i>
                            Esta ação não pode ser desfeita.
                        </span>
                    </p>
                </div>
            </div>

            <div class="rodape-modal">
                <button type="button" class="botao-secundario" style="flex:1" onclick="closeDeleteModal()">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarDelete" onclick="confirmDeleteProduct()"
                    style="flex:1;padding:10px;background:var(--red);border:none;border-radius:var(--radius-sm);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all var(--transition)">
                    <i class="fa-solid fa-trash" style="margin-right:6px"></i>
                    Excluir
                </button>
            </div>

        </div>
    </div>

    <div class="sp-toast" id="spToast"
        <?php if ($flash): ?> data-flash-msg="<?= htmlspecialchars($flash['message']) ?>" data-flash-type="<?= $flash['type'] === 'success' ? 'success' : 'error' ?>" <?php endif; ?>>
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/produtos.js"></script>

</body>

</html>
