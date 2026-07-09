<?php
session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}
$usuario_logado = $_SESSION['user'];
$current_page   = 'configuracoes';
$page_title     = 'Configurações';
$breadcrumb     = [['label' => 'Configurações']];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Configurações</title>
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
            <div class="empty-state" style="margin-top:60px">
                <i class="fa-solid fa-gear"></i>
                <h4>Configurações em breve</h4>
                <p>Esta seção está sendo desenvolvida.</p>
            </div>
        </main>

    </div>

    <?php require_once __DIR__ . '/../app/includes/modal-perfil.php'; ?>

    <script src="/assets/js/script.js"></script>
</body>

</html>