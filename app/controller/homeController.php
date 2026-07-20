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

$usuario_logado = $_SESSION['user'];
$current_page   = 'home';
$page_title     = 'Loja';
$trilhaNavegacao     = [];
$mensagem_flash  = $_SESSION['flash'] ?? null;
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

// IDs dos produtos favoritados — recurso exclusivo de usuários comuns.
// (tabela existe após migração do banco)
$ids_favoritos = [];
if (($usuario_logado['tipo'] ?? '') !== 'admin') {
    try {
        $stmtFav = $pdo->prepare("SELECT produto_id FROM favoritos WHERE usuario_id = ?");
        $stmtFav->execute([$usuario_logado['id']]);
        $ids_favoritos = array_map('intval', array_column($stmtFav->fetchAll(PDO::FETCH_ASSOC), 'produto_id'));
    } catch (PDOException $e) { /* tabela favoritos não existe ainda */
    }
}
