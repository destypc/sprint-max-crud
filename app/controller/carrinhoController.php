<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Connection::getConnection();

garantirSessaoValida($pdo);
exigirCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {

    $id_produto = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));

    if ($id_produto > 0) {
        $stmt = $pdo->prepare("SELECT id, quantidade FROM produtos WHERE id = ? AND quantidade > 0");
        $stmt->execute([$id_produto]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            $quantidade_atual = (int) ($_SESSION['cart'][$id_produto] ?? 0);
            $nova_quantidade  = min($quantidade_atual + $quantidade, (int) $produto['quantidade']);

            if ($nova_quantidade > $quantidade_atual) {
                $_SESSION['cart'][$id_produto] = $nova_quantidade;
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produto adicionado ao carrinho!'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Estoque insuficiente.'];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Produto indisponível.'];
        }
    }

    // Redireciona de volta para a página de origem
    $redirect = filter_var($_POST['redirect'] ?? '/pages/home.php', FILTER_SANITIZE_URL);
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'remover') {
    $id_produto = (int) ($_POST['produto_id'] ?? 0);
    if ($id_produto > 0 && isset($_SESSION['cart'][$id_produto])) {
        unset($_SESSION['cart'][$id_produto]);
    }
    header('Location: ' . filter_var($_POST['redirect'] ?? '/pages/carrinho.php', FILTER_SANITIZE_URL));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {

    $id_produto = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 0);

    if ($id_produto > 0) {
        if ($quantidade <= 0) {
            unset($_SESSION['cart'][$id_produto]);
        } else {
            $_SESSION['cart'][$id_produto] = $quantidade;
        }
    }
    header('Location: /pages/carrinho.php');
    exit;
}

header('Location: /pages/home.php');
exit;
