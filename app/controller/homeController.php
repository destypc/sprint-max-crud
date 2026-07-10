<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Connection::getConnection();

$usuario_logado = $_SESSION['user'];
$current_page   = 'home';
$page_title     = 'Loja';
$breadcrumb     = [];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Filtra produtos visíveis; fallback para todos se coluna não existir ainda
try {
    $stmt = $pdo->query("SELECT * FROM produtos WHERE visivel = 1 ORDER BY nome ASC");
} catch (PDOException $e) {
    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC");
}
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorias únicas para o filtro de categorias
$categorias = array_values(array_unique(array_column($produtos, 'categoria')));
sort($categorias);

// Contagem de itens no carrinho (para badge na sidebar)
$cartTotal = 0;
if (!empty($_SESSION['cart'])) {
    $cartTotal = array_sum($_SESSION['cart']);
}

// IDs dos produtos favoritados (tabela existe após migração do banco)
$favoritoIds = [];
try {
    $stmtFav = $pdo->prepare("SELECT produto_id FROM favoritos WHERE usuario_id = ?");
    $stmtFav->execute([$usuario_logado['id']]);
    $favoritoIds = array_map('intval', array_column($stmtFav->fetchAll(PDO::FETCH_ASSOC), 'produto_id'));
} catch (PDOException $e) { /* tabela favoritos não existe ainda */
}
