<?php
session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../app/config/conexao.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'dashboard-usuario';
$page_title     = 'Meu Painel';
$trilhaNavegacao     = [['label' => 'Meu Painel']];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$uid = (int) $usuario_logado['id'];

// Estatísticas do usuário (tabelas existem após rodar banco-migration.sql)
$totalPedidos     = 0;
$totalGasto       = 0.0;
$pedidosPendentes = 0;
$ultimoPedido     = null;
$produtoFavorito  = null;
$ultimasPedidos   = [];
$itensPorPedido   = [];
$erroMigracao     = false;

try {
    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ?");
    $s->execute([$uid]);
    $totalPedidos = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE usuario_id = ?");
    $s->execute([$uid]);
    $totalGasto = (float)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE usuario_id = ? AND status = 'pendente'");
    $s->execute([$uid]);
    $pedidosPendentes = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT id, total, status, created_at FROM pedidos WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 1");
    $s->execute([$uid]);
    $ultimoPedido = $s->fetch(PDO::FETCH_ASSOC) ?: null;

    $s = $pdo->prepare("
        SELECT pr.nome, SUM(pi.quantidade) AS qtd
        FROM pedido_itens pi
        JOIN pedidos p  ON p.id  = pi.pedido_id
        JOIN produtos pr ON pr.id = pi.produto_id
        WHERE p.usuario_id = ?
        GROUP BY pr.id, pr.nome ORDER BY qtd DESC LIMIT 1
    ");
    $s->execute([$uid]);
    $produtoFavorito = $s->fetch(PDO::FETCH_ASSOC) ?: null;

    $s = $pdo->prepare("SELECT id, total, status, created_at FROM pedidos WHERE usuario_id = ? ORDER BY id DESC LIMIT 5");
    $s->execute([$uid]);
    $ultimasPedidos = $s->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($ultimasPedidos)) {
        $oids = array_column($ultimasPedidos, 'id');
        $ph   = implode(',', array_fill(0, count($oids), '?'));
        $s = $pdo->prepare("
            SELECT pi.pedido_id, pi.quantidade, pi.subtotal,
                   pr.nome AS produto_nome,
                   COALESCE(pr.imagem,'') AS produto_imagem
            FROM pedido_itens pi
            JOIN produtos pr ON pr.id = pi.produto_id
            WHERE pi.pedido_id IN ($ph)
        ");
        $s->execute($oids);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $it) {
            $itensPorPedido[(int)$it['pedido_id']][] = $it;
        }
    }
} catch (PDOException $e) {
    $erroMigracao = true;
}

function statusBadgeSimples(string $status): string
{
    return match ($status) {
        'pendente'   => '<span class="badge badge-yellow">Pendente</span>',
        'preparando' => '<span class="badge badge-orange">Preparando</span>',
        'enviado'    => '<span class="badge badge-blue">Enviado</span>',
        'entregue'   => '<span class="badge badge-green">Entregue</span>',
        'cancelado'  => '<span class="badge badge-red">Cancelado</span>',
        default      => '<span class="badge">' . htmlspecialchars($status) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<?php $css_extra = ['loja.css']; require __DIR__ . '/../app/includes/head.php'; ?>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <?php if ($erroMigracao): ?>
                <div class="card" style="padding:40px;text-align:center">
                    <i class="fa-solid fa-database" style="font-size:2.5rem;color:var(--orange);margin-bottom:16px;display:block"></i>
                    <h3 style="color:var(--text-main);margin-bottom:8px">Migração do banco necessária</h3>
                    <p style="color:var(--text-dim);margin-bottom:16px">
                        Execute o arquivo <strong>banco-migration.sql</strong> no phpMyAdmin<br>
                        para habilitar pedidos, favoritos e notificações.
                    </p>
                    <a href="/pages/home.php" class="botao-primario" style="display:inline-flex">
                        <i class="fa-solid fa-store"></i> Ir para a loja
                    </a>
                </div>
            <?php else: ?>
                <div class="painel-destaque">
                    <h1>Meu Painel</h1>
                    <p>Olá, <?= htmlspecialchars($usuario_logado['nome']) ?>. Confira suas estatísticas.</p>
                </div>

                <div class="painel-cartoes">

                    <div class="indicador">
                        <div class="indicador-cabecalho">
                            <span class="indicador-rotulo">Total de Pedidos</span>
                            <div class="indicador-icone orange"><i class="fa-solid fa-bag-shopping"></i></div>
                        </div>
                        <div class="indicador-valor"><?= $totalPedidos ?></div>
                        <div class="indicador-rodape">
                            <span><a href="/pages/pedidos.php">Ver pedidos</a></span>
                        </div>
                    </div>

                    <div class="indicador">
                        <div class="indicador-cabecalho">
                            <span class="indicador-rotulo">Total Gasto</span>
                            <div class="indicador-icone green"><i class="fa-solid fa-sack-dollar"></i></div>
                        </div>
                        <div class="indicador-valor" style="font-size:1.6rem">
                            R$&nbsp;<?= number_format($totalGasto, 2, ',', '.') ?>
                        </div>
                        <div class="indicador-rodape">
                            <span><?= $totalPedidos > 0 ? 'em ' . $totalPedidos . ' pedido(s)' : 'Sem compras ainda' ?></span>
                        </div>
                    </div>

                    <div class="indicador">
                        <div class="indicador-cabecalho">
                            <span class="indicador-rotulo">Pedidos Pendentes</span>
                            <div class="indicador-icone <?= $pedidosPendentes > 0 ? 'orange' : 'green' ?>">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                        <div class="indicador-valor"><?= $pedidosPendentes ?></div>
                        <div class="indicador-rodape">
                            <span><?= $pedidosPendentes > 0 ? 'aguardando processamento' : 'Nenhum pendente' ?></span>
                        </div>
                    </div>

                    <div class="indicador">
                        <div class="indicador-cabecalho">
                            <span class="indicador-rotulo">Produto Favorito</span>
                            <div class="indicador-icone purple"><i class="fa-solid fa-heart"></i></div>
                        </div>
                        <div class="indicador-valor" style="font-size:1rem;line-height:1.3;margin-top:4px">
                            <?= $produtoFavorito ? htmlspecialchars($produtoFavorito['nome']) : '—' ?>
                        </div>
                        <div class="indicador-rodape">
                            <span>
                                <?= $produtoFavorito
                                    ? 'comprado ' . $produtoFavorito['qtd'] . ' vez(es)'
                                    : 'Nenhuma compra ainda' ?>
                            </span>
                        </div>
                    </div>

                </div>

                <div class="painel-grade">

                    <!-- Último pedido -->
                    <div class="painel">
                        <div class="painel-cabecalho">
                            <div>
                                <div class="painel-titulo">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    Último Pedido
                                </div>
                                <div class="painel-subtitulo">Sua compra mais recente</div>
                            </div>
                        </div>
                        <div class="painel-corpo">

                            <?php if ($ultimoPedido): ?>
                                <div class="lista-atividade">

                                    <div class="item-atividade" style="align-items:center">
                                        <div class="ponto-atividade orange">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </div>
                                        <div class="texto-atividade">
                                            <div class="descricao-atividade">Pedido #<?= (int)$ultimoPedido['id'] ?></div>
                                            <div class="horario-atividade">
                                                <?= date('d/m/Y \à\s H:i', strtotime($ultimoPedido['created_at'])) ?>
                                            </div>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0">
                                            <?= statusBadgeSimples($ultimoPedido['status']) ?>
                                            <div style="font-weight:700;font-size:.9rem;color:var(--text-main);margin-top:4px">
                                                R$ <?= number_format((float)$ultimoPedido['total'], 2, ',', '.') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php foreach ($itensPorPedido[$ultimoPedido['id']] ?? [] as $it): ?>
                                        <div class="item-atividade" style="align-items:center">
                                            <?php if (!empty($it['produto_imagem'])): ?>
                                                <img src="<?= htmlspecialchars($it['produto_imagem']) ?>"
                                                    style="width:30px;height:30px;border-radius:6px;object-fit:cover;border:1px solid var(--border);flex-shrink:0"
                                                    alt="">
                                            <?php else: ?>
                                                <div class="ponto-atividade orange" style="background:var(--orange-subtle)">
                                                    <i class="fa-solid fa-box" style="color:var(--orange)"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="texto-atividade">
                                                <div class="descricao-atividade"><?= htmlspecialchars($it['produto_nome']) ?></div>
                                                <div class="horario-atividade"><?= (int)$it['quantidade'] ?> unidade(s)</div>
                                            </div>
                                            <div style="font-weight:600;font-size:.84rem;color:var(--text-main);flex-shrink:0">
                                                R$ <?= number_format((float)$it['subtotal'], 2, ',', '.') ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="item-atividade" style="border-bottom:none;padding-bottom:0">
                                        <a href="/pages/pedidos.php" style="color:var(--orange);font-size:.82rem;font-weight:500">
                                            Ver todos os pedidos →
                                        </a>
                                    </div>

                                </div>
                            <?php else: ?>
                                <div class="estado-vazio">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                    <h4>Nenhum pedido ainda</h4>
                                    <p><a href="/pages/home.php" style="color:var(--orange)">Ir para a loja</a></p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- Histórico de compras -->
                    <div class="painel">
                        <div class="painel-cabecalho">
                            <div>
                                <div class="painel-titulo">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Histórico de Compras
                                </div>
                                <div class="painel-subtitulo">Seus últimos 5 pedidos</div>
                            </div>
                        </div>
                        <div class="painel-corpo">
                            <div class="lista-atividade">

                                <?php if (!empty($ultimasPedidos)): ?>
                                    <?php foreach ($ultimasPedidos as $i => $p): ?>
                                        <div class="item-atividade" style="align-items:center<?= $i === count($ultimasPedidos) - 1 ? ';border-bottom:none;padding-bottom:0' : '' ?>">
                                            <div class="ponto-atividade <?= $p['status'] === 'entregue' ? 'green' : ($p['status'] === 'cancelado' ? 'red' : 'orange') ?>">
                                                <i class="fa-solid fa-bag-shopping"></i>
                                            </div>
                                            <div class="texto-atividade">
                                                <div class="descricao-atividade">Pedido #<?= (int)$p['id'] ?></div>
                                                <div class="horario-atividade">
                                                    <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                                    &middot;
                                                    <?= count($itensPorPedido[$p['id']] ?? []) ?> item(s)
                                                </div>
                                            </div>
                                            <div style="text-align:right;flex-shrink:0">
                                                <?= statusBadgeSimples($p['status']) ?>
                                                <div style="font-size:.82rem;font-weight:700;color:var(--text-main);margin-top:3px">
                                                    R$ <?= number_format((float)$p['total'], 2, ',', '.') ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="item-atividade" style="border-bottom:none;padding-bottom:0">
                                        <div class="texto-atividade">
                                            <div class="descricao-atividade" style="color:var(--text-dim)">
                                                Você ainda não fez nenhuma compra.
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                </div>

        </main>

    </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/includes/toast.php'; ?>

<?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>