<?php
$current_page = $current_page ?? 'dashboard';
$tipo_usuario = $_SESSION['user']['tipo'] ?? 'usuario';
$cartCount    = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <img src="/assets/img/logo-name-escuro.png" class="logo-dark" alt="Sprint Max">
        <img src="/assets/img/logo-name-claro.png" class="logo-light" alt="Sprint Max">
    </div>

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

        <span class="nav-section-label">Gestão</span>

        <a href="/pages/produtos.php" class="nav-item <?= $current_page === 'produtos' ? 'active' : '' ?>">
            <i class="fa-solid fa-box icon"></i>
            Produtos
        </a>

        <a href="/pages/pedidos.php" class="nav-item <?= $current_page === 'pedidos' ? 'active' : '' ?>">
            <i class="fa-solid fa-bag-shopping icon"></i>
            Pedidos
        </a>

        <span class="nav-section-label">Sistema</span>

        <a href="/pages/relatorios.php" class="nav-item <?= $current_page === 'relatorios' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-bar icon"></i>
            Logs
        </a>

        <a href="/pages/home.php" class="nav-item <?= $current_page === 'home' ? 'active' : '' ?>">
            <i class="fa-solid fa-store icon"></i>
            Ver Loja
        </a>

        <?php else: ?>

        <span class="nav-section-label">Loja</span>

        <a href="/pages/home.php" class="nav-item <?= $current_page === 'home' ? 'active' : '' ?>">
            <i class="fa-solid fa-store icon"></i>
            Início
        </a>

        <span class="nav-section-label">Compras</span>

        <a href="/pages/carrinho.php" class="nav-item <?= $current_page === 'carrinho' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping icon"></i>
            Carrinho
            <?php if ($cartCount > 0): ?>
            <span class="cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>

        <a href="/pages/pedidos.php" class="nav-item <?= $current_page === 'pedidos' ? 'active' : '' ?>">
            <i class="fa-solid fa-bag-shopping icon"></i>
            Meus Pedidos
        </a>

        <a href="/pages/favoritos.php" class="nav-item <?= $current_page === 'favoritos' ? 'active' : '' ?>">
            <i class="fa-solid fa-heart icon"></i>
            Favoritos
        </a>

        <span class="nav-section-label">Conta</span>

        <a href="/pages/dashboard-usuario.php" class="nav-item <?= $current_page === 'dashboard-usuario' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie icon"></i>
            Meu Painel
        </a>

        <a href="/pages/perfil.php" class="nav-item <?= $current_page === 'perfil' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle-user icon"></i>
            Meu Perfil
        </a>

        <?php endif; ?>

        <a href="/auth/logout.php" class="nav-item" style="margin-top:auto;color:var(--red)">
            <i class="fa-solid fa-right-from-bracket icon"></i>
            Sair
        </a>

    </nav>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
