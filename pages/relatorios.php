<?php
session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}
$usuario_logado = $_SESSION['user'];
$current_page   = 'relatorios';
$page_title     = 'Relatórios';
$breadcrumb     = [['label' => 'Relatórios']];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Relatórios</title>
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

            <!-- ── HERO ─────────────────────────────────────────── -->
            <div class="dash-hero">
                <h1>Relatórios</h1>
                <p>Visualize o histórico de acessos e atividades do sistema.</p>
            </div>

            <!-- ── ÚLTIMOS ACESSOS ───────────────────────────────── -->
            <div class="dash-access">
                <div class="dash-panel-head">
                    <div>
                        <div class="dash-panel-title">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            Últimos Acessos
                        </div>
                        <div class="dash-panel-sub">Histórico recente de entradas no sistema</div>
                    </div>
                </div>
                <div class="access-table-wrap">
                    <table class="access-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Data / Hora</th>
                                <th>Última Ação</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="access-user">
                                        <img class="access-avatar"
                                            src="https://ui-avatars.com/api/?name=Admin+Principal&background=F97316&color=fff&bold=true&size=80"
                                            alt="Admin Principal">
                                        <div>
                                            <div class="access-name">Admin Principal</div>
                                            <div class="access-email">admin@sprintmax.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-tipo admin">Admin</span></td>
                                <td>Hoje, 09:42</td>
                                <td><span class="badge-tipo admin">Login</span></td>
                                <td><span class="badge-status-online">Online</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="access-user">
                                        <img class="access-avatar"
                                            src="https://ui-avatars.com/api/?name=Carlos+Souza&background=a78bfa&color=fff&bold=true&size=80"
                                            alt="Carlos Souza">
                                        <div>
                                            <div class="access-name">Carlos Souza</div>
                                            <div class="access-email">carlos@sprintmax.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-tipo usuario">Usuário</span></td>
                                <td>Hoje, 08:55</td>
                                <td style="font-size:.82rem;color:var(--text-sub)">Editou produto</td>
                                <td><span class="badge-status-offline">Offline</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="access-user">
                                        <img class="access-avatar"
                                            src="https://ui-avatars.com/api/?name=Fernanda+Lima&background=4ade80&color=fff&bold=true&size=80"
                                            alt="Fernanda Lima">
                                        <div>
                                            <div class="access-name">Fernanda Lima</div>
                                            <div class="access-email">fernanda@sprintmax.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-tipo usuario">Usuário</span></td>
                                <td>Ontem, 17:30</td>
                                <td style="font-size:.82rem;color:var(--text-sub)">Registrou venda</td>
                                <td><span class="badge-status-offline">Offline</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="access-user">
                                        <img class="access-avatar"
                                            src="https://ui-avatars.com/api/?name=Ricardo+Mendes&background=60a5fa&color=fff&bold=true&size=80"
                                            alt="Ricardo Mendes">
                                        <div>
                                            <div class="access-name">Ricardo Mendes</div>
                                            <div class="access-email">ricardo@sprintmax.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-tipo usuario">Usuário</span></td>
                                <td>Ontem, 14:12</td>
                                <td style="font-size:.82rem;color:var(--text-sub)">Cadastrou usuário</td>
                                <td><span class="badge-status-offline">Offline</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- /dash-access -->

        </main>

    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/script.js"></script>
</body>

</html>