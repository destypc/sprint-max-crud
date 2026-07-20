<?php require_once __DIR__ . '/../app/controller/usuariosController.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<?php require __DIR__ . '/../app/includes/head.php'; ?>

<body>

    <?php require_once __DIR__ . '/../app/includes/sidebar.php'; ?>

    <div class="conteiner-principal">

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

                    <div class="toolbar">
                        <div class="caixa-busca">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" class="entrada-busca"
                                placeholder="Pesquisar por nome, e-mail ou tipo..."
                                value="<?= htmlspecialchars($busca) ?>" autocomplete="off">
                        </div>
                        <button class="botao-primario" onclick="openModal()">
                            <i class="fa-solid fa-plus"></i>
                            Novo Usuário
                        </button>
                    </div>

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
                                <?php foreach ($usuarios as $u): ?>
                                <?php
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
                                            <img class="user-avatar" src="<?= $uAvatar ?>"
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
                                            <button class="btn-icon" aria-label="Visualizar usuário" title="Visualizar" onclick='openViewModal(<?= $uJson ?>)'>
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn-icon edit" aria-label="Editar usuário" title="Editar" onclick='openDrawer(<?= $uJson ?>)'>
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <?php if ($isSelf): ?>
                                            <button class="btn-icon del" title="Não pode excluir a si mesmo" disabled style="opacity:.3;cursor:not-allowed">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                            <?php elseif ($u['tipo'] === 'admin' && !ehSuperAdmin()): ?>
                                            <button class="btn-icon del" title="Apenas o administrador principal pode excluir administradores" disabled style="opacity:.3;cursor:not-allowed">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn-icon del" aria-label="Excluir usuário" title="Excluir"
                                                onclick="confirmDelete(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nome'])) ?>')">
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

                </div>

                <?php if ($total > 0): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Mostrando <strong><?= $inicio ?>–<?= $fim ?></strong> de <strong><?= $total ?></strong> usuários
                    </div>
                    <div class="pagination-btns">
                        <?php
                        $prevDisabled = $pagina <= 1 ? 'disabled' : '';
                        $prevHref     = '?pagina=' . ($pagina - 1) . ($busca ? '&busca=' . urlencode($busca) : '');
                        ?>
                        <button class="page-btn" <?= $prevDisabled ?>
                            <?= !$prevDisabled ? "onclick=\"location.href='{$prevHref}'\"" : '' ?>>
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <?php
                        // Mostra no máximo 5 páginas centradas na atual
                        $start = max(1, min($pagina - 2, $total_paginas - 4));
                        $end   = min($total_paginas, $start + 4);
                        for ($p = $start; $p <= $end; $p++):
                            $href   = '?pagina=' . $p . ($busca ? '&busca=' . urlencode($busca) : '');
                            $active = $p === $pagina ? 'active' : '';
                        ?>
                        <button class="page-btn <?= $active ?>"
                            <?= !$active ? "onclick=\"location.href='{$href}'\"" : '' ?>>
                            <?= $p ?>
                        </button>
                        <?php endfor; ?>

                        <?php
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

            </div>

        </main>

    </div>

    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

    <!-- Drawer: editar usuário -->
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

    <!-- Modal: novo usuário -->
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

    <!-- Form oculto para exclusão -->
    <form id="deleteForm" method="POST" action="/app/controller/usuariosController.php" style="display:none">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Modal reutilizável de confirmação de exclusão -->
    <?php require_once __DIR__ . '/../app/includes/modal-exclusao.php'; ?>

    <!-- Modal: visualizar usuário -->
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

                <div class="uview-hero">
                    <img class="uview-avatar" id="viewAvatar" src="" alt="Avatar">
                    <div class="uview-hero-info">
                        <h4 class="uview-nome" id="viewNome"></h4>
                        <div id="viewTipoBadge"></div>
                    </div>
                </div>

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

    <?php require __DIR__ . '/../app/includes/toast.php'; ?>

    <script src="/assets/js/usuarios.js"></script>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

</body>

</html>
