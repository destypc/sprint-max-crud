<?php

/**
 * Sidebar — Sprint Max Dashboard
 * Variável esperada: $current_page (string) para marcar item ativo.
 */
$current_page = $current_page ?? 'dashboard';
$tipo_usuario  = $_SESSION['user']['tipo'] ?? 'usuario';
?>

<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="/assets/img/logo-name.png" alt="Sprint Max">
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <?php if ($tipo_usuario === 'admin'): ?>
            <span class="nav-section-label">Principal</span>

            <a href="/pages/dashboard.php" class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-house icon"></i>
                Dashboard
            </a>

            <a href="/pages/usuarios.php" class="nav-item <?= $current_page === 'usuarios' ? 'active' : '' ?>">
                <i class="fa-solid fa-users icon"></i>
                Usuários
            </a>
        <?php endif; ?>

        <span class="nav-section-label">Gestão</span>

        <a href="/pages/produtos.php" class="nav-item <?= $current_page === 'produtos' ? 'active' : '' ?>">
            <i class="fa-solid fa-box icon"></i>
            Produtos
        </a>

        <a href="/pages/vendas.php" class="nav-item <?= $current_page === 'vendas' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle-dollar-to-slot icon"></i>
            Vendas
        </a>

    </nav>
</aside>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>