<?php require_once __DIR__ . '/../app/controller/usuariosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/../app/includes/theme-init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Usuários</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/theme.css">

    <style>
        /* ── Modal Visualizar Usuário ── */
        .uview-hero {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px 22px 20px;
            background: linear-gradient(135deg, rgba(249, 115, 22, .08), rgba(249, 115, 22, .02));
            border-bottom: 1px solid var(--border);
        }

        .uview-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 3px solid rgba(249, 115, 22, .35);
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .3);
        }

        .uview-hero-info {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .uview-nome {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            line-height: 1.2;
        }

        .uview-body {
            padding: 6px 0 4px;
        }

        .uview-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 22px;
            border-bottom: 1px solid var(--border);
        }

        .uview-row:last-child {
            border-bottom: none;
        }

        .uview-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .75rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .uview-label i {
            color: var(--orange);
            font-size: .7rem;
        }

        .uview-value {
            font-size: .84rem;
            font-weight: 500;
            color: var(--text-main);
            text-align: right;
            word-break: break-all;
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

        <!-- TOPBAR -->
        <?php require_once __DIR__ . '/../app/includes/header.php'; ?>

        <main class="conteudo-pagina">

            <div class="card">

                <div class="card-header">
                    <div class="card-title">
                        <h2><i class="fa-solid fa-users" style="color:var(--orange);margin-right:8px;font-size:.95rem"></i>Usuários</h2>
                        <p>Gerencie todos os usuários do sistema.</p>
                    </div>
                </div>

                <div class="card-body">

                    <!-- Toolbar -->
                    <div class="toolbar">
                        <div class="caixa-busca">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text"
                                id="searchInput"
                                class="entrada-busca"
                                placeholder="Pesquisar por nome, e-mail ou tipo..."
                                value="<?= htmlspecialchars($busca) ?>"
                                autocomplete="off">
                        </div>
                        <button class="botao-primario" onclick="openModal()">
                            <i class="fa-solid fa-plus"></i>
                            Novo Usuário
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="envoltorio-tabela">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="estado-vazio">
                                                <i class="fa-solid fa-users-slash"></i>
                                                <h4>Nenhum usuário encontrado</h4>
                                                <p><?= $busca ? 'Tente outro termo de pesquisa.' : 'Cadastre o primeiro usuário.' ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $u):
                                        $status_val = !empty($u['status']) ? $u['status'] : 'ativo';
                                        $uAvatar    = avatarUrl($u['nome']);
                                        $isSelf     = (int)$u['id'] === (int)$usuario_logado['id'];
                                        $uJson      = htmlspecialchars(json_encode([
                                            'id'         => $u['id'],
                                            'nome'       => $u['nome'],
                                            'email'      => $u['email'],
                                            'tipo'       => $u['tipo'],
                                            'status'     => $status_val,
                                            'avatar'     => $uAvatar,
                                            'created_at' => $u['created_at'] ?? null,
                                        ]), ENT_QUOTES);
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <img class="user-avatar"
                                                        src="<?= $uAvatar ?>"
                                                        alt="Avatar de <?= htmlspecialchars($u['nome']) ?>">
                                                    <div>
                                                        <div class="user-name"><?= htmlspecialchars($u['nome']) ?></div>
                                                        <div class="user-email">#<?= $u['id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= tipoBadge($u['tipo']) ?></td>
                                            <td><?= statusBadge($status_val) ?></td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-icon"
                                                        title="Visualizar"
                                                        onclick='openViewModal(<?= $uJson ?>)'>
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                    <button class="btn-icon edit"
                                                        title="Editar"
                                                        onclick='openDrawer(<?= $uJson ?>)'>
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <?php if (!$isSelf): ?>
                                                        <button class="btn-icon del"
                                                            title="Excluir"
                                                            onclick="confirmDelete(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nome'])) ?>')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn-icon del" title="Não pode excluir a si mesmo" disabled style="opacity:.3;cursor:not-allowed">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div><!-- /card-body -->

                <!-- Pagination -->
                <?php if ($total > 0): ?>
                    <div class="pagination">
                        <div class="pagination-info">
                            Mostrando <strong><?= $inicio ?>–<?= $fim ?></strong> de <strong><?= $total ?></strong> usuários
                        </div>
                        <div class="pagination-btns">
                            <?php
                            // Botão anterior
                            $prevDisabled = $pagina <= 1 ? 'disabled' : '';
                            $prevHref     = '?pagina=' . ($pagina - 1) . ($busca ? '&busca=' . urlencode($busca) : '');
                            ?>
                            <button class="page-btn" <?= $prevDisabled ?>
                                <?= !$prevDisabled ? "onclick=\"location.href='{$prevHref}'\"" : '' ?>>
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <?php
                            // Páginas numeradas (máx 5)
                            $start = max(1, min($pagina - 2, $total_paginas - 4));
                            $end   = min($total_paginas, $start + 4);
                            for ($p = $start; $p <= $end; $p++):
                                $href    = '?pagina=' . $p . ($busca ? '&busca=' . urlencode($busca) : '');
                                $active  = $p === $pagina ? 'active' : '';
                            ?>
                                <button class="page-btn <?= $active ?>"
                                    <?= !$active ? "onclick=\"location.href='{$href}'\"" : '' ?>>
                                    <?= $p ?>
                                </button>
                            <?php endfor; ?>

                            <?php
                            // Botão próximo
                            $nextDisabled = $pagina >= $total_paginas ? 'disabled' : '';
                            $nextHref     = '?pagina=' . ($pagina + 1) . ($busca ? '&busca=' . urlencode($busca) : '');
                            ?>
                            <button class="page-btn" <?= $nextDisabled ?>
                                <?= !$nextDisabled ? "onclick=\"location.href='{$nextHref}'\"" : '' ?>>
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /card -->

        </main><!-- /conteudo-pagina -->

    </div><!-- /conteiner-principal -->



    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

    <div class="drawer" id="editDrawer" role="dialog" aria-modal="true" aria-label="Editar usuário">

        <div class="drawer-head">
            <h3><i class="fa-solid fa-pen" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Editar Usuário</h3>
            <button class="btn-close-drawer" onclick="closeDrawer()" aria-label="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="drawer-body">
            <div class="drawer-avatar-wrap">
                <img class="drawer-avatar" id="drawerAvatar" src="" alt="Avatar">
                <span class="drawer-username" id="drawerUsername"></span>
            </div>

            <form id="editForm" method="POST" action="/app/controller/usuariosController.php">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="editId">

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editNome">Nome completo</label>
                    <input type="text" id="editNome" name="nome" class="entrada-formulario"
                        placeholder="Nome do usuário" required maxlength="120">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editEmail">E-mail</label>
                    <input type="email" id="editEmail" name="email" class="entrada-formulario"
                        placeholder="email@exemplo.com" required maxlength="180">
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editTipo">Tipo / Cargo</label>
                    <select id="editTipo" name="tipo" class="selecao-formulario">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editStatus">Status</label>
                    <select id="editStatus" name="status" class="selecao-formulario">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label class="rotulo-formulario" for="editSenha">Nova senha <span style="color:var(--text-dim);font-weight:400;text-transform:none">(deixe em branco para manter)</span></label>
                    <input type="password" id="editSenha" name="senha" class="entrada-formulario"
                        placeholder="••••••••" minlength="6" autocomplete="new-password">
                    <span class="form-hint">Mínimo 6 caracteres.</span>
                </div>
        </div>

        <div class="drawer-foot">
            <button type="button" class="btn-cancel-drawer" onclick="closeDrawer()">Cancelar</button>
            <button type="submit" class="btn-save-drawer" form="editForm">
                <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Salvar Alterações
            </button>
        </div>

        </form>
    </div>



    <div class="fundo-modal" id="modalBackdrop" onclick="handleModalClick(event)">
        <div class="modal" role="dialog" aria-modal="true" aria-label="Novo usuário">

            <div class="cabecalho-modal">
                <h3><i class="fa-solid fa-user-plus" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Novo Usuário</h3>
                <button class="botao-fechar-modal" onclick="closeModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="/app/controller/usuariosController.php" id="createForm">
                <input type="hidden" name="acao" value="criar">

                <div class="corpo-modal">

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarNome">Nome completo *</label>
                        <input type="text" id="criarNome" name="nome" class="entrada-formulario"
                            placeholder="Nome do usuário" required maxlength="120">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarEmail">E-mail *</label>
                        <input type="email" id="criarEmail" name="email" class="entrada-formulario"
                            placeholder="email@exemplo.com" required maxlength="180">
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarSenha">Senha *</label>
                        <input type="password" id="criarSenha" name="senha" class="entrada-formulario"
                            placeholder="••••••••" required minlength="6" autocomplete="new-password">
                        <span class="form-hint">Mínimo 6 caracteres.</span>
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarTipo">Tipo</label>
                        <select id="criarTipo" name="tipo" class="selecao-formulario">
                            <option value="usuario">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="grupo-formulario">
                        <label class="rotulo-formulario" for="criarStatus">Status</label>
                        <select id="criarStatus" name="status" class="selecao-formulario">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                </div>

                <div class="rodape-modal">
                    <button type="button" class="botao-secundario" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="botao-primario">
                        <i class="fa-solid fa-plus"></i>
                        Criar Usuário
                    </button>
                </div>
            </form>

        </div>
    </div>



    <form id="deleteForm" method="POST" action="/app/controller/usuariosController.php" style="display:none">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteId">
    </form>



    <!-- Modal Visualizar Usuário -->
    <div class="fundo-modal" id="viewModalBackdrop" onclick="handleViewModalClick(event)">
        <div class="modal" role="dialog" aria-modal="true" aria-label="Detalhes do usuário"
            style="max-width:420px" onclick="event.stopPropagation()">

            <div class="cabecalho-modal">
                <h3><i class="fa-solid fa-eye" style="color:var(--orange);margin-right:8px;font-size:.85rem"></i>Detalhes do Usuário</h3>
                <button class="botao-fechar-modal" onclick="closeViewModal()" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="corpo-modal" style="gap:0;padding:0">

                <!-- Cabeçalho com avatar -->
                <div class="uview-hero">
                    <img class="uview-avatar" id="viewAvatar" src="" alt="Avatar">
                    <div class="uview-hero-info">
                        <h4 class="uview-nome" id="viewNome"></h4>
                        <div id="viewTipoBadge"></div>
                    </div>
                </div>

                <!-- Linhas de info -->
                <div class="uview-body">
                    <div class="uview-row">
                        <span class="uview-label"><i class="fa-solid fa-hashtag"></i> ID</span>
                        <span class="uview-value" id="viewId"></span>
                    </div>
                    <div class="uview-row">
                        <span class="uview-label"><i class="fa-solid fa-envelope"></i> E-mail</span>
                        <span class="uview-value" id="viewEmail"></span>
                    </div>
                    <div class="uview-row">
                        <span class="uview-label"><i class="fa-solid fa-circle-dot"></i> Status</span>
                        <span id="viewStatusBadge"></span>
                    </div>
                    <div class="uview-row" id="viewCreatedRow">
                        <span class="uview-label"><i class="fa-solid fa-calendar"></i> Cadastrado em</span>
                        <span class="uview-value" id="viewCreated"></span>
                    </div>
                </div>

            </div>

            <div class="rodape-modal">
                <button type="button" class="botao-secundario" onclick="closeViewModal()">Fechar</button>
                <button type="button" class="botao-primario" id="viewEditBtn">
                    <i class="fa-solid fa-pen"></i>
                    Editar
                </button>
            </div>
        </div>
    </div>

    <div class="sp-toast" id="spToast"
        <?php if ($flash): ?> data-flash-msg="<?= htmlspecialchars($flash['msg']) ?>" data-flash-type="<?= $flash['tipo'] === 'sucesso' ? 'success' : 'error' ?>" <?php endif; ?>>
        <i class="fa-solid fa-circle-check" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>


    <script src="/assets/js/usuarios.js"></script>

    <!-- Modal Editar Perfil -->
    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>