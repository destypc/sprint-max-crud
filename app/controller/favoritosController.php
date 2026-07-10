<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';

$pdo = Connection::getConnection();
$uid = (int) $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'toggle') {

    $produto_id = (int) ($_POST['produto_id'] ?? 0);

    if ($produto_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND produto_id = ?");
        $stmt->execute([$uid, $produto_id]);

        if ($stmt->fetchColumn()) {
            $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND produto_id = ?")
                ->execute([$uid, $produto_id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Removido dos favoritos.'];
        } else {
            $pdo->prepare("INSERT INTO favoritos (usuario_id, produto_id) VALUES (?, ?)")
                ->execute([$uid, $produto_id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Adicionado aos favoritos!'];
        }
    }

    $redirect = filter_var($_POST['redirect'] ?? '/pages/home.php', FILTER_SANITIZE_URL);
    header('Location: ' . $redirect);
    exit;
}

header('Location: /pages/home.php');
exit;
