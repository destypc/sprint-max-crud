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
$trilhaNavegacao     = [['label' => 'Meu Perfil']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

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
} catch (PDOException $e) { /* tabela pedidos pode nao existir */
}

$avatar = !empty($foto_perfil)
    ? htmlspecialchars($foto_perfil)
    : 'https://ui-avatars.com/api/?name=' . urlencode($usuario['nome'])
    . '&background=F97316&color=fff&bold=true&size=200';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<?php $css_extra = ['loja.css']; require __DIR__ . '/../app/includes/head.php'; ?>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <div class="painel-destaque">
                <h1>Meu Perfil</h1>
                <p>Gerencie suas informacoes pessoais.</p>
            </div>

            <div class="painel-grade" style="grid-template-columns:280px 1fr;align-items:start">

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

                    <button class="botao-primario" onclick="openProfileModal()"
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

            </div><!-- /painel-grade -->

        </main>

    </div><!-- /conteiner-principal -->


    <!-- Toast -->
    <?php require __DIR__ . '/../app/includes/toast.php'; ?>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>