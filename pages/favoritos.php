<?php
session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'favoritos';
$page_title     = 'Favoritos';
$trilhaNavegacao     = [['label' => 'Favoritos']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// A tabela favoritos existe após rodar banco-migration.sql
$favoritos    = [];
$erroMigracao = false;
try {
    $stmt = $pdo->prepare("
        SELECT pr.id, pr.nome, pr.categoria, pr.preco, pr.quantidade,
               pr.descricao,
               COALESCE(pr.imagem, '') AS imagem
        FROM favoritos f
        JOIN produtos pr ON pr.id = f.produto_id
        WHERE f.usuario_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$usuario_logado['id']]);
    $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erroMigracao = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<?php $css_extra = ['loja.css']; require __DIR__ . '/../app/includes/head.php'; ?>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <?php if ($erroMigracao): ?>
                <div class="card" style="padding:40px;text-align:center">
                    <i class="fa-solid fa-database" style="font-size:2.5rem;color:var(--orange);margin-bottom:16px;display:block"></i>
                    <h3 style="color:var(--text-main);margin-bottom:8px">Migração do banco necessária</h3>
                    <p style="color:var(--text-dim);margin-bottom:16px">
                        A tabela de favoritos ainda não foi criada.<br>
                        Execute o arquivo <strong>banco-migration.sql</strong> no phpMyAdmin.
                    </p>
                    <a href="/pages/home.php" class="botao-primario" style="display:inline-flex">
                        <i class="fa-solid fa-store"></i> Ir para a loja
                    </a>
                </div>
            <?php else: ?>
                <div class="loja-hero">
                    <div class="loja-hero-text">
                        <h1>Favoritos</h1>
                        <p><?= count($favoritos) ?> produto<?= count($favoritos) !== 1 ? 's' : '' ?> favoritado<?= count($favoritos) !== 1 ? 's' : '' ?></p>
                    </div>
                </div>

                <?php if (empty($favoritos)): ?>
                    <div class="fav-empty-wrapper">

                        <div class="fav-empty-bg" aria-hidden="true">
                            <div class="fav-decor fd1"></div>
                            <div class="fav-decor fd2"></div>
                            <div class="fav-decor fd3"></div>
                        </div>

                        <div class="fav-empty-icon-wrap" aria-hidden="true">
                            <div class="fav-icon-glow"></div>
                            <div class="fav-icon-circle">
                                <i class="fa-solid fa-heart"></i>
                            </div>
                            <div class="fav-float fi-a"><i class="fa-solid fa-star"></i></div>
                            <div class="fav-float fi-b"><i class="fa-solid fa-bolt"></i></div>
                            <div class="fav-float fi-c"><i class="fa-solid fa-tag"></i></div>
                            <div class="fav-float fi-d"><i class="fa-solid fa-box-open"></i></div>
                        </div>

                        <h2 class="fav-empty-title">Sua lista de favoritos está vazia</h2>
                        <p class="fav-empty-subtitle">
                            Navegue pela loja e clique no <i class="fa-solid fa-heart" style="color:#f97316;font-size:.9em"></i> para salvar<br>
                            os produtos que você mais gosta.
                        </p>

                        <div class="fav-empty-actions">
                            <a href="/pages/home.php" class="btn-fav-primary">
                                <i class="fa-solid fa-store"></i>
                                Explorar produtos
                            </a>
                            <a href="/pages/carrinho.php" class="btn-fav-secondary">
                                <i class="fa-solid fa-cart-shopping"></i>
                                Ver carrinho
                            </a>
                        </div>

                        <div class="fav-empty-tips">
                            <div class="fav-tip">
                                <i class="fa-solid fa-heart"></i>
                                <span>Clique no coração em qualquer produto</span>
                            </div>
                            <div class="fav-tip-sep" aria-hidden="true"></div>
                            <div class="fav-tip">
                                <i class="fa-solid fa-bookmark"></i>
                                <span>Salve para comprar depois</span>
                            </div>
                            <div class="fav-tip-sep" aria-hidden="true"></div>
                            <div class="fav-tip">
                                <i class="fa-solid fa-bell"></i>
                                <span>Acompanhe preços e promoções</span>
                            </div>
                        </div>

                    </div>
                <?php else: ?>
                    <div class="produtos-grid">
                        <?php foreach ($favoritos as $p): ?>
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

                                    <form class="fav-form" method="POST" action="/app/controller/favoritosController.php" onclick="event.stopPropagation()">
                                        <input type="hidden" name="acao" value="toggle">
                                        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="redirect" value="/pages/favoritos.php">
                                        <button type="submit" class="btn-fav ativo" title="Remover dos favoritos">
                                            <i class="fa-solid fa-heart"></i>
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
                                    <form method="POST" action="/app/controller/carrinhoController.php" style="flex:2;display:flex;">
                                        <input type="hidden" name="acao" value="adicionar">
                                        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="quantidade" value="1">
                                        <input type="hidden" name="redirect" value="/pages/favoritos.php">
                                        <button type="submit" class="btn-add-cart" <?= (int)$p['quantidade'] === 0 ? 'disabled' : '' ?>>
                                            <i class="fa-solid fa-cart-shopping"></i>
                                            <?= (int)$p['quantidade'] === 0 ? 'Indisponível' : '+ Carrinho' ?>
                                        </button>
                                    </form>
                                </div>

                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

        </main>

    </div>

    <!-- Modal: detalhe do produto (mesmo do home.php) -->
    <div class="fundo-modal" id="detalheModalBackdrop" onclick="fecharDetalhe(event)">
        <div class="modal modal-produto-detalhe" role="dialog" aria-modal="true" onclick="event.stopPropagation()">

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

                    <div class="dprod-qtd-row">
                        <span class="dprod-qtd-label">Quantidade</span>
                        <div class="dprod-qtd-controles">
                            <button class="dprod-qtd-btn" id="qtdMenos" type="button" aria-label="Diminuir">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="dprod-qtd-num" id="qtdNum">1</span>
                            <button class="dprod-qtd-btn" id="qtdMais" type="button" aria-label="Aumentar">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="dprod-actions">
                        <button type="button" class="dprod-btn-fechar" onclick="closeDetalheModal()">
                            <i class="fa-solid fa-chevron-left"></i>
                            Voltar
                        </button>
                        <form id="formAddCart" method="POST" action="/app/controller/carrinhoController.php" style="flex:1;display:flex;">
                            <input type="hidden" name="acao" value="adicionar">
                            <input type="hidden" name="produto_id" id="cartProdutoId" value="">
                            <input type="hidden" name="quantidade" id="cartQuantidade" value="1">
                            <input type="hidden" name="redirect" value="/pages/favoritos.php">
                            <button type="submit" id="btnAdicionarCart" class="dprod-btn-cart">
                                <i class="fa-solid fa-cart-shopping"></i>
                                Adicionar ao Carrinho
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/includes/toast.php'; ?>

<?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

<script src="/assets/js/loja.js"></script>

</body>

</html>