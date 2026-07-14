<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';

$pdo = Connection::getConnection();
$id_usuario = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'marcar_lidas') {
    try {
        $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = ?")
            ->execute([$id_usuario]);
    } catch (PDOException $e) { /* silencioso */
    }

    $redirect = filter_var($_POST['redirect'] ?? '/pages/home.php', FILTER_SANITIZE_URL);
    header('Location: ' . $redirect);
    exit;
}

$redirect = filter_var($_SERVER['HTTP_REFERER'] ?? '/pages/home.php', FILTER_SANITIZE_URL);
header('Location: ' . $redirect);
exit;
