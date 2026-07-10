<?php require_once __DIR__ . '/../app/controller/homeController.php'; ?>
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
    <title>Sprint Max — Loja</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/loja.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="main-wrapper">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="page-content">

            <!-- ── HERO: título + pesquisa ───────────────────────── -->
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

            <!-- ── FILTRO POR CATEGORIA ───────────────────────────── -->
            <div class="cat-filters">
                <button class="cat-btn active" data-cat="">Todos</button>
                <?php foreach ($categorias as $cat): ?>
                    <button class="cat-btn" data-cat="<?= htmlspecialchars($cat) ?>">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- ── GRID DE PRODUTOS ──────────────────────────────── -->
            <div class="produtos-grid" id="produtosGrid">

                <?php foreach ($produtos as $p):
                    $pJson = htmlspecialchars(json_encode([
                        'id'        => (int)   $p['id'],
                        'nome'      =>         $p['nome'],
                        'categoria' =>         $p['categoria'],
                        'preco'     => (float) $p['preco'],
                        'quantidade' => (int)   $p['quantidade'],
                        'descricao' =>         $p['descricao'] ?? '',
                        'status'    =>         $p['status'],
                        'imagem'    =>         $p['imagem'] ?? '',
                    ]), ENT_QUOTES);
                ?>
                    <article class="produto-card"
                        data-categoria="<?= htmlspecialchars($p['categoria']) ?>"
                        data-search="<?= htmlspecialchars(strtolower($p['nome'] . ' ' . $p['categoria'])) ?>"
                        onclick="verDetalhes(<?= $pJson ?>)">

                        <div class="produto-img">
                            <?php if (!empty($p['imagem'])): ?>
                                <img src="<?= htmlspecialchars($p['imagem']) ?>"
                                    alt="<?= htmlspecialchars($p['nome']) ?>">
                            <?php else: ?>
                                <div class="no-img"><i class="fa-solid fa-box"></i></div>
                            <?php endif; ?>

                            <!-- Tags do produto -->
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

                            <!-- Botão favoritar -->
                            <form class="fav-form" method="POST"
                                action="/app/controller/favoritosController.php"
                                onclick="event.stopPropagation()">
                                <input type="hidden" name="acao" value="toggle">
                                <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="redirect" value="/pages/home.php">
                                <button type="submit"
                                    class="btn-fav <?= in_array((int)$p['id'], $favoritoIds) ? 'ativo' : '' ?>"
                                    title="<?= in_array((int)$p['id'], $favoritoIds) ? 'Remover dos favoritos' : 'Favoritar' ?>">
                                    <i class="fa-<?= in_array((int)$p['id'], $favoritoIds) ? 'solid' : 'regular' ?> fa-heart"></i>
                                </button>
                            </form>

                            <?php if ($p['status'] === 'sem_estoque'): ?>
                                <div class="sem-estoque-overlay">Indisponível</div>
                            <?php endif; ?>
                        </div>

                        <div class="produto-info">
                            <span class="produto-cat"><?= htmlspecialchars($p['categoria']) ?></span>
                            <h3 class="produto-nome"><?= htmlspecialchars($p['nome']) ?></h3>
                            <div class="produto-preco">R$ <?= number_format($p['preco'], 2, ',', '.') ?></div>
                            <?php if ($p['status'] === 'sem_estoque'): ?>
                                <div class="produto-estoque sem">Indisponível</div>
                            <?php elseif ($p['status'] === 'baixo_estoque'): ?>
                                <div class="produto-estoque baixo">Apenas <?= (int)$p['quantidade'] ?> em estoque</div>
                            <?php else: ?>
                                <div class="produto-estoque"><?= (int)$p['quantidade'] ?> disponível(is)</div>
                            <?php endif; ?>
                        </div>

                        <div class="produto-actions" onclick="event.stopPropagation()">
                            <button class="btn-detalhe" onclick="verDetalhes(<?= $pJson ?>)">
                                <i class="fa-solid fa-eye"></i> Ver mais
                            </button>
                            <form method="POST" action="/app/controller/carrinhoController.php"
                                style="flex:2;display:flex;">
                                <input type="hidden" name="acao" value="adicionar">
                                <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                <input type="hidden" name="quantidade" value="1">
                                <input type="hidden" name="redirect" value="/pages/home.php">
                                <button type="submit" class="btn-add-cart"
                                    <?= $p['status'] === 'sem_estoque' ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <?= $p['status'] === 'sem_estoque' ? 'Indisponível' : '+ Carrinho' ?>
                                </button>
                            </form>
                        </div>

                    </article>
                <?php endforeach; ?>

                <!-- Estado vazio (exibido pelo JS se nada for encontrado) -->
                <div class="loja-empty" id="lojaEmpty" style="display:none">
                    <i class="fa-solid fa-box-open"></i>
                    <h4>Nenhum produto encontrado</h4>
                    <p>Tente outro termo ou categoria.</p>
                </div>

            </div><!-- /produtos-grid -->

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

    </div><!-- /main-wrapper -->


    <!-- ═══ MODAL — Detalhe do produto ═══════════════════════════ -->
    <div class="modal-backdrop" id="detalheModalBackdrop" onclick="fecharDetalhe(event)">
        <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Detalhe do produto"
            style="max-width:640px" onclick="event.stopPropagation()">

            <div class="modal-head">
                <h3 id="detalheTitulo" style="font-size:.95rem;font-weight:700"></h3>
                <button class="btn-close-modal" onclick="closeDetalheModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="detalhe-grid">

                    <!-- Imagem -->
                    <div class="detalhe-img">
                        <img id="detalheImg" src="" alt="" style="display:none">
                        <div id="detalheNoImg" class="no-img" style="display:none">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    </div>

                    <!-- Informações -->
                    <div class="detalhe-info">
                        <span class="detalhe-cat" id="detalheCat"></span>
                        <h2 class="detalhe-nome" id="detalheNome"></h2>
                        <div class="detalhe-preco" id="detalhePreco"></div>
                        <div class="detalhe-estoque-badge" id="detalheEstoque"></div>
                        <p class="detalhe-desc" id="detalheDesc"></p>

                        <!-- Quantidade -->
                        <div class="qtd-wrap">
                            <span class="qtd-label">Qtd.</span>
                            <div class="qtd-controles">
                                <button class="qtd-btn" id="qtdMenos" type="button">−</button>
                                <span class="qtd-num" id="qtdNum">1</span>
                                <button class="qtd-btn" id="qtdMais" type="button">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-ghost" onclick="closeDetalheModal()">Fechar</button>
                <form id="formAddCart" method="POST" action="/app/controller/carrinhoController.php"
                    style="flex:2;display:flex;">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" id="cartProdutoId" value="">
                    <input type="hidden" name="quantidade" id="cartQuantidade" value="1">
                    <input type="hidden" name="redirect" value="/pages/home.php">
                    <button type="submit" id="btnAdicionarCart" class="btn-primary" style="width:100%">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- ── Toast ──────────────────────────────────────────────── -->
    <div class="sp-toast" id="spToast">
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>

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

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script>
        /* sidebar toggle */
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

        /* profile dropdown */
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

        /* toast */
        function showToast(msg, type) {
            const toast = document.getElementById('spToast');
            const iconEl = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');
            toast.className = 'sp-toast ' + type;
            iconEl.className = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
            msgEl.textContent = msg;
            toast.classList.add('show');
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3800);
        }

        /* ── Filtro de categoria ──────────────────────────────── */
        var activeCategory = '';
        var searchQuery = '';

        function filterProdutos() {
            var cards = document.querySelectorAll('.produto-card');
            var visible = 0;
            cards.forEach(function(card) {
                var catMatch = !activeCategory || card.dataset.categoria === activeCategory;
                var srchMatch = !searchQuery || card.dataset.search.includes(searchQuery);
                var show = catMatch && srchMatch;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            document.getElementById('lojaEmpty').style.display = visible === 0 ? '' : 'none';
        }

        document.querySelectorAll('.cat-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cat-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                activeCategory = this.dataset.cat;
                filterProdutos();
            });
        });

        var searchTimer;
        document.getElementById('lojaSearch').addEventListener('input', function() {
            clearTimeout(searchTimer);
            var val = this.value.trim().toLowerCase();
            searchTimer = setTimeout(function() {
                searchQuery = val;
                filterProdutos();
            }, 220);
        });

        /* ── Modal de detalhe ─────────────────────────────────── */
        var qtdAtual = 1;
        var qtdMax = 99;

        function verDetalhes(p) {
            qtdAtual = 1;
            qtdMax = p.status === 'sem_estoque' ? 0 : Math.max(1, p.quantidade);

            document.getElementById('detalheTitulo').textContent = p.nome;
            document.getElementById('detalheCat').textContent = p.categoria;
            document.getElementById('detalheNome').textContent = p.nome;
            document.getElementById('detalhePreco').textContent = 'R$ ' + parseFloat(p.preco).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('detalheDesc').textContent = p.descricao || 'Sem descrição.';

            var estoqueEl = document.getElementById('detalheEstoque');
            if (p.status === 'sem_estoque') {
                estoqueEl.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:var(--red)"></i> Indisponível';
            } else if (p.status === 'baixo_estoque') {
                estoqueEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow)"></i> Apenas ' + p.quantidade + ' em estoque';
            } else {
                estoqueEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--green)"></i> ' + p.quantidade + ' disponível(is)';
            }

            var img = document.getElementById('detalheImg');
            var noImg = document.getElementById('detalheNoImg');
            if (p.imagem) {
                img.src = p.imagem;
                img.alt = p.nome;
                img.style.display = 'block';
                noImg.style.display = 'none';
            } else {
                img.style.display = 'none';
                noImg.style.display = 'flex';
            }

            document.getElementById('qtdNum').textContent = '1';
            document.getElementById('cartQuantidade').value = '1';
            document.getElementById('cartProdutoId').value = p.id;

            var btnCart = document.getElementById('btnAdicionarCart');
            if (p.status === 'sem_estoque') {
                btnCart.disabled = true;
                btnCart.innerHTML = 'Indisponível';
            } else {
                btnCart.disabled = false;
                btnCart.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> Adicionar ao Carrinho';
            }

            document.getElementById('detalheModalBackdrop').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeDetalheModal() {
            document.getElementById('detalheModalBackdrop').classList.remove('open');
            document.body.style.overflow = '';
        }

        function fecharDetalhe(e) {
            if (e.target === document.getElementById('detalheModalBackdrop')) closeDetalheModal();
        }

        /* Seletor de quantidade */
        document.getElementById('qtdMenos').addEventListener('click', function() {
            if (qtdAtual > 1) {
                qtdAtual--;
                document.getElementById('qtdNum').textContent = qtdAtual;
                document.getElementById('cartQuantidade').value = qtdAtual;
            }
        });

        document.getElementById('qtdMais').addEventListener('click', function() {
            if (qtdAtual < qtdMax) {
                qtdAtual++;
                document.getElementById('qtdNum').textContent = qtdAtual;
                document.getElementById('cartQuantidade').value = qtdAtual;
            }
        });

        /* ESC fecha modais */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetalheModal();
                if (typeof closeProfileModal === 'function') closeProfileModal();
            }
        });
    </script>

</body>

</html>