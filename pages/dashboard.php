<?php
require_once __DIR__ . '/../app/controller/dashboardController.php';
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
    <title>Sprint Max — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="main-wrapper">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="page-content">

            <!-- HERO -->
            <div class="dash-hero">
                <h1>Dashboard</h1>
                <p>Bem-vindo de volta, <?= htmlspecialchars($usuario_logado['nome'] ?? 'Administrador') ?>.</p>
            </div>

            <!-- STAT CARDS -->
            <div class="dash-cards">

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Usuários</span>
                        <div class="dash-stat-icon orange"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $totalUsuarios ?></div>
                    <div class="dash-stat-footer">
                        <span><a href="/pages/usuarios.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Produtos</span>
                        <div class="dash-stat-icon purple"><i class="fa-solid fa-box"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $totalProdutos ?></div>
                    <div class="dash-stat-footer">
                        <span><a href="/pages/produtos.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Pedidos</span>
                        <div class="dash-stat-icon green"><i class="fa-solid fa-bag-shopping"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $totalPedidos ?></div>
                    <div class="dash-stat-footer">
                        <span><a href="/pages/pedidos.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Administradores</span>
                        <div class="dash-stat-icon blue"><i class="fa-solid fa-user-shield"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $totalAdmins ?></div>
                    <div class="dash-stat-footer">
                        <span><a href="/pages/usuarios.php">Gerenciar</a></span>
                    </div>
                </div>

            </div><!-- /dash-cards -->

            <!-- GRID: VENDAS RECENTES + ATIVIDADES -->
            <div class="dash-grid">

                <!-- Pedidos Recentes -->
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <div>
                            <div class="dash-panel-title">
                                <i class="fa-solid fa-bag-shopping"></i>
                                Pedidos Recentes
                            </div>
                            <div class="dash-panel-sub">As últimas compras registradas</div>
                        </div>
                    </div>
                    <div class="dash-panel-body">
                        <div class="activity-list">

                            <?php if (!empty($pedidosRecentes)): ?>
                                <?php foreach ($pedidosRecentes as $i => $pr): ?>
                                    <div class="activity-item" style="justify-content:space-between;align-items:center<?= $i === count($pedidosRecentes) - 1 ? ';border-bottom:none;padding-bottom:0' : '' ?>">
                                        <div class="activity-dot <?= pedidoDotCor($pr['status']) ?>">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </div>
                                        <div class="activity-text">
                                            <div class="activity-desc">Pedido #<?= (int)$pr['id'] ?></div>
                                            <div class="activity-time"><?= htmlspecialchars($pr['cliente']) ?> &middot; <?= timeAgo($pr['created_at']) ?></div>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0;margin-left:12px">
                                            <div style="font-size:.88rem;font-weight:700;color:var(--text-main)">R$&nbsp;<?= number_format((float)$pr['total'], 2, ',', '.') ?></div>
                                            <?= pedidoStatusBadge($pr['status']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item" style="border-bottom:none;padding-bottom:0">
                                    <div class="activity-text">
                                        <div class="activity-desc" style="color:var(--text-dim)">Nenhum pedido registrado ainda.</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Atividades Recentes -->
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <div>
                            <div class="dash-panel-title">
                                <i class="fa-solid fa-bell"></i>
                                Atividades Recentes
                            </div>
                            <div class="dash-panel-sub">As últimas atividades no sistema</div>
                        </div>
                    </div>
                    <div class="dash-panel-body">
                        <div class="activity-list">

                            <?php if (!empty($logsRecentes)): ?>
                                <?php foreach ($logsRecentes as $i => $log):
                                    [$cor, $icone] = logIcone($log['acao']);
                                ?>
                                    <div class="activity-item<?= $i === count($logsRecentes) - 1 ? ' style="border-bottom:none;padding-bottom:0"' : '' ?>">
                                        <div class="activity-dot <?= $cor ?>"><i class="fa-solid <?= $icone ?>"></i></div>
                                        <div class="activity-text">
                                            <div class="activity-desc"><?= htmlspecialchars($log['descricao']) ?></div>
                                            <div class="activity-time"><?= timeAgo($log['data']) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-item" style="border-bottom:none;padding-bottom:0">
                                    <div class="activity-text">
                                        <div class="activity-desc" style="color:var(--text-dim)">Nenhuma atividade registrada ainda.</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div><!-- /dash-grid -->

        </main><!-- /page-content -->

    </div><!-- /main-wrapper -->

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/script.js"></script>
</body>

</html>