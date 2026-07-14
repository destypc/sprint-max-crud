<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';

$pdo = Connection::getConnection();

// Cria a tabela de logs se ainda não existir
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS logs (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            acao       VARCHAR(50)  NOT NULL,
            descricao  VARCHAR(255) NOT NULL,
            data       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) { /* ignora se já existir ou não for possível */
}

$usuario_logado = $_SESSION['user'];
unset($_SESSION['flash']);
$current_page   = 'dashboard';
$page_title     = 'Dashboard';
$breadcrumb     = [];

$totalUsuarios = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalProdutos = (int) $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totalAdmins   = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'")->fetchColumn();

// Queries que dependem das tabelas criadas pela migração
$totalPedidos    = 0;
$pedidosRecentes = [];

try {
    $totalPedidos = (int) $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();

    $stmtPR = $pdo->query("
        SELECT
            pedidos.id,
            pedidos.total,
            pedidos.status,
            pedidos.created_at,
            usuarios.nome AS cliente
        FROM pedidos
        INNER JOIN usuarios ON usuarios.id = pedidos.usuario_id
        ORDER BY pedidos.created_at DESC
        LIMIT 5
    ");
    $pedidosRecentes = $stmtPR->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tabelas de pedidos ainda não existem — rodar banco-migration.sql
    error_log('dashboardController: tabela pedidos não encontrada. ' . $e->getMessage());
}

$logsRecentes = [];
try {
    $stmtLog = $pdo->query("
        SELECT logs.acao, logs.descricao, logs.data,
               COALESCE(usuarios.nome, 'Sistema') AS usuario
        FROM logs
        LEFT JOIN usuarios ON usuarios.id = logs.usuario_id
        ORDER BY logs.data DESC
        LIMIT 5
    ");
    $logsRecentes = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* tabela logs ainda não criada */
}

function timeAgo(string $date): string
{
    $diff = time() - strtotime($date);
    if ($diff < 60)     return 'Agora mesmo';
    if ($diff < 3600)   return 'Há ' . floor($diff / 60) . ' min';
    if ($diff < 86400)  return 'Há ' . floor($diff / 3600) . ' h';
    if ($diff < 172800) return 'Ontem, ' . date('H:i', strtotime($date));
    return date('d/m/Y H:i', strtotime($date));
}

function logIcone(string $acao): array
{
    return match ($acao) {
        'cadastro_usuario'  => ['green',  'fa-user-plus'],
        'edicao_usuario'    => ['purple', 'fa-pen'],
        'cadastro_produto'  => ['orange', 'fa-box'],
        'edicao_produto'    => ['orange', 'fa-pen'],
        'exclusao_produto'  => ['red',    'fa-trash'],
        'pedido_realizado'  => ['blue',   'fa-bag-shopping'],
        'status_pedido'     => ['purple', 'fa-rotate'],
        'venda_cadastrada'  => ['blue',   'fa-circle-dollar-to-slot'],
        'venda_editada'     => ['purple', 'fa-pen'],
        'venda_excluida'    => ['red',    'fa-trash'],
        'login'             => ['green',  'fa-right-to-bracket'],
        default             => ['gray',   'fa-bell'],
    };
}

function pedidoDotCor(string $status): string
{
    return match ($status) {
        'entregue'   => 'green',
        'enviado'    => 'blue',
        'preparando' => 'orange',
        'cancelado'  => 'red',
        default      => 'orange',
    };
}

function pedidoStatusBadge(string $status): string
{
    return match ($status) {
        'pendente'   => '<span class="badge badge-yellow"  style="font-size:.65rem">Pendente</span>',
        'preparando' => '<span class="badge badge-orange"  style="font-size:.65rem">Preparando</span>',
        'enviado'    => '<span class="badge badge-blue"    style="font-size:.65rem">Enviado</span>',
        'entregue'   => '<span class="badge badge-green"   style="font-size:.65rem">Entregue</span>',
        'cancelado'  => '<span class="badge badge-red"     style="font-size:.65rem">Cancelado</span>',
        default      => '<span style="font-size:.65rem">' . htmlspecialchars($status) . '</span>',
    };
}
