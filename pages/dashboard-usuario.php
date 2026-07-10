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
$current_page   = 'dashboard-usuario';
$page_title     = 'Meu Painel';
$breadcrumb     = [['label' => 'Meu Painel']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

$uid = (int) $usuario_logado['id'];

// Estatísticas do usuário (tabelas existem após banco-migration.sql)
$totalPedidos     = 0;
$totalGasto       = 0.0;
$pedidosPendentes = 0;
$ultimoPedido     = null;
$produtoFavorito  = null;
$ultimasPedidos   = [];
$itensPorPedido   = [];
$erroMigracao     = false;

try {
    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?");
    $s->execute([$uid]);
    $totalPedidos = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE usuario_id = ?");
    $s->execute([$uid]);
    $totalGasto = (float)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ? AND status = 'pendente'");
    $s->execute([$uid]);
    $pedidosPendentes = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT id, total, status, created_at FROM pedidos WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 1");
    $s->execute([$uid]);
    $ultimoPedido = $s->fetch(PDO::FETCH_ASSOC) ?: null;

    $s = $pdo->prepare("
        SELECT pr.nome, SUM(pi.quantidade) AS qtd
        FROM pedido_itens pi
        JOIN pedidos p  ON p.id  = pi.pedido_id
        JOIN produtos pr ON pr.id = pi.produto_id
        WHERE p.usuario_id = ?
        GROUP BY pr.id, pr.nome ORDER BY qtd DESC LIMIT 1
    ");
    $s->execute([$uid]);
    $produtoFavorito = $s->fetch(PDO::FETCH_ASSOC) ?: null;

    $s = $pdo->prepare("SELECT id, total, status, created_at FROM pedidos WHERE usuario_id = ? ORDER BY id DESC LIMIT 5");
    $s->execute([$uid]);
    $ultimasPedidos = $s->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($ultimasPedidos)) {
        $oids = array_column($ultimasPedidos, 'id');
        $ph   = implode(',', array_fill(0, count($oids), '?'));
        $s = $pdo->prepare("
            SELECT pi.pedido_id, pi.quantidade, pi.subtotal,
                   pr.nome AS produto_nome,
                   COALESCE(pr.imagem,'') AS produto_imagem
            FROM pedido_itens pi
            JOIN produtos pr ON pr.id = pi.produto_id
            WHERE pi.pedido_id IN ($ph)
        ");
        $s->execute($oids);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $it) {
            $itensPorPedido[(int)$it['pedido_id']][] = $it;
        }
    }
} catch (PDOException $e) {
    $erroMigracao = true;
}

function statusBadgeSimples(string $status): string
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
    <title>Sprint Max — Meu Painel</title>
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

            <!-- HERO -->
            <?php if ($erroMigracao): ?>
                <div class="card" style="padding:40px;text-align:center">
                    <i class="fa-solid fa-database" style="font-size:2.5rem;color:var(--orange);margin-bottom:16px;display:block"></i>
                    <h3 style="color:var(--text-main);margin-bottom:8px">Migração do banco necessária</h3>
                    <p style="color:var(--text-dim);margin-bottom:16px">
                        Execute o arquivo <strong>banco-migration.sql</strong> no phpMyAdmin<br>
                        para habilitar pedidos, favoritos e notificações.
                    </p>
                    <a href="/pages/home.php" class="btn-primary" style="display:inline-flex">
                        <i class="fa-solid fa-store"></i> Ir para a loja
                    </a>
                </div>
            <?php else: ?>
                <div class="dash-hero">
                    <h1>Meu Painel</h1>
                    <p>Olá, <?= htmlspecialchars($usuario_logado['nome']) ?>. Confira suas estatísticas.</p>
                </div>

                <!-- STAT CARDS -->
                <div class="dash-cards">

                    <div class="dash-stat">
                        <div class="dash-stat-top">
                            <span class="dash-stat-label">Total de Pedidos</span>
                            <div class="dash-stat-icon orange"><i class="fa-solid fa-bag-shopping"></i></div>
                        </div>
                        <div class="dash-stat-value"><?= $totalPedidos ?></div>
                        <div class="dash-stat-footer">
                            <span><a href="/pages/pedidos.php">Ver pedidos</a></span>
                        </div>
                    </div>

                    <div class="dash-stat">
                        <div class="dash-stat-top">
                            <span class="dash-stat-label">Total Gasto</span>
                            <div class="dash-stat-icon green"><i class="fa-solid fa-sack-dollar"></i></div>
                        </div>
                        <div class="dash-stat-value" style="font-size:1.6rem">
                            R$&nbsp;<?= number_format($totalGasto, 2, ',', '.') ?>
                        </div>
                        <div class="dash-stat-footer">
                            <span><?= $totalPedidos > 0 ? 'em ' . $totalPedidos . ' pedido(s)' : 'Sem compras ainda' ?></span>
                        </div>
                    </div>

                    <div class="dash-stat">
                        <div class="dash-stat-top">
                            <span class="dash-stat-label">Pedidos Pendentes</span>
                            <div class="dash-stat-icon <?= $pedidosPendentes > 0 ? 'orange' : 'green' ?>">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                        <div class="dash-stat-value"><?= $pedidosPendentes ?></div>
                        <div class="dash-stat-footer">
                            <span><?= $pedidosPendentes > 0 ? 'aguardando processamento' : 'Nenhum pendente' ?></span>
                        </div>
                    </div>

                    <div class="dash-stat">
                        <div class="dash-stat-top">
                            <span class="dash-stat-label">Produto Favorito</span>
                            <div class="dash-stat-icon purple"><i class="fa-solid fa-heart"></i></div>
                        </div>
                        <div class="dash-stat-value" style="font-size:1rem;line-height:1.3;margin-top:4px">
                            <?= $produtoFavorito ? htmlspecialchars($produtoFavorito['nome']) : '—' ?>
                        </div>
                        <div class="dash-stat-footer">
                            <span>
                                <?= $produtoFavorito
                                    ? 'comprado ' . $produtoFavorito['qtd'] . ' vez(es)'
                                    : 'Nenhuma compra ainda' ?>
                            </span>
                        </div>
                    </div>

                </div><!-- /dash-cards -->


                <!-- GRID: último pedido + últimas compras -->
                <div class="dash-grid">

                    <!-- Último Pedido -->
                    <div class="dash-panel">
                        <div class="dash-panel-head">
                            <div>
                                <div class="dash-panel-title">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    Último Pedido
                                </div>
                                <div class="dash-panel-sub">Sua compra mais recente</div>
                            </div>
                        </div>
                        <div class="dash-panel-body">

                            <?php if ($ultimoPedido): ?>
                                <div class="activity-list">

                                    <div class="activity-item" style="align-items:center">
                                        <div class="activity-dot orange">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </div>
                                        <div class="activity-text">
                                            <div class="activity-desc">Pedido #<?= (int)$ultimoPedido['id'] ?></div>
                                            <div class="activity-time">
                                                <?= date('d/m/Y \à\s H:i', strtotime($ultimoPedido['created_at'])) ?>
                                            </div>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0">
                                            <?= statusBadgeSimples($ultimoPedido['status']) ?>
                                            <div style="font-weight:700;font-size:.9rem;color:var(--text-main);margin-top:4px">
                                                R$ <?= number_format((float)$ultimoPedido['total'], 2, ',', '.') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php foreach ($itensPorPedido[$ultimoPedido['id']] ?? [] as $it): ?>
                                        <div class="activity-item" style="align-items:center">
                                            <?php if (!empty($it['produto_imagem'])): ?>
                                                <img src="<?= htmlspecialchars($it['produto_imagem']) ?>"
                                                    style="width:30px;height:30px;border-radius:6px;object-fit:cover;border:1px solid var(--border);flex-shrink:0"
                                                    alt="">
                                            <?php else: ?>
                                                <div class="activity-dot orange" style="background:var(--orange-subtle)">
                                                    <i class="fa-solid fa-box" style="color:var(--orange)"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="activity-text">
                                                <div class="activity-desc"><?= htmlspecialchars($it['produto_nome']) ?></div>
                                                <div class="activity-time"><?= (int)$it['quantidade'] ?> unidade(s)</div>
                                            </div>
                                            <div style="font-weight:600;font-size:.84rem;color:var(--text-main);flex-shrink:0">
                                                R$ <?= number_format((float)$it['subtotal'], 2, ',', '.') ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="activity-item" style="border-bottom:none;padding-bottom:0">
                                        <a href="/pages/pedidos.php" style="color:var(--orange);font-size:.82rem;font-weight:500">
                                            Ver todos os pedidos →
                                        </a>
                                    </div>

                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                    <h4>Nenhum pedido ainda</h4>
                                    <p><a href="/pages/home.php" style="color:var(--orange)">Ir para a loja</a></p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div><!-- /último pedido -->


                    <!-- Histórico de compras -->
                    <div class="dash-panel">
                        <div class="dash-panel-head">
                            <div>
                                <div class="dash-panel-title">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Histórico de Compras
                                </div>
                                <div class="dash-panel-sub">Seus últimos 5 pedidos</div>
                            </div>
                        </div>
                        <div class="dash-panel-body">
                            <div class="activity-list">

                                <?php if (!empty($ultimasPedidos)): ?>
                                    <?php foreach ($ultimasPedidos as $i => $p): ?>
                                        <div class="activity-item" style="align-items:center<?= $i === count($ultimasPedidos) - 1 ? ';border-bottom:none;padding-bottom:0' : '' ?>">
                                            <div class="activity-dot <?= $p['status'] === 'entregue' ? 'green' : ($p['status'] === 'cancelado' ? 'red' : 'orange') ?>">
                                                <i class="fa-solid fa-bag-shopping"></i>
                                            </div>
                                            <div class="activity-text">
                                                <div class="activity-desc">Pedido #<?= (int)$p['id'] ?></div>
                                                <div class="activity-time">
                                                    <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                                    &middot;
                                                    <?= count($itensPorPedido[$p['id']] ?? []) ?> item(s)
                                                </div>
                                            </div>
                                            <div style="text-align:right;flex-shrink:0">
                                                <?= statusBadgeSimples($p['status']) ?>
                                                <div style="font-size:.82rem;font-weight:700;color:var(--text-main);margin-top:3px">
                                                    R$ <?= number_format((float)$p['total'], 2, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="activity-item" style="border-bottom:none;padding-bottom:0">
                                        <div class="activity-text">
                                            <div class="activity-desc" style="color:var(--text-dim)">
                                                Você ainda não fez nenhuma compra.
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div><!-- /histórico -->

                </div><!-- /dash-grid -->

        </main>

    </div><!-- /main-wrapper -->
<?php endif; ?>

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