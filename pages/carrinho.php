<?php
session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';
require_once __DIR__ . '/../app/config/helpers.php';

$pdo = Connection::getConnection();

garantirSessaoValida($pdo);

$usuario_logado = $_SESSION['user'];
$current_page   = 'carrinho';
$page_title     = 'Carrinho';
$trilhaNavegacao     = [['label' => 'Carrinho']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Monta os itens do carrinho a partir da sessão, limitando pela quantidade em estoque
$itens     = [];
$total     = 0.0;
$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    $ids          = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, nome, preco, imagem, quantidade AS estoque
        FROM produtos
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $prod) {
        $qtd = (int) ($_SESSION['cart'][$prod['id']] ?? 0);
        $qtd = min($qtd, (int) $prod['estoque']);

        if ($qtd <= 0) {
            unset($_SESSION['cart'][$prod['id']]);
            continue;
        }

        $_SESSION['cart'][$prod['id']] = $qtd;

        $subtotal   = round((float)$prod['preco'] * $qtd, 2);
        $total     += $subtotal;
        $cartCount += $qtd;

        $itens[] = [
            'id'       => (int)   $prod['id'],
            'nome'     =>         $prod['nome'],
            'preco'    => (float) $prod['preco'],
            'imagem'   =>         $prod['imagem'] ?? '',
            'estoque'  => (int)   $prod['estoque'],
            'qtd'      =>         $qtd,
            'subtotal' =>         $subtotal,
        ];
    }
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

            <?php if (empty($itens)): ?>
            <div class="cart-empty-wrapper">

                <div class="empty-bg-decor" aria-hidden="true">
                    <div class="decor-circle c1"></div>
                    <div class="decor-circle c2"></div>
                    <div class="decor-circle c3"></div>
                </div>

                <div class="empty-icon-area" aria-hidden="true">
                    <div class="empty-cart-glow"></div>
                    <div class="empty-cart-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div class="float-item fi-1"><i class="fa-solid fa-box-open"></i></div>
                    <div class="float-item fi-2"><i class="fa-solid fa-tag"></i></div>
                    <div class="float-item fi-3"><i class="fa-solid fa-star"></i></div>
                    <div class="float-item fi-4"><i class="fa-solid fa-percent"></i></div>
                </div>

                <h2 class="empty-title">Seu carrinho está vazio</h2>
                <p class="empty-subtitle">
                    Você ainda não adicionou nenhum produto.<br>
                    Explore a loja e encontre algo incrível!
                </p>

                <div class="empty-actions">
                    <a href="/pages/home.php" class="btn-empty-primary">
                        <i class="fa-solid fa-store"></i>
                        Explorar produtos
                    </a>
                    <a href="/pages/favoritos.php" class="btn-empty-secondary">
                        <i class="fa-solid fa-heart"></i>
                        Ver favoritos
                    </a>
                </div>

                <div class="empty-trust">
                    <div class="trust-badge">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Entrega rápida</span>
                    </div>
                    <div class="trust-sep" aria-hidden="true"></div>
                    <div class="trust-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Compra segura</span>
                    </div>
                    <div class="trust-sep" aria-hidden="true"></div>
                    <div class="trust-badge">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        <span>Devolução fácil</span>
                    </div>
                </div>

            </div>
            <?php else: ?>
            <div class="cart-layout">

                <!-- Itens do carrinho -->
                <div class="card">

                    <div class="card-header">
                        <div class="card-title">
                            <h2>
                                <i class="fa-solid fa-cart-shopping" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>
                                Meu Carrinho
                            </h2>
                            <p><?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?> selecionado<?= $cartCount !== 1 ? 's' : '' ?></p>
                        </div>
                    </div>

                    <div class="envoltorio-tabela">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Preço unit.</th>
                                    <th>Quantidade</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-item-cell">
                                            <?php if (!empty($item['imagem'])): ?>
                                            <img class="cart-item-img" src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                                            <?php else: ?>
                                            <div class="cart-item-no-img">
                                                <i class="fa-solid fa-box"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="cart-item-nome"><?= htmlspecialchars($item['nome']) ?></div>
                                                <?php if ($item['qtd'] >= $item['estoque']): ?>
                                                <div style="font-size:.7rem;color:var(--yellow);margin-top:2px">
                                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                                    Estoque máximo
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="color:var(--text-sub);font-size:.875rem">
                                        R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                                    </td>

                                    <td>
                                        <div style="display:flex;align-items:center;gap:6px">
                                            <form method="POST" action="/app/controller/carrinhoController.php" style="display:inline">
                                                <input type="hidden" name="acao" value="atualizar">
                                                <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                <button type="submit" name="quantidade" value="<?= $item['qtd'] - 1 ?>" class="qtd-btn"
                                                    title="<?= $item['qtd'] <= 1 ? 'Remover item' : 'Diminuir' ?>">
                                                    <?= $item['qtd'] <= 1
                                                        ? '<i class="fa-solid fa-trash" style="font-size:.75rem;color:var(--red)"></i>'
                                                        : '−' ?>
                                                </button>
                                            </form>

                                            <span class="qtd-num"><?= $item['qtd'] ?></span>

                                            <form method="POST" action="/app/controller/carrinhoController.php" style="display:inline">
                                                <input type="hidden" name="acao" value="atualizar">
                                                <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                <button type="submit" name="quantidade" value="<?= $item['qtd'] + 1 ?>" class="qtd-btn"
                                                    <?= $item['qtd'] >= $item['estoque'] ? 'disabled' : '' ?>>+</button>
                                            </form>
                                        </div>
                                    </td>

                                    <td style="font-weight:700;color:var(--text-main);font-size:.9rem">
                                        R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                    </td>

                                    <td>
                                        <form id="formRemoverCarrinho<?= (int)$item['id'] ?>" method="POST" action="/app/controller/carrinhoController.php">
                                            <input type="hidden" name="acao" value="remover">
                                            <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="redirect" value="/pages/carrinho.php">
                                            <button type="button" class="btn-icon del" title="Remover do carrinho"
                                                onclick="abrirModalExclusao({titulo:'Remover do carrinho?', mensagem:'Deseja remover do carrinho o item', alvo:'<?= htmlspecialchars(addslashes($item['nome'])) ?>', formId:'formRemoverCarrinho<?= (int)$item['id'] ?>'})">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="padding:16px 26px;border-top:1px solid var(--border)">
                        <a href="/pages/home.php"
                            style="display:inline-flex;align-items:center;gap:6px;
                                  font-size:.82rem;color:var(--text-dim);transition:color .2s"
                            onmouseover="this.style.color='var(--orange)'"
                            onmouseout="this.style.color='var(--text-dim)'">
                            <i class="fa-solid fa-arrow-left"></i>
                            Continuar comprando
                        </a>
                    </div>

                </div>

                <!-- Resumo do pedido -->
                <div class="cart-summary-sticky">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 style="font-size:.98rem">Resumo do Pedido</h2>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="summary-line">
                                <span>Subtotal (<?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?>)</span>
                                <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Frete</span>
                                <span style="color:var(--green);font-weight:600">Grátis</span>
                            </div>

                            <div class="summary-total-line">
                                <span>Total</span>
                                <span style="color:var(--orange)">
                                    R$ <?= number_format($total, 2, ',', '.') ?>
                                </span>
                            </div>

                            <!-- Finalizar compra (tratado em pedidoController.php) -->
                            <form method="POST" action="/app/controller/pedidoController.php">
                                <input type="hidden" name="acao" value="finalizar">
                                <button type="submit" class="botao-primario" style="width:100%;justify-content:center;padding:12px">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                    Finalizar Compra
                                </button>
                            </form>

                            <div style="text-align:center;margin-top:12px;font-size:.74rem;color:var(--text-dim)">
                                <i class="fa-solid fa-lock" style="color:var(--green)"></i>
                                Compra segura e protegida
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <?php endif; ?>

        </main>

    </div>

    <?php require __DIR__ . '/../app/includes/toast.php'; ?>

    <!-- Modal reutilizável de confirmação de exclusão -->
    <?php require_once __DIR__ . '/../app/includes/modal-exclusao.php'; ?>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>
