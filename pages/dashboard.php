<?php
require_once __DIR__ . '/../app/controller/dashboardController.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<?php require __DIR__ . '/../app/includes/head.php'; ?>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <div class="painel-destaque">
                <h1>Dashboard</h1>
                <p>Bem-vindo de volta, <?= htmlspecialchars($usuario_logado['nome'] ?? 'Administrador') ?>.</p>
            </div>

            <div class="painel-cartoes">

                <div class="indicador">
                    <div class="indicador-cabecalho">
                        <span class="indicador-rotulo">Usuários</span>
                        <div class="indicador-icone orange"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="indicador-valor"><?= $totalUsuarios ?></div>
                    <div class="indicador-rodape">
                        <span><a href="/pages/usuarios.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="indicador">
                    <div class="indicador-cabecalho">
                        <span class="indicador-rotulo">Produtos</span>
                        <div class="indicador-icone purple"><i class="fa-solid fa-box"></i></div>
                    </div>
                    <div class="indicador-valor"><?= $totalProdutos ?></div>
                    <div class="indicador-rodape">
                        <span><a href="/pages/produtos.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="indicador">
                    <div class="indicador-cabecalho">
                        <span class="indicador-rotulo">Pedidos</span>
                        <div class="indicador-icone green"><i class="fa-solid fa-bag-shopping"></i></div>
                    </div>
                    <div class="indicador-valor"><?= $totalPedidos ?></div>
                    <div class="indicador-rodape">
                        <span><a href="/pages/pedidos.php">Ver todos</a></span>
                    </div>
                </div>

                <div class="indicador">
                    <div class="indicador-cabecalho">
                        <span class="indicador-rotulo">Administradores</span>
                        <div class="indicador-icone blue"><i class="fa-solid fa-user-shield"></i></div>
                    </div>
                    <div class="indicador-valor"><?= $totalAdmins ?></div>
                    <div class="indicador-rodape">
                        <span><a href="/pages/usuarios.php">Gerenciar</a></span>
                    </div>
                </div>

            </div>

            <div class="painel-grade">

                <!-- Pedidos recentes -->
                <div class="painel">
                    <div class="painel-cabecalho">
                        <div>
                            <div class="painel-titulo">
                                <i class="fa-solid fa-bag-shopping"></i>
                                Pedidos Recentes
                            </div>
                            <div class="painel-subtitulo">As últimas compras registradas</div>
                        </div>
                    </div>
                    <div class="painel-corpo">
                        <div class="lista-atividade">
                            <?php if (!empty($pedidosRecentes)): ?>
                            <?php foreach ($pedidosRecentes as $i => $pr): ?>
                            <div class="item-atividade" style="justify-content:space-between;align-items:center<?= $i === count($pedidosRecentes) - 1 ? ';border-bottom:none;padding-bottom:0' : '' ?>">
                                <div class="ponto-atividade <?= pedidoDotCor($pr['status']) ?>">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </div>
                                <div class="texto-atividade">
                                    <div class="descricao-atividade">Pedido #<?= (int)$pr['id'] ?></div>
                                    <div class="horario-atividade"><?= htmlspecialchars($pr['cliente']) ?> &middot; <?= timeAgo($pr['created_at']) ?></div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;margin-left:12px">
                                    <div style="font-size:.88rem;font-weight:700;color:var(--text-main)">R$&nbsp;<?= number_format((float)$pr['total'], 2, ',', '.') ?></div>
                                    <?= pedidoStatusBadge($pr['status']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="item-atividade" style="border-bottom:none;padding-bottom:0">
                                <div class="texto-atividade">
                                    <div class="descricao-atividade" style="color:var(--text-dim)">Nenhum pedido registrado ainda.</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Atividades recentes (logs) -->
                <div class="painel">
                    <div class="painel-cabecalho">
                        <div>
                            <div class="painel-titulo">
                                <i class="fa-solid fa-bell"></i>
                                Atividades Recentes
                            </div>
                            <div class="painel-subtitulo">As últimas atividades no sistema</div>
                        </div>
                    </div>
                    <div class="painel-corpo">
                        <div class="lista-atividade">
                            <?php if (!empty($logsRecentes)): ?>
                            <?php foreach ($logsRecentes as $i => $log): ?>
                            <?php [$cor, $icone] = logIcone($log['acao']); ?>
                            <div class="item-atividade"<?= $i === count($logsRecentes) - 1 ? ' style="border-bottom:none;padding-bottom:0"' : '' ?>>
                                <div class="ponto-atividade <?= $cor ?>"><i class="fa-solid <?= $icone ?>"></i></div>
                                <div class="texto-atividade">
                                    <div class="descricao-atividade"><?= htmlspecialchars($log['descricao']) ?></div>
                                    <div class="horario-atividade"><?= timeAgo($log['data']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <div class="item-atividade" style="border-bottom:none;padding-bottom:0">
                                <div class="texto-atividade">
                                    <div class="descricao-atividade" style="color:var(--text-dim)">Nenhuma atividade registrada ainda.</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>
