<?php
session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';
require_once __DIR__ . '/../app/config/helpers.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'carrinho';
$page_title     = 'Carrinho';
$breadcrumb     = [['label' => 'Carrinho']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Carregar produtos do carrinho ────────────────────────
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
        $qtd = min($qtd, (int) $prod['estoque']); // limita pelo estoque atual

        if ($qtd <= 0) {
            unset($_SESSION['cart'][$prod['id']]);
            continue;
        }

        // Garante que a sessão reflete a quantidade correta
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
    <title>Sprint Max — Carrinho</title>
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

            <?php if (empty($itens)): ?>

                <!-- ── CARRINHO VAZIO ──────────────────────────── -->
                <div class="card">
                    <div class="cart-empty-state">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <h3>Seu carrinho está vazio</h3>
                        <p style="margin-bottom:20px">Adicione produtos para continuar.</p>
                        <a href="/pages/home.php" class="btn-primary" style="display:inline-flex">
                            <i class="fa-solid fa-store"></i>
                            Ver produtos
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <!-- ── LAYOUT: ITENS + RESUMO ─────────────────── -->
                <div class="cart-layout">

                    <!-- ── ITENS DO CARRINHO ──────────────────── -->
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

                        <div class="table-wrap">
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
                                            <!-- Produto -->
                                            <td>
                                                <div class="cart-item-cell">
                                                    <?php if (!empty($item['imagem'])): ?>
                                                        <img class="cart-item-img"
                                                            src="<?= htmlspecialchars($item['imagem']) ?>"
                                                            alt="<?= htmlspecialchars($item['nome']) ?>">
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

                                            <!-- Preço -->
                                            <td style="color:var(--text-sub);font-size:.875rem">
                                                R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                                            </td>

                                            <!-- Quantidade: botões − e + com forms separadas -->
                                            <td>
                                                <div style="display:flex;align-items:center;gap:6px">
                                                    <form method="POST"
                                                        action="/app/controller/carrinhoController.php"
                                                        style="display:inline">
                                                        <input type="hidden" name="acao" value="atualizar">
                                                        <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                        <button type="submit"
                                                            name="quantidade"
                                                            value="<?= $item['qtd'] - 1 ?>"
                                                            class="qtd-btn"
                                                            title="<?= $item['qtd'] <= 1 ? 'Remover item' : 'Diminuir' ?>">
                                                            <?= $item['qtd'] <= 1
                                                                ? '<i class="fa-solid fa-trash" style="font-size:.75rem;color:var(--red)"></i>'
                                                                : '−' ?>
                                                        </button>
                                                    </form>

                                                    <span class="qtd-num"><?= $item['qtd'] ?></span>

                                                    <form method="POST"
                                                        action="/app/controller/carrinhoController.php"
                                                        style="display:inline">
                                                        <input type="hidden" name="acao" value="atualizar">
                                                        <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                        <button type="submit"
                                                            name="quantidade"
                                                            value="<?= $item['qtd'] + 1 ?>"
                                                            class="qtd-btn"
                                                            <?= $item['qtd'] >= $item['estoque'] ? 'disabled' : '' ?>>+</button>
                                                    </form>
                                                </div>
                                            </td>

                                            <!-- Subtotal -->
                                            <td style="font-weight:700;color:var(--text-main);font-size:.9rem">
                                                R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                            </td>

                                            <!-- Remover -->
                                            <td>
                                                <form method="POST" action="/app/controller/carrinhoController.php">
                                                    <input type="hidden" name="acao" value="remover">
                                                    <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                                    <input type="hidden" name="redirect" value="/pages/carrinho.php">
                                                    <button type="submit"
                                                        class="btn-icon del"
                                                        title="Remover do carrinho"
                                                        onclick="return confirm('Remover <?= htmlspecialchars(addslashes($item['nome'])) ?> do carrinho?')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Rodapé do card: continuar comprando -->
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

                    </div><!-- /card itens -->


                    <!-- ── RESUMO DO PEDIDO ─────────────────── -->
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

                                <!-- Botão Finalizar Compra (handler criado na Etapa 5) -->
                                <form method="POST" action="/app/controller/pedidoController.php">
                                    <input type="hidden" name="acao" value="finalizar">
                                    <button type="submit" class="btn-primary"
                                        style="width:100%;justify-content:center;padding:12px">
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
                    </div><!-- /resumo -->

                </div><!-- /cart-layout -->

            <?php endif; ?>

        </main>

    </div><!-- /main-wrapper -->


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
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');
            toast.className = 'sp-toast ' + type;
            icon.className = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
            msgEl.textContent = msg;
            toast.classList.add('show');
            setTimeout(function() {
                toast.classList.remove('show');
            }, 3800);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && typeof closeProfileModal === 'function') closeProfileModal();
        });
    </script>

</body>

</html>