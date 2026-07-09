<?php

/**
 * Topbar — Sprint Max Dashboard
 * Variáveis esperadas:
 *   $usuario_logado  — array da sessão ($_SESSION['user'])
 *   $page_title      — título da página (string)
 *   $breadcrumb      — array de ['label' => '...', 'url' => '...'] (opcional)
 */
$page_title  = $page_title  ?? 'Dashboard';
$breadcrumb  = $breadcrumb  ?? [];
$usuario_logado = $usuario_logado ?? $_SESSION['user'] ?? [];

$avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($usuario_logado['nome'] ?? 'U')
    . '&background=F97316&color=fff&bold=true&size=80';
$cargo_label = ($usuario_logado['tipo'] ?? '') === 'admin' ? 'Administrador' : 'Usuário';
?>

<header class="topbar">

    <!-- Esquerda -->
    <div class="topbar-left">

        <!-- Toggle sidebar (mobile) -->
        <button class="btn-toggle" id="sidebarToggle" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Título + Breadcrumb -->
        <div class="topbar-title">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            <?php if (!empty($breadcrumb)): ?>
                <div class="breadcrumb">
                    <a href="/pages/dashboard.php">Home</a>
                    <?php foreach ($breadcrumb as $crumb): ?>
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

    <!-- Direita -->
    <div class="topbar-right">

        <!-- Profile card -->
        <div class="profile-wrap">
            <button class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                <img class="profile-avatar"
                    src="<?= $avatar_url ?>"
                    alt="Avatar de <?= htmlspecialchars($usuario_logado['nome'] ?? '') ?>">
                <div class="profile-text">
                    <div class="p-name"><?= htmlspecialchars($usuario_logado['nome'] ?? 'Usuário') ?></div>
                    <div class="p-role"><?= $cargo_label ?></div>
                </div>
                <i class="fa-solid fa-chevron-down profile-chevron"></i>
            </button>

            <!-- Dropdown -->
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