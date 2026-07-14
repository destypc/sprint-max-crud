<?php
session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'relatorios';
$page_title     = 'Logs';
$breadcrumb     = [['label' => 'Logs']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Paginação
$por_pagina = 15;
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$offset     = ($pagina - 1) * $por_pagina;
$total_logs = 0;

// Logs do sistema (todas as ações)
$logs = [];
try {
    $total_logs = (int) $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();

    $stmtL = $pdo->prepare("
        SELECT l.acao, l.descricao, l.data,
               COALESCE(u.nome,  'Sistema') AS nome,
               COALESCE(u.email, '—')      AS email,
               COALESCE(u.tipo,  '—')      AS tipo
        FROM logs l
        LEFT JOIN usuarios u ON u.id = l.usuario_id
        ORDER BY l.data DESC
        LIMIT :lim OFFSET :off
    ");
    $stmtL->bindValue(':lim', $por_pagina, PDO::PARAM_INT);
    $stmtL->bindValue(':off', $offset,     PDO::PARAM_INT);
    $stmtL->execute();
    $logs = $stmtL->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* tabela logs pode não existir */
}

$total_paginas = $total_logs > 0 ? (int) ceil($total_logs / $por_pagina) : 1;
$pagina        = min($pagina, $total_paginas);
$inicio        = $total_logs > 0 ? $offset + 1 : 0;
$fim           = min($offset + $por_pagina, $total_logs);

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
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <!-- ── HERO ─────────────────────────────────────────── -->
            <div class="painel-destaque">
                <h1>Logs</h1>
                <p>Visualize o histórico de acessos e atividades do sistema.</p>
            </div>

            <!-- ── ÚLTIMOS ACESSOS ───────────────────────────────── -->
            <div class="dash-access">
                <div class="painel-cabecalho">
                    <div>
                        <div class="painel-titulo">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Log de Atividades
                        </div>
                        <div class="painel-subtitulo">
                            Mostrando <strong><?= $inicio ?>–<?= $fim ?></strong> de <strong><?= $total_logs ?></strong> registro<?= $total_logs !== 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>
                <div class="access-envoltorio-tabela">
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
                                        <div class="estado-vazio">
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

            <?php if ($total_paginas > 1): ?>
                <div class="pagination" style="margin-top:16px">
                    <div class="pagination-info">
                        Página <strong><?= $pagina ?></strong> de <strong><?= $total_paginas ?></strong>
                    </div>
                    <div class="pagination-btns">
                        <?php
                        $prevDisabled = $pagina <= 1 ? 'disabled' : '';
                        $prevHref     = '?pagina=' . ($pagina - 1);
                        ?>
                        <button class="page-btn" <?= $prevDisabled ?>
                            <?= !$prevDisabled ? "onclick=\"location.href='{$prevHref}'\"" : '' ?>>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <?php
                        $start = max(1, min($pagina - 2, $total_paginas - 4));
                        $end   = min($total_paginas, $start + 4);
                        for ($p = $start; $p <= $end; $p++):
                            $href   = '?pagina=' . $p;
                            $active = $p === $pagina ? 'active' : '';
                        ?>
                            <button class="page-btn <?= $active ?>"
                                <?= !$active ? "onclick=\"location.href='{$href}'\"" : '' ?>>
                                <?= $p ?>
                            </button>
                        <?php endfor; ?>

                        <?php
                        $nextDisabled = $pagina >= $total_paginas ? 'disabled' : '';
                        $nextHref     = '?pagina=' . ($pagina + 1);
                        ?>
                        <button class="page-btn" <?= $nextDisabled ?>
                            <?= !$nextDisabled ? "onclick=\"location.href='{$nextHref}'\"" : '' ?>>
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

        </main>

    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>