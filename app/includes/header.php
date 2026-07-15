<?php
$page_title     = $page_title  ?? 'Dashboard';
$trilhaNavegacao = $trilhaNavegacao  ?? [];
$usuario_logado = $usuario_logado ?? $_SESSION['user'] ?? [];

$avatar_url = !empty($usuario_logado['foto_perfil'])
    ? htmlspecialchars($usuario_logado['foto_perfil'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($usuario_logado['nome'] ?? 'U')
    . '&background=F97316&color=fff&bold=true&size=80';
$cargo_label = ($usuario_logado['tipo'] ?? '') === 'admin' ? 'Administrador' : 'Usuário';

// Garante conexão disponível (algumas páginas não incluem um controller)
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/conexao.php';
    $pdo = Connection::getConnection();
}

// Carrega as notificações do usuário logado (tabela existe após a migração)
$notifList     = [];
$totalNaoLidas = 0;
try {
    if (!empty($usuario_logado['id'])) {
        $stmtN = $pdo->prepare("
            SELECT id, titulo, mensagem, tipo, lida, created_at
            FROM notificacoes
            WHERE usuario_id = ?
            ORDER BY created_at DESC
            LIMIT 8
        ");
        $stmtN->execute([(int) $usuario_logado['id']]);
        $notifList     = $stmtN->fetchAll(PDO::FETCH_ASSOC);
        $totalNaoLidas = count(array_filter($notifList, fn($n) => !(int) $n['lida']));
    }
} catch (PDOException $e) { /* tabela pode não existir ainda */
}
?>

<header class="topbar">

    <div class="topbar-left">

        <button class="btn-toggle" id="sidebarToggle" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="topbar-title">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <?php if (!empty($trilhaNavegacao)): ?>
            <div class="breadcrumb">
                <a href="/pages/dashboard.php">Home</a>
                <?php foreach ($trilhaNavegacao as $crumb): ?>
                <span class="sep">›</span>
                <?php if (!empty($crumb['url'])): ?>
                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                <?php else: ?>
                <span class="current"><?= htmlspecialchars($crumb['label']) ?></span>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="topbar-right">

        <button id="themeToggle" class="theme-toggle" title="Ativar modo claro" aria-label="Ativar modo claro">
            <i class="fa-solid fa-moon  icon-dark"></i>
            <i class="fa-solid fa-sun   icon-light"></i>
        </button>

        <div class="notif-wrap" id="notifWrap">
            <button class="notif-btn" id="notifBtn" type="button" aria-label="Notificações">
                <i class="fa-solid fa-bell"></i>
                <?php if ($totalNaoLidas > 0): ?>
                <span class="notif-badge">
                    <?= $totalNaoLidas > 9 ? '9+' : $totalNaoLidas ?>
                </span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown" id="notifDropdown" role="menu">
                <div class="notif-head">
                    <span>Notificações</span>
                    <?php if ($totalNaoLidas > 0): ?>
                    <form method="POST" action="/app/controller/notificacoesController.php" style="display:inline">
                        <input type="hidden" name="acao" value="marcar_lidas">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>">
                        <button type="submit" class="notif-mark-all">Marcar como lidas</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="notif-list">
                    <?php if (empty($notifList)): ?>
                    <div class="notif-empty">
                        <i class="fa-regular fa-bell-slash"></i>
                        Nenhuma notificação
                    </div>
                    <?php else: ?>
                    <?php foreach ($notifList as $n): ?>
                    <?php
                        $icone = match ($n['tipo']) {
                            'sucesso' => 'fa-check',
                            'aviso'   => 'fa-triangle-exclamation',
                            'erro'    => 'fa-circle-exclamation',
                            default   => 'fa-circle-info',
                        };
                    ?>
                    <div class="notif-item <?= (int)$n['lida'] === 0 ? 'unread' : '' ?>">
                        <div class="notif-icon <?= htmlspecialchars($n['tipo']) ?>">
                            <i class="fa-solid <?= $icone ?>"></i>
                        </div>
                        <div class="notif-text">
                            <div class="notif-titulo"><?= htmlspecialchars($n['titulo']) ?></div>
                            <div class="notif-msg"><?= htmlspecialchars($n['mensagem']) ?></div>
                            <div class="notif-time">
                                <?= date('d/m H:i', strtotime($n['created_at'])) ?>
                            </div>
                        </div>
                        <?php if ((int)$n['lida'] === 0): ?>
                        <div class="notif-dot"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Perfil (dropdown controlado por painel.js) -->
        <div class="profile-wrap">
            <button class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                <img class="profile-avatar" src="<?= $avatar_url ?>"
                    alt="Avatar de <?= htmlspecialchars($usuario_logado['nome'] ?? '') ?>">
                <div class="profile-text">
                    <div class="p-name"><?= htmlspecialchars($usuario_logado['nome'] ?? 'Usuário') ?></div>
                    <div class="p-role"><?= $cargo_label ?></div>
                </div>
                <i class="fa-solid fa-chevron-down profile-chevron"></i>
            </button>

            <div class="profile-dropdown" id="profileDropdown" role="menu">
                <button class="dd-item" onclick="openProfileModal()">
                    <i class="fa-regular fa-user"></i>
                    Editar Perfil
                </button>
                <div class="dd-sep"></div>
                <a class="dd-item dd-danger" href="/auth/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Sair
                </a>
            </div>
        </div>

    </div>

</header>
