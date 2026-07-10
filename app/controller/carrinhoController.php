<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Connection::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar') {

    $produto_id = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));

    if ($produto_id > 0) {
        $stmt = $pdo->prepare("SELECT id, quantidade FROM produtos WHERE id = ? AND status != 'sem_estoque'");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            $qtdAtual = (int) ($_SESSION['cart'][$produto_id] ?? 0);
            $novaQtd  = min($qtdAtual + $quantidade, (int) $produto['quantidade']);

            if ($novaQtd > $qtdAtual) {
                $_SESSION['cart'][$produto_id] = $novaQtd;
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

    $produto_id = (int) ($_POST['produto_id'] ?? 0);
    if ($produto_id > 0 && isset($_SESSION['cart'][$produto_id])) {
        unset($_SESSION['cart'][$produto_id]);
    }
    header('Location: ' . filter_var($_POST['redirect'] ?? '/pages/carrinho.php', FILTER_SANITIZE_URL));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {

    $produto_id = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 0);

    if ($produto_id > 0) {
        if ($quantidade <= 0) {
            unset($_SESSION['cart'][$produto_id]);
        } else {
            $_SESSION['cart'][$produto_id] = $quantidade;
        }
    }
    header('Location: /pages/carrinho.php');
    exit;
}

header('Location: /pages/home.php');
exit;
