<?php
session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$uid            = (int) $usuario_logado['id'];
$current_page   = 'perfil';
$page_title     = 'Meu Perfil';
$breadcrumb     = [['label' => 'Meu Perfil']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Dados basicos (colunas sempre existem)
$stmt = $pdo->prepare('SELECT id, nome, email, tipo FROM usuarios WHERE id = ?');
$stmt->execute([$uid]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Foto de perfil (requer migracao)
$foto_perfil = $usuario_logado['foto_perfil'] ?? null;

// Estatisticas (requer tabela pedidos)
$totalPedidos = 0;
$totalGasto   = 0.0;
try {
    $s = $pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?');
    $s->execute([$uid]);
    $totalPedidos = (int) $s->fetchColumn();

    $s = $pdo->prepare('SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE usuario_id = ?');
    $s->execute([$uid]);
    $totalGasto = (float) $s->fetchColumn();
} catch (PDOException $e) { /* tabela pedidos pode nao existir */ }

$avatar = !empty($foto_perfil)
    ? htmlspecialchars($foto_perfil)
    : 'https://ui-avatars.com/api/?name=' . urlencode($usuario['nome'])
      . '&background=F97316&color=fff&bold=true&size=200';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <script>(function(){var t=localStorage.getItem('sprint-theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max &mdash; Meu Perfil</title>
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

        <div class="dash-hero">
            <h1>Meu Perfil</h1>
            <p>Gerencie suas informacoes pessoais.</p>
        </div>

        <div class="dash-grid" style="grid-template-columns:280px 1fr;align-items:start">

            <!-- Card do perfil -->
            <div class="card" style="text-align:center;padding:28px 20px">

                <img src="<?= $avatar ?>"
                     alt="Avatar de <?= htmlspecialchars($usuario['nome']) ?>"
                     style="width:88px;height:88px;border-radius:50%;object-fit:cover;
                            border:3px solid rgba(249,115,22,.4);display:block;margin:0 auto 14px">

                <div style="font-size:1rem;font-weight:700;color:var(--text-main)">
                    <?= htmlspecialchars($usuario['nome']) ?>
                </div>
                <div style="font-size:.76rem;color:var(--text-dim);margin-top:3px">
                    <?= htmlspecialchars($usuario['email']) ?>
                </div>
                <div style="margin-top:8px">
                    <?= $usuario['tipo'] === 'admin'
                        ? '<span class="badge-tipo admin">Administrador</span>'
                        : '<span class="badge-tipo usuario">Cliente</span>' ?>
                </div>

                <div style="display:flex;justify-content:space-around;
                            margin:18px 0;padding:14px 0;
                            border-top:1px solid var(--border);
                            border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-size:1.3rem;font-weight:700;color:var(--orange)">
                            <?= $totalPedidos ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px">
                            Pedidos
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.95rem;font-weight:700;color:var(--orange)">
                            R$&nbsp;<?= number_format($totalGasto, 0, ',', '.') ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px">
                            Gasto
                        </div>
                    </div>
                </div>

                <button class="btn-primary" onclick="openProfileModal()"
                        style="width:100%;justify-content:center">
                    <i class="fa-solid fa-pen"></i>
                    Editar Perfil
                </button>

            </div>

            <!-- Links rapidos -->
            <div class="card" style="padding:24px">
                <h3 style="font-size:.9rem;font-weight:700;color:var(--text-main);margin-bottom:16px">
                    Acesso rapido
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px">

                    <a href="/pages/pedidos.php"
                       style="display:flex;align-items:center;gap:12px;padding:13px 14px;
                              background:var(--bg-input);border:1px solid var(--border);
                              border-radius:var(--radius-sm);color:var(--text-sub);
                              text-decoration:none;transition:border-color .2s"
                       onmouseover="this.style.borderColor='var(--orange)'"
                       onmouseout="this.style.borderColor='var(--border)'">
                        <i class="fa-solid fa-bag-shopping" style="color:var(--orange);width:18px;text-align:center;font-size:1rem"></i>
                        <div>
                            <div style="font-size:.86rem;font-weight:600;color:var(--text-main)">Meus Pedidos</div>
                            <div style="font-size:.73rem;color:var(--text-dim)"><?= $totalPedidos ?> pedido<?= $totalPedidos !== 1 ? 's' : '' ?></div>
                        </div>
                    </a>

                    <a href="/pages/favoritos.php"
                       style="display:flex;align-items:center;gap:12px;padding:13px 14px;
                              background:var(--bg-input);border:1px solid var(--border);
                              border-radius:var(--radius-sm);color:var(--text-sub);
                              text-decoration:none;transition:border-color .2s"
                       onmouseover="this.style.borderColor='var(--orange)'"
                       onmouseout="this.style.borderColor='var(--border)'">
                        <i class="fa-solid fa-heart" style="color:var(--red);width:18px;text-align:center;font-size:1rem"></i>
                        <div>
                            <div style="font-size:.86rem;font-weight:600;color:var(--text-main)">Favoritos</div>
                            <div style="font-size:.73rem;color:var(--text-dim)">Produtos salvos</div>
                        </div>
                    </a>

                    <a href="/pages/home.php"
                       style="display:flex;align-items:center;gap:12px;padding:13px 14px;
                              background:var(--bg-input);border:1px solid var(--border);
                              border-radius:var(--radius-sm);color:var(--text-sub);
                              text-decoration:none;transition:border-color .2s"
                       onmouseover="this.style.borderColor='var(--orange)'"
                       onmouseout="this.style.borderColor='var(--border)'">
                        <i class="fa-solid fa-store" style="color:var(--orange);width:18px;text-align:center;font-size:1rem"></i>
                        <div>
                            <div style="font-size:.86rem;font-weight:600;color:var(--text-main)">Ir para a loja</div>
                            <div style="font-size:.73rem;color:var(--text-dim)">Explorar produtos</div>
                        </div>
                    </a>

                </div>
            </div>

        </div><!-- /dash-grid -->

    </main>

</div><!-- /main-wrapper -->


<!-- Toast -->
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

    function openSidebar()  { sidebar.classList.add('open');    sidebarOverlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('open'); }

    if (toggleBtn)      toggleBtn.addEventListener('click', openSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

    /* profile dropdown */
    const profileBtn      = document.getElementById('profileBtn');
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
        const icon  = document.getElementById('toastIcon');
        const msgEl = document.getElementById('toastMsg');
        toast.className  = 'sp-toast ' + type;
        icon.className   = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
        msgEl.textContent = msg;
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 3800);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && typeof closeProfileModal === 'function') closeProfileModal();
    });
</script>

</body>
</html>