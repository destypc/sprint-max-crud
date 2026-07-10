<?php
session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';
require_once __DIR__ . '/../app/config/helpers.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'relatorios';
$page_title     = 'Logs';
$breadcrumb     = [['label' => 'Logs']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Logs do sistema (todas as ações)
$logs = [];
try {
    $stmtL = $pdo->query("
        SELECT l.acao, l.descricao, l.data,
               COALESCE(u.nome,  'Sistema') AS nome,
               COALESCE(u.email, '—')      AS email,
               COALESCE(u.tipo,  '—')      AS tipo
        FROM logs l
        LEFT JOIN usuarios u ON u.id = l.usuario_id
        ORDER BY l.data DESC
        LIMIT 50
    ");
    $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* tabela logs pode não existir */
}

// Contações rápidas
$totalLogs     = count($logs);
$loginsTotal   = count(array_filter($logs, fn($l) => $l['acao'] === 'login'));
$pedidosTotais = 0;
try {
    $pedidosTotais = (int) $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
} catch (PDOException $e) { /* tabela pedidos ainda não criada — rodar banco-migration.sql */
}

function logIconeRelatorio(string $acao): string
{
    return match ($acao) {
        'login'             => 'fa-right-to-bracket',
        'cadastro_usuario'  => 'fa-user-plus',
        'edicao_usuario'    => 'fa-user-pen',
        'cadastro_produto'  => 'fa-box',
        'edicao_produto'    => 'fa-pen',
        'exclusao_produto'  => 'fa-trash',
        'pedido_realizado'  => 'fa-bag-shopping',
        'status_pedido'     => 'fa-rotate',
        default             => 'fa-circle-info',
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
    <title>Sprint Max — Logs</title>
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

            <!-- ── HERO ─────────────────────────────────────────── -->
            <div class="dash-hero">
                <h1>Logs</h1>
                <p>Visualize o histórico de acessos e atividades do sistema.</p>
            </div>

            <!-- ── ÚLTIMOS ACESSOS ───────────────────────────────── -->
            <div class="dash-access">
                <div class="dash-panel-head">
                    <div>
                        <div class="dash-panel-title">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Log de Atividades
                        </div>
                        <div class="dash-panel-sub">
                            <?= $totalLogs ?> registro<?= $totalLogs !== 1 ? 's' : '' ?> recentes
                        </div>
                    </div>
                </div>
                <div class="access-table-wrap">
                    <table class="access-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Data / Hora</th>
                                <th>Ação</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-list"></i>
                                            <h4>Nenhum log registrado ainda</h4>
                                            <p>As atividades do sistema aparecerão aqui.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <div class="access-user">
                                                <img class="access-avatar"
                                                    src="https://ui-avatars.com/api/?name=<?= urlencode($log['nome']) ?>&background=F97316&color=fff&bold=true&size=80"
                                                    alt="<?= htmlspecialchars($log['nome']) ?>">
                                                <div>
                                                    <div class="access-name"><?= htmlspecialchars($log['nome']) ?></div>
                                                    <div class="access-email"><?= htmlspecialchars($log['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($log['tipo'] === 'admin'): ?>
                                                <span class="badge-tipo admin">Admin</span>
                                            <?php elseif ($log['tipo'] === 'usuario'): ?>
                                                <span class="badge-tipo usuario">Usuário</span>
                                            <?php else: ?>
                                                <span class="badge-tipo" style="background:rgba(100,116,139,.1);color:var(--text-dim)">Sistema</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:.82rem;color:var(--text-sub);white-space:nowrap">
                                            <?= date('d/m/Y H:i', strtotime($log['data'])) ?>
                                        </td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--text-sub)">
                                                <i class="fa-solid <?= logIconeRelatorio($log['acao']) ?>" style="color:var(--orange);font-size:.78rem;width:14px;text-align:center"></i>
                                                <?= htmlspecialchars($log['acao']) ?>
                                            </div>
                                        </td>
                                        <td style="font-size:.82rem;color:var(--text-sub);max-width:240px">
                                            <?= htmlspecialchars($log['descricao']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div><!-- /dash-access -->

        </main>

    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/script.js"></script>
</body>

</html>