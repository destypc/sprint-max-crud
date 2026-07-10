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
$isAdmin        = $usuario_logado['tipo'] === 'admin';
$current_page   = 'pedidos';
$page_title     = $isAdmin ? 'Pedidos' : 'Meus Pedidos';
$breadcrumb     = [['label' => $page_title]];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Carregar pedidos (tabela existe após banco-migration.sql)
$pedidos      = [];
$itemsByOrder = [];
$erroMigracao = false;

try {
    if ($isAdmin) {
        $stmt = $pdo->query("
            SELECT p.id, p.total, p.status, p.created_at,
                   u.nome AS cliente, u.email AS cliente_email
            FROM pedidos p
            JOIN usuarios u ON u.id = p.usuario_id
            ORDER BY p.id DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT p.id, p.total, p.status, p.created_at,
                   u.nome AS cliente, u.email AS cliente_email
            FROM pedidos p
            JOIN usuarios u ON u.id = p.usuario_id
            WHERE p.usuario_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$usuario_logado['id']]);
    }
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($pedidos)) {
        $orderIds     = array_column($pedidos, 'id');
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        $stmtItens = $pdo->prepare("
            SELECT pi.pedido_id, pi.quantidade, pi.preco_unitario, pi.subtotal,
                   pr.nome AS produto_nome,
                   COALESCE(pr.imagem, '') AS produto_imagem
            FROM pedido_itens pi
            JOIN produtos pr ON pr.id = pi.produto_id
            WHERE pi.pedido_id IN ($placeholders)
        ");
        $stmtItens->execute($orderIds);

        foreach ($stmtItens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $itemsByOrder[(int)$item['pedido_id']][] = $item;
        }
    }
} catch (PDOException $e) {
    $erroMigracao = true;
}

// ── Helper: badge de status ──────────────────────────────────
function statusBadgePedido(string $status): string
{
    return match ($status) {
        'pendente'   => '<span class="badge badge-yellow">Pendente</span>',
        'preparando' => '<span class="badge badge-orange">Preparando</span>',
        'enviado'    => '<span class="badge badge-blue">Enviado</span>',
        'entregue'   => '<span class="badge badge-green">Entregue</span>',
        'cancelado'  => '<span class="badge badge-red">Cancelado</span>',
        default      => '<span class="badge">' . htmlspecialchars($status) . '</span>',
    };
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
    <title>Sprint Max — <?= htmlspecialchars($page_title) ?></title>
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

            <?php if ($erroMigracao): ?>
                <div class="card" style="padding:40px;text-align:center">
                    <i class="fa-solid fa-database" style="font-size:2.5rem;color:var(--orange);margin-bottom:16px;display:block"></i>
                    <h3 style="color:var(--text-main);margin-bottom:8px">Migração do banco necessária</h3>
                    <p style="color:var(--text-dim);margin-bottom:16px">
                        A tabela de pedidos ainda não foi criada.<br>
                        Execute o arquivo <strong>banco-migration.sql</strong> no phpMyAdmin.
                    </p>
                    <a href="/pages/dashboard.php" class="btn-primary" style="display:inline-flex">
                        <i class="fa-solid fa-house"></i> Voltar ao Dashboard
                    </a>
                </div>
            <?php elseif ($isAdmin): ?>
                <!-- ═══════════════════════════════════════════════ -->
                <!-- VISÃO ADMIN: tabela de todos os pedidos         -->
                <!-- ═══════════════════════════════════════════════ -->
                <div class="card">

                    <div class="card-header">
                        <div class="card-title">
                            <h2>
                                <i class="fa-solid fa-bag-shopping" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>
                                Pedidos
                            </h2>
                            <p><?= count($pedidos) ?> pedido<?= count($pedidos) !== 1 ? 's' : '' ?> registrado<?= count($pedidos) !== 1 ? 's' : '' ?></p>
                        </div>
                    </div>

                    <div class="card-body" style="padding-bottom:0">

                        <?php if (empty($pedidos)): ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-bag-shopping"></i>
                                <h4>Nenhum pedido registrado</h4>
                                <p>Os pedidos dos clientes aparecerão aqui.</p>
                            </div>
                        <?php else: ?>

                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Data</th>
                                            <th>Itens</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Salvar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $p): ?>
                                            <tr>
                                                <td style="font-weight:700;color:var(--text-main)">#<?= (int)$p['id'] ?></td>
                                                <td>
                                                    <div style="font-weight:600;color:var(--text-main);font-size:.85rem"><?= htmlspecialchars($p['cliente']) ?></div>
                                                    <div style="font-size:.73rem;color:var(--text-dim)"><?= htmlspecialchars($p['cliente_email']) ?></div>
                                                </td>
                                                <td style="font-size:.83rem;color:var(--text-sub)">
                                                    <?= date('d/m/Y', strtotime($p['created_at'])) ?><br>
                                                    <span style="font-size:.72rem;color:var(--text-dim)"><?= date('H:i', strtotime($p['created_at'])) ?></span>
                                                </td>
                                                <td style="color:var(--text-sub);font-size:.84rem">
                                                    <?= count($itemsByOrder[$p['id']] ?? []) ?> item<?= count($itemsByOrder[$p['id']] ?? []) !== 1 ? 's' : '' ?>
                                                </td>
                                                <td style="font-weight:700;color:var(--text-main)">
                                                    R$ <?= number_format((float)$p['total'], 2, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <?= statusBadgePedido($p['status']) ?>
                                                </td>
                                                <!-- Formulário para atualizar status -->
                                                <td>
                                                    <form method="POST" action="/app/controller/pedidoController.php"
                                                        style="display:flex;gap:6px;align-items:center">
                                                        <input type="hidden" name="acao" value="atualizar_status">
                                                        <input type="hidden" name="pedido_id" value="<?= (int)$p['id'] ?>">
                                                        <select name="status" class="form-select"
                                                            style="width:auto;padding:6px 10px;font-size:.8rem">
                                                            <option value="pendente" <?= $p['status'] === 'pendente'   ? 'selected' : '' ?>>Pendente</option>
                                                            <option value="preparando" <?= $p['status'] === 'preparando' ? 'selected' : '' ?>>Preparando</option>
                                                            <option value="enviado" <?= $p['status'] === 'enviado'    ? 'selected' : '' ?>>Enviado</option>
                                                            <option value="entregue" <?= $p['status'] === 'entregue'   ? 'selected' : '' ?>>Entregue</option>
                                                            <option value="cancelado" <?= $p['status'] === 'cancelado'  ? 'selected' : '' ?>>Cancelado</option>
                                                        </select>
                                                        <button type="submit" class="btn-icon edit" title="Salvar status">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            <?php else: ?>
                <!-- ═══════════════════════════════════════════════ -->
                <!-- VISÃO USUÁRIO: cards com detalhes dos pedidos   -->
                <!-- ═══════════════════════════════════════════════ -->

                <div class="dash-hero">
                    <h1>Meus Pedidos</h1>
                    <p>Acompanhe o status das suas compras.</p>
                </div>

                <?php if (empty($pedidos)): ?>

                    <div class="card">
                        <div class="cart-empty-state">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <h3>Você ainda não fez nenhum pedido</h3>
                            <p style="margin-bottom:20px">Explore a loja e faça seu primeiro pedido!</p>
                            <a href="/pages/home.php" class="btn-primary" style="display:inline-flex">
                                <i class="fa-solid fa-store"></i>
                                Ir para a loja
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <?php foreach ($pedidos as $p): ?>
                        <div class="pedido-card">

                            <!-- Cabeçalho do pedido -->
                            <div class="pedido-head">
                                <div>
                                    <div class="pedido-id">
                                        <i class="fa-solid fa-bag-shopping" style="color:var(--orange);font-size:.8rem;margin-right:5px"></i>
                                        Pedido #<?= (int)$p['id'] ?>
                                    </div>
                                    <div class="pedido-data">
                                        <?= date('d/m/Y \à\s H:i', strtotime($p['created_at'])) ?>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:12px">
                                    <?= statusBadgePedido($p['status']) ?>
                                    <div class="pedido-total-val">
                                        R$ <?= number_format((float)$p['total'], 2, ',', '.') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Itens do pedido -->
                            <div class="pedido-itens-lista">
                                <?php foreach ($itemsByOrder[$p['id']] ?? [] as $item): ?>
                                    <div class="pedido-item-row">
                                        <?php if (!empty($item['produto_imagem'])): ?>
                                            <img class="pedido-item-img"
                                                src="<?= htmlspecialchars($item['produto_imagem']) ?>"
                                                alt="<?= htmlspecialchars($item['produto_nome']) ?>">
                                        <?php else: ?>
                                            <div class="pedido-item-no-img">
                                                <i class="fa-solid fa-box"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="pedido-item-info">
                                            <div class="pedido-item-nome"><?= htmlspecialchars($item['produto_nome']) ?></div>
                                            <div class="pedido-item-qtd">
                                                <?= (int)$item['quantidade'] ?> × R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="pedido-item-sub">
                                            R$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($itemsByOrder[$p['id']])): ?>
                                    <div style="padding:14px 0;color:var(--text-dim);font-size:.84rem">
                                        Detalhes dos itens não disponíveis.
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

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