<?php require_once __DIR__ . '/../app/controller/homeController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/../app/includes/theme-init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Loja</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/loja.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <div class="loja-hero">
                <div class="loja-hero-text">
                    <h1>Loja</h1>
                    <p>Encontre os melhores produtos de tecnologia.</p>
                </div>
                <div class="loja-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="lojaSearch" placeholder="Pesquisar produto..." autocomplete="off">
                </div>
            </div>

            <div class="cat-filters">
                <button class="cat-btn active" data-cat="">Todos</button>
                <?php foreach ($categorias as $cat): ?>
                <button class="cat-btn" data-cat="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="produtos-grid" id="produtosGrid">

                <?php foreach ($produtos as $p): ?>
                <?php
                    $pJson = htmlspecialchars(json_encode([
                        'id'         => (int)   $p['id'],
                        'nome'       =>         $p['nome'],
                        'categoria'  =>         $p['categoria'],
                        'preco'      => (float) $p['preco'],
                        'quantidade' => (int)   $p['quantidade'],
                        'descricao'  =>         $p['descricao'] ?? '',
                        'imagem'     =>         $p['imagem'] ?? '',
                    ]), ENT_QUOTES);
                ?>
                <article class="produto-card"
                    data-categoria="<?= htmlspecialchars($p['categoria']) ?>"
                    data-search="<?= htmlspecialchars(strtolower($p['nome'] . ' ' . $p['categoria'])) ?>"
                    onclick="verDetalhes(<?= $pJson ?>)">

                    <div class="produto-img">
                        <?php if (!empty($p['imagem'])): ?>
                        <img src="<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                        <?php else: ?>
                        <div class="no-img"><i class="fa-solid fa-box"></i></div>
                        <?php endif; ?>

                        <?php if (!empty($p['tags'])): ?>
                        <div class="produto-tags">
                            <?php
                            $tagCores = ['Promoção' => 'promo', 'Oferta' => 'oferta', 'Lançamento' => 'novo', 'Novidade' => 'novo'];
                            foreach (array_slice(explode(',', $p['tags']), 0, 2) as $tag):
                                $tag = trim($tag);
                                if ($tag === '') continue;
                                $cls = $tagCores[$tag] ?? '';
                            ?>
                            <span class="produto-tag-badge <?= $cls ?>"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <form class="fav-form" method="POST" action="/app/controller/favoritosController.php" onclick="event.stopPropagation()">
                            <input type="hidden" name="acao" value="toggle">
                            <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="redirect" value="/pages/home.php">
                            <button type="submit"
                                class="btn-fav <?= in_array((int)$p['id'], $ids_favoritos) ? 'ativo' : '' ?>"
                                title="<?= in_array((int)$p['id'], $ids_favoritos) ? 'Remover dos favoritos' : 'Favoritar' ?>">
                                <i class="fa-<?= in_array((int)$p['id'], $ids_favoritos) ? 'solid' : 'regular' ?> fa-heart"></i>
                            </button>
                        </form>

                        <?php if ((int)$p['quantidade'] === 0): ?>
                        <div class="sem-estoque-overlay">Indisponível</div>
                        <?php endif; ?>
                    </div>

                    <div class="produto-info">
                        <span class="produto-cat"><?= htmlspecialchars($p['categoria']) ?></span>
                        <h3 class="produto-nome"><?= htmlspecialchars($p['nome']) ?></h3>
                        <div class="produto-preco">R$ <?= number_format($p['preco'], 2, ',', '.') ?></div>
                        <?php if ((int)$p['quantidade'] === 0): ?>
                        <div class="produto-estoque sem">Indisponível</div>
                        <?php elseif ((int)$p['quantidade'] <= 5): ?>
                        <div class="produto-estoque baixo">Apenas <?= (int)$p['quantidade'] ?> em estoque</div>
                        <?php else: ?>
                        <div class="produto-estoque"><?= (int)$p['quantidade'] ?> disponível(is)</div>
                        <?php endif; ?>
                    </div>

                    <div class="produto-actions" onclick="event.stopPropagation()">
                        <button class="btn-detalhe" onclick="verDetalhes(<?= $pJson ?>)">
                            <i class="fa-solid fa-eye"></i> Ver mais
                        </button>
                        <?php if (($usuario_logado['tipo'] ?? '') !== 'admin'): ?>
                        <form method="POST" action="/app/controller/carrinhoController.php" style="flex:2;display:flex;">
                            <input type="hidden" name="acao" value="adicionar">
                            <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="quantidade" value="1">
                            <input type="hidden" name="redirect" value="/pages/home.php">
                            <button type="submit" class="btn-add-cart" <?= (int)$p['quantidade'] === 0 ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-cart-shopping"></i>
                                <?= (int)$p['quantidade'] === 0 ? 'Indisponível' : '+ Carrinho' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                </article>
                <?php endforeach; ?>

                <!-- Estado vazio exibido via JS quando a busca não retorna nada -->
                <div class="loja-empty" id="lojaEmpty" style="display:none">
                    <i class="fa-solid fa-box-open"></i>
                    <h4>Nenhum produto encontrado</h4>
                    <p>Tente outro termo ou categoria.</p>
                </div>

            </div>

            <?php if (empty($produtos)): ?>
            <div class="loja-empty">
                <i class="fa-solid fa-box-open"></i>
                <h4>Nenhum produto cadastrado ainda</h4>
                <p>
                    <?php if ($_SESSION['user']['tipo'] === 'admin'): ?>
                    <a href="/pages/produtos.php" style="color:var(--orange)">Cadastre o primeiro produto</a>
                    <?php else: ?>
                    Aguarde novos produtos em breve.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

        </main>

    </div>

    <!-- Modal: detalhe do produto -->
    <div class="fundo-modal" id="detalheModalBackdrop" onclick="fecharDetalhe(event)">
        <div class="modal modal-produto-detalhe" role="dialog" aria-modal="true" aria-label="Detalhe do produto"
            onclick="event.stopPropagation()">

            <button class="btn-close-detalhe" onclick="closeDetalheModal()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="dprod-layout">

                <div class="dprod-img-col">
                    <img id="detalheImg" src="" alt="" style="display:none">
                    <div id="detalheNoImg" class="dprod-no-img" style="display:none">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <span class="dprod-cat-overlay" id="detalheCat"></span>
                </div>

                <div class="dprod-info-col">
                    <h2 class="dprod-nome" id="detalheNome"></h2>
                    <div class="dprod-preco" id="detalhePreco"></div>
                    <div class="dprod-estoque-pill" id="detalheEstoque"></div>

                    <div class="dprod-sep"></div>

                    <p class="dprod-desc" id="detalheDesc"></p>

                    <div class="dprod-actions">
                        <button type="button" class="dprod-btn-fechar" onclick="closeDetalheModal()">
                            <i class="fa-solid fa-chevron-left"></i>
                            Voltar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="sp-toast" id="spToast"
        <?php if ($mensagem_flash): ?> data-flash-msg="<?= htmlspecialchars($mensagem_flash['message']) ?>" data-flash-type="<?= $mensagem_flash['type'] === 'success' ? 'success' : 'error' ?>" <?php endif; ?>>
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/loja.js"></script>

</body>

</html>
