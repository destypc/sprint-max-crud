<?php
require_once __DIR__ . '/../app/controller/vendasController.php';
if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}
$usuario_logado = $_SESSION['user'];
$current_page   = 'vendas';
$page_title     = 'Vendas';
$breadcrumb     = [['label' => 'Vendas']];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Vendas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="main-wrapper">

        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="page-content">

            <!-- HERO -->
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:32px">
                <div class="dash-hero" style="margin-bottom:0">
                    <h1>Vendas</h1>
                    <p>Gerencie todas as vendas do sistema.</p>
                </div>
                <button class="btn-primary" style="align-self:center" onclick="openVendaModal()">
                    <i class="fa-solid fa-plus"></i>
                    Nova Venda
                </button>
            </div>

            <!-- STAT CARDS -->
            <div class="dash-cards">

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Total de Vendas</span>
                        <div class="dash-stat-icon green"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $totalVendas ?></div>
                    <div class="dash-stat-footer"><span><?= $totalVendas > 0 ? 'venda(s) registrada(s)' : 'Sem vendas' ?></span></div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Faturamento Total</span>
                        <div class="dash-stat-icon orange"><i class="fa-solid fa-sack-dollar"></i></div>
                    </div>
                    <div class="dash-stat-value" style="font-size:1.55rem">R$&nbsp;<?= number_format($faturamento, 2, ',', '.') ?></div>
                    <div class="dash-stat-footer"><span><?= $faturamento > 0 ? 'acumulado total' : 'Sem faturamento' ?></span></div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Ticket Médio</span>
                        <div class="dash-stat-icon purple"><i class="fa-solid fa-receipt"></i></div>
                    </div>
                    <div class="dash-stat-value" style="font-size:1.55rem">R$&nbsp;<?= number_format($ticketMedio, 2, ',', '.') ?></div>
                    <div class="dash-stat-footer"><span><?= $ticketMedio > 0 ? 'média por venda' : 'Sem dados' ?></span></div>
                </div>

                <div class="dash-stat">
                    <div class="dash-stat-top">
                        <span class="dash-stat-label">Vendas Hoje</span>
                        <div class="dash-stat-icon blue"><i class="fa-solid fa-calendar-day"></i></div>
                    </div>
                    <div class="dash-stat-value"><?= $vendasHoje ?></div>
                    <div class="dash-stat-footer"><span><?= $vendasHoje > 0 ? 'hoje' : 'Nenhuma hoje' ?></span></div>
                </div>

            </div><!-- /dash-cards -->

            <!-- GRID: TABELA + RESUMO -->
            <div class="dash-grid">

                <!-- Tabela de Vendas -->
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <div>
                            <div class="dash-panel-title">
                                <i class="fa-solid fa-table-list"></i>
                                Tabela de Vendas
                            </div>
                            <div class="dash-panel-sub">Todas as transações do período</div>
                        </div>
                    </div>
                    <div class="dash-panel-body">

                        <!-- Filtros -->
                        <div class="toolbar" style="margin-bottom:18px">
                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" class="search-input" placeholder="Buscar por cliente ou produto..." autocomplete="off">
                            </div>
                            <select class="form-select" style="width:auto;min-width:130px;padding:9px 13px;border-radius:50px">
                                <option>Todos status</option>
                                <option>Concluída</option>
                                <option>Pendente</option>
                                <option>Cancelada</option>
                            </select>
                            <select class="form-select" style="width:auto;min-width:130px;padding:9px 13px;border-radius:50px">
                                <option>Hoje</option>
                                <option>Esta semana</option>
                                <option>Este mês</option>
                            </select>
                            <button class="btn-ghost">
                                <i class="fa-solid fa-sliders"></i>
                                Filtrar
                            </button>
                        </div>

                        <!-- Tabela -->
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Produto</th>
                                        <th>Qtd.</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($vendas)): ?>
                                        <?php foreach ($vendas as $venda):
                                            $vJson = htmlspecialchars(json_encode([
                                                'id'         => (int)$venda['id'],
                                                'quantidade' => (int)$venda['quantidade'],
                                                'valor'      => (float)$venda['valor'],
                                                'status'     => $venda['status'],
                                                'cliente'    => $venda['cliente'],
                                                'produto'    => $venda['produto'],
                                            ]), ENT_QUOTES);
                                        ?>
                                            <tr>
                                                <td><?= (int)$venda['id'] ?></td>
                                                <td><?= htmlspecialchars($venda['cliente']) ?></td>
                                                <td><?= htmlspecialchars($venda['produto']) ?></td>
                                                <td><?= (int)$venda['quantidade'] ?></td>
                                                <td>R$ <?= number_format($venda['valor'], 2, ',', '.') ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                                                <td>
                                                    <?php
                                                    $badgeMap = [
                                                        'concluida' => '<span class="badge badge-green">Concluída</span>',
                                                        'pendente'  => '<span class="badge badge-yellow">Pendente</span>',
                                                        'cancelada' => '<span class="badge badge-red">Cancelada</span>',
                                                    ];
                                                    echo $badgeMap[$venda['status']] ?? '<span class="badge">' . htmlspecialchars($venda['status']) . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="actions-cell">
                                                        <button class="btn-icon edit" title="Editar"
                                                            onclick='openEditVendaDrawer(<?= $vJson ?>)'>
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form method="POST" style="display:inline">
                                                            <input type="hidden" name="acao" value="excluir_venda">
                                                            <input type="hidden" name="id" value="<?= (int)$venda['id'] ?>">
                                                            <button class="btn-icon del" type="button" title="Excluir"
                                                                onclick="confirmDeleteVenda(this.closest('form'))">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="empty-state">
                                                    <i class="fa-solid fa-circle-dollar-to-slot"></i>
                                                    <h4>Nenhuma venda registrada</h4>
                                                    <p>Clique em "Nova Venda" para registrar a primeira.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div><!-- /tabela -->

                <!-- Resumo das Vendas -->
                <div class="dash-panel">
                    <div class="dash-panel-head">
                        <div>
                            <div class="dash-panel-title">
                                <i class="fa-solid fa-chart-pie"></i>
                                Resumo das Vendas
                            </div>
                            <div class="dash-panel-sub">Informações do período</div>
                        </div>
                    </div>
                    <div class="dash-panel-body">
                        <div class="activity-list">

                            <div class="activity-item" style="align-items:center">
                                <div class="activity-dot orange"><i class="fa-solid fa-coins"></i></div>
                                <div class="activity-text">
                                    <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:2px">Total vendido hoje</div>
                                    <div style="font-size:.95rem;font-weight:700;color:var(--text-main)">
                                        R$&nbsp;<?= number_format($totalHoje, 2, ',', '.') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item" style="align-items:center">
                                <div class="activity-dot purple"><i class="fa-solid fa-box"></i></div>
                                <div class="activity-text">
                                    <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:2px">Produto mais vendido</div>
                                    <div style="font-size:.88rem;font-weight:600;color:var(--text-main)">
                                        <?= htmlspecialchars($produtoMaisVendido['produto'] ?? '—') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item" style="align-items:center">
                                <div class="activity-dot blue"><i class="fa-solid fa-clock"></i></div>
                                <div class="activity-text">
                                    <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:2px">Última venda realizada</div>
                                    <div style="font-size:.88rem;font-weight:600;color:var(--text-main)">
                                        <?= $ultimaVenda ? date('d/m/Y H:i', strtotime($ultimaVenda['data_venda'])) : '—' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item" style="align-items:center">
                                <div class="activity-dot green"><i class="fa-solid fa-user-check"></i></div>
                                <div class="activity-text">
                                    <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:2px">Cliente que mais comprou</div>
                                    <div style="font-size:.88rem;font-weight:600;color:var(--text-main)">
                                        <?= htmlspecialchars($clienteMaisComprou['cliente'] ?? '—') ?>
                                        <?php if ($clienteMaisComprou): ?>
                                            <span style="font-size:.72rem;color:var(--text-dim);font-weight:400">
                                                (<?= $clienteMaisComprou['total_compras'] ?> compra<?= $clienteMaisComprou['total_compras'] != 1 ? 's' : '' ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="activity-item" style="align-items:center;border-bottom:none;padding-bottom:0">
                                <div class="activity-dot" style="background:rgba(255,255,255,0.05);color:var(--text-dim)">
                                    <i class="fa-solid fa-rotate"></i>
                                </div>
                                <div class="activity-text">
                                    <div style="font-size:.72rem;color:var(--text-dim);margin-bottom:2px">Última atualização</div>
                                    <div style="font-size:.82rem;color:var(--text-sub)"><?= date('d/m/Y, H:i') ?></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div><!-- /resumo -->

            </div><!-- /dash-grid -->

        </main><!-- /page-content -->

    </div><!-- /main-wrapper -->


    <!-- DRAWER — Editar Venda -->
    <div class="drawer-overlay" id="editVendaOverlay" onclick="closeEditVendaDrawer()"></div>
    <div class="drawer" id="editVendaDrawer" role="dialog" aria-modal="true" aria-label="Editar venda">
        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Venda</h3>
            <button class="btn-close-drawer" onclick="closeEditVendaDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="editVendaForm" method="POST" action="/pages/vendas.php" novalidate>
            <input type="hidden" id="editVendaId" name="id" value="">
            <input type="hidden" name="acao" value="editar_venda">
            <div class="drawer-body">
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <input type="text" id="editVendaCliente" class="form-input" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Produto</label>
                    <input type="text" id="editVendaProduto" class="form-input" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editVendaQuantidade">Quantidade *</label>
                    <input type="number" id="editVendaQuantidade" name="quantidade" class="form-input" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editVendaValor">Valor (R$) *</label>
                    <input type="number" id="editVendaValor" name="valor" class="form-input" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editVendaStatus">Status</label>
                    <select id="editVendaStatus" name="status" class="form-select">
                        <option value="concluida">Concluída</option>
                        <option value="pendente">Pendente</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>
            <div class="drawer-foot">
                <button type="button" class="btn-cancel-drawer" onclick="closeEditVendaDrawer()">Cancelar</button>
                <button type="submit" class="btn-save-drawer">
                    <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <!-- MODAL — Nova Venda -->
    <div class="modal-backdrop" id="vendaModalBackdrop" onclick="handleVendaModalClick(event)">
        <div class="modal modal-scroll" role="dialog" aria-modal="true" aria-label="Nova venda">

            <div class="modal-head">
                <h3><i class="fa-solid fa-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Nova Venda</h3>
                <button class="btn-close-modal" onclick="closeVendaModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="">
                <div class="modal-body">

                    <div class="form-group">
                        <label class="form-label" for="vendaCliente">Cliente *</label>
                        <select id="vendaCliente" name="cliente" class="form-select">
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientesLista as $c): ?>
                                <option value="<?= htmlspecialchars($c['nome']) ?>">
                                    <?= htmlspecialchars($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vendaProduto">Produto *</label>
                        <select id="vendaProduto" name="produto" class="form-select">
                            <option value="" data-estoque="0" data-preco="0">Selecione um produto</option>
                            <?php foreach ($produtosLista as $p): ?>
                                <option value="<?= htmlspecialchars($p['nome']) ?>"
                                    data-estoque="<?= (int)$p['quantidade'] ?>"
                                    data-preco="<?= number_format((float)$p['preco'], 2, '.', '') ?>">
                                    <?= htmlspecialchars($p['nome']) ?> — <?= (int)$p['quantidade'] ?> em estoque
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span id="estoqueInfo" style="display:block;font-size:.74rem;margin-top:5px;color:var(--text-dim)"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vendaQuantidade">Quantidade *</label>
                        <input type="number" id="vendaQuantidade" name="quantidade" class="form-input" placeholder="1" min="1" value="1">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vendaValor">Valor (R$) *</label>
                        <input type="number" id="vendaValor" name="valor" class="form-input" placeholder="0.00" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vendaStatus">Status</label>
                        <select id="vendaStatus" name="status" class="form-select">
                            <option value="concluida">Concluída</option>
                            <option value="pendente">Pendente</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                </div>

                <div class="modal-foot">
                    <button type="button" class="btn-ghost" onclick="closeVendaModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-plus"></i>
                        Registrar Venda
                    </button>
                </div>
            </form>

        </div>
    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

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

    <script src="/assets/js/script.js"></script>
    <script src="/assets/js/vendas.js"></script>
</body>

</html>