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
$current_page   = 'favoritos';
$page_title     = 'Favoritos';
$breadcrumb     = [['label' => 'Favoritos']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Carregar produtos favoritados (tabela existe após banco-migration.sql)
$favoritos    = [];
$favoritoIds  = [];
$erroMigracao = false;
try {
    $stmt = $pdo->prepare("
        SELECT pr.id, pr.nome, pr.categoria, pr.preco, pr.quantidade,
               pr.descricao, pr.status,
               COALESCE(pr.imagem, '') AS imagem
        FROM favoritos f
        JOIN produtos pr ON pr.id = f.produto_id
        WHERE f.usuario_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$usuario_logado['id']]);
    $favoritos   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $favoritoIds = array_map('intval', array_column($favoritos, 'id'));
} catch (PDOException $e) {
    $erroMigracao = true;
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
    <title>Sprint Max — Favoritos</title>
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

            <?php if ($erroMigracao): ?>
                <div class="card" style="padding:40px;text-align:center">
                    <i class="fa-solid fa-database" style="font-size:2.5rem;color:var(--orange);margin-bottom:16px;display:block"></i>
                    <h3 style="color:var(--text-main);margin-bottom:8px">Migração do banco necessária</h3>
                    <p style="color:var(--text-dim);margin-bottom:16px">
                        A tabela de favoritos ainda não foi criada.<br>
                        Execute o arquivo <strong>banco-migration.sql</strong> no phpMyAdmin.
                    </p>
                    <a href="/pages/home.php" class="btn-primary" style="display:inline-flex">
                        <i class="fa-solid fa-store"></i> Ir para a loja
                    </a>
                </div>
            <?php else: ?>
                <div class="loja-hero">
                    <div class="loja-hero-text">
                        <h1>Favoritos</h1>
                        <p><?= count($favoritos) ?> produto<?= count($favoritos) !== 1 ? 's' : '' ?> favoritado<?= count($favoritos) !== 1 ? 's' : '' ?></p>
                    </div>
                </div>

                <?php if (empty($favoritos)): ?>

                    <div class="card">
                        <div class="cart-empty-state">
                            <i class="fa-regular fa-heart"></i>
                            <h3>Nenhum favorito ainda</h3>
                            <p style="margin-bottom:20px">Clique no coração ❤ nos produtos para salvá-los aqui.</p>
                            <a href="/pages/home.php" class="btn-primary" style="display:inline-flex">
                                <i class="fa-solid fa-store"></i>
                                Explorar produtos
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="produtos-grid">
                        <?php foreach ($favoritos as $p):
                            $pJson = htmlspecialchars(json_encode([
                                'id'         => (int)   $p['id'],
                                'nome'       =>         $p['nome'],
                                'categoria'  =>         $p['categoria'],
                                'preco'      => (float) $p['preco'],
                                'quantidade' => (int)   $p['quantidade'],
                                'descricao'  =>         $p['descricao'] ?? '',
                                'status'     =>         $p['status'],
                                'imagem'     =>         $p['imagem'] ?? '',
                            ]), ENT_QUOTES);
                        ?>
                            <article class="produto-card"
                                data-categoria="<?= htmlspecialchars($p['categoria']) ?>"
                                data-search="<?= htmlspecialchars(strtolower($p['nome'] . ' ' . $p['categoria'])) ?>"
                                onclick="verDetalhes(<?= $pJson ?>)">

                                <div class="produto-img">
                                    <?php if (!empty($p['imagem'])): ?>
                                        <img src="<?= htmlspecialchars($p['imagem']) ?>"
                                            alt="<?= htmlspecialchars($p['nome']) ?>">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fa-solid fa-box"></i></div>
                                    <?php endif; ?>

                                    <!-- Remover favorito -->
                                    <form class="fav-form" method="POST"
                                        action="/app/controller/favoritosController.php"
                                        onclick="event.stopPropagation()">
                                        <input type="hidden" name="acao" value="toggle">
                                        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="redirect" value="/pages/favoritos.php">
                                        <button type="submit" class="btn-fav ativo" title="Remover dos favoritos">
                                            <i class="fa-solid fa-heart"></i>
                                        </button>
                                    </form>

                                    <?php if ($p['status'] === 'sem_estoque'): ?>
                                        <div class="sem-estoque-overlay">Indisponível</div>
                                    <?php endif; ?>
                                </div>

                                <div class="produto-info">
                                    <span class="produto-cat"><?= htmlspecialchars($p['categoria']) ?></span>
                                    <h3 class="produto-nome"><?= htmlspecialchars($p['nome']) ?></h3>
                                    <div class="produto-preco">R$ <?= number_format($p['preco'], 2, ',', '.') ?></div>
                                    <?php if ($p['status'] === 'sem_estoque'): ?>
                                        <div class="produto-estoque sem">Indisponível</div>
                                    <?php elseif ($p['status'] === 'baixo_estoque'): ?>
                                        <div class="produto-estoque baixo">Apenas <?= (int)$p['quantidade'] ?> em estoque</div>
                                    <?php else: ?>
                                        <div class="produto-estoque"><?= (int)$p['quantidade'] ?> disponível(is)</div>
                                    <?php endif; ?>
                                </div>

                                <div class="produto-actions" onclick="event.stopPropagation()">
                                    <button class="btn-detalhe" onclick="verDetalhes(<?= $pJson ?>)">
                                        <i class="fa-solid fa-eye"></i> Ver mais
                                    </button>
                                    <form method="POST" action="/app/controller/carrinhoController.php"
                                        style="flex:2;display:flex;">
                                        <input type="hidden" name="acao" value="adicionar">
                                        <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="quantidade" value="1">
                                        <input type="hidden" name="redirect" value="/pages/favoritos.php">
                                        <button type="submit" class="btn-add-cart"
                                            <?= $p['status'] === 'sem_estoque' ? 'disabled' : '' ?>>
                                            <i class="fa-solid fa-cart-shopping"></i>
                                            <?= $p['status'] === 'sem_estoque' ? 'Indisponível' : '+ Carrinho' ?>
                                        </button>
                                    </form>
                                </div>

                            </article>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

        </main>

    </div>


    <!-- Modal de detalhe (mesmo do home.php) -->
    <div class="modal-backdrop" id="detalheModalBackdrop" onclick="fecharDetalhe(event)">
        <div class="modal modal-scroll" style="max-width:640px" onclick="event.stopPropagation()"
            role="dialog" aria-modal="true">
            <div class="modal-head">
                <h3 id="detalheTitulo" style="font-size:.95rem;font-weight:700"></h3>
                <button class="btn-close-modal" onclick="closeDetalheModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="detalhe-grid">
                    <div class="detalhe-img">
                        <img id="detalheImg" src="" alt="" style="display:none">
                        <div id="detalheNoImg" class="no-img" style="display:none">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    </div>
                    <div class="detalhe-info">
                        <span class="detalhe-cat" id="detalheCat"></span>
                        <h2 class="detalhe-nome" id="detalheNome"></h2>
                        <div class="detalhe-preco" id="detalhePreco"></div>
                        <div class="detalhe-estoque-badge" id="detalheEstoque"></div>
                        <p class="detalhe-desc" id="detalheDesc"></p>
                        <div class="qtd-wrap">
                            <span class="qtd-label">Qtd.</span>
                            <div class="qtd-controles">
                                <button class="qtd-btn" id="qtdMenos" type="button">−</button>
                                <span class="qtd-num" id="qtdNum">1</span>
                                <button class="qtd-btn" id="qtdMais" type="button">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" onclick="closeDetalheModal()">Fechar</button>
                <form id="formAddCart" method="POST" action="/app/controller/carrinhoController.php"
                    style="flex:2;display:flex;">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" id="cartProdutoId" value="">
                    <input type="hidden" name="quantidade" id="cartQuantidade" value="1">
                    <input type="hidden" name="redirect" value="/pages/favoritos.php">
                    <button type="submit" id="btnAdicionarCart" class="btn-primary" style="width:100%">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

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
    /* sidebar, profile dropdown, toast — idêntico ao home.php */
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

    /* Modal detalhe */
    var qtdAtual = 1,
        qtdMax = 99;

    function verDetalhes(p) {
        qtdAtual = 1;
        qtdMax = p.status === 'sem_estoque' ? 0 : Math.max(1, p.quantidade);
        document.getElementById('detalheTitulo').textContent = p.nome;
        document.getElementById('detalheCat').textContent = p.categoria;
        document.getElementById('detalheNome').textContent = p.nome;
        document.getElementById('detalhePreco').textContent = 'R$ ' + parseFloat(p.preco).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('detalheDesc').textContent = p.descricao || 'Sem descrição.';

        var estoqueEl = document.getElementById('detalheEstoque');
        if (p.status === 'sem_estoque') {
            estoqueEl.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:var(--red)"></i> Indisponível';
        } else {
            estoqueEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--green)"></i> ' + p.quantidade + ' disponível(is)';
        }

        var img = document.getElementById('detalheImg');
        var noImg = document.getElementById('detalheNoImg');
        if (p.imagem) {
            img.src = p.imagem;
            img.style.display = 'block';
            noImg.style.display = 'none';
        } else {
            img.style.display = 'none';
            noImg.style.display = 'flex';
        }

        document.getElementById('qtdNum').textContent = '1';
        document.getElementById('cartQuantidade').value = '1';
        document.getElementById('cartProdutoId').value = p.id;

        var btn = document.getElementById('btnAdicionarCart');
        btn.disabled = p.status === 'sem_estoque';
        btn.innerHTML = p.status === 'sem_estoque' ?
            'Indisponível' :
            '<i class="fa-solid fa-cart-shopping"></i> Adicionar ao Carrinho';

        document.getElementById('detalheModalBackdrop').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDetalheModal() {
        document.getElementById('detalheModalBackdrop').classList.remove('open');
        document.body.style.overflow = '';
    }

    function fecharDetalhe(e) {
        if (e.target === document.getElementById('detalheModalBackdrop')) closeDetalheModal();
    }

    document.getElementById('qtdMenos').addEventListener('click', function() {
        if (qtdAtual > 1) {
            qtdAtual--;
            document.getElementById('qtdNum').textContent = qtdAtual;
            document.getElementById('cartQuantidade').value = qtdAtual;
        }
    });
    document.getElementById('qtdMais').addEventListener('click', function() {
        if (qtdAtual < qtdMax) {
            qtdAtual++;
            document.getElementById('qtdNum').textContent = qtdAtual;
            document.getElementById('cartQuantidade').value = qtdAtual;
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetalheModal();
            if (typeof closeProfileModal === 'function') closeProfileModal();
        }
    });
</script>

</body>

</html>