<?php

/**
 * dashboardController.php — Sprint Max
 * Busca todos os dados reais do banco para a Dashboard.
 */

session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

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
} catch (PDOException $e) { /* ignora se já existir ou não for possível */ }

// Variáveis da página
$usuario_logado = $_SESSION['user'];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$current_page   = 'dashboard';
$page_title     = 'Dashboard';
$breadcrumb     = [];

// ── Cards superiores ──────────────────────────────────────────
$totalUsuarios = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalProdutos = (int) $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totalVendas   = (int) $pdo->query("SELECT COUNT(*) FROM vendas")->fetchColumn();
$totalAdmins   = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'")->fetchColumn();

// ── Últimas 5 vendas com JOIN ─────────────────────────────────
$stmtVR = $pdo->query("
    SELECT
        produtos.nome AS produto,
        usuarios.nome AS cliente,
        vendas.valor,
        vendas.status,
        vendas.data_venda
    FROM vendas
    INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    INNER JOIN produtos  ON produtos.id  = vendas.produto_id
    ORDER BY vendas.data_venda DESC
    LIMIT 5
");
$vendasRecentes = $stmtVR->fetchAll(PDO::FETCH_ASSOC);

// ── Últimas 5 atividades do log ───────────────────────────────
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
} catch (PDOException $e) { /* tabela logs ainda não criada */ }

// ── Indicadores adicionais ────────────────────────────────────
$totalFaturamento = (float) $pdo->query("SELECT COALESCE(SUM(valor),0) FROM vendas")->fetchColumn();
$ticketMedio      = $totalVendas > 0 ? $totalFaturamento / $totalVendas : 0;
$vendasHoje       = (int)   $pdo->query("SELECT COUNT(*) FROM vendas WHERE DATE(data_venda) = CURDATE()")->fetchColumn();
$valorHoje        = (float) $pdo->query("SELECT COALESCE(SUM(valor),0) FROM vendas WHERE DATE(data_venda) = CURDATE()")->fetchColumn();

$stmtPMV = $pdo->query("
    SELECT produtos.nome AS nome, SUM(vendas.quantidade) AS total
    FROM vendas INNER JOIN produtos ON produtos.id = vendas.produto_id
    GROUP BY produtos.id, produtos.nome ORDER BY total DESC LIMIT 1
");
$produtoMaisVendido = $stmtPMV->fetch(PDO::FETCH_ASSOC) ?: null;

$stmtCMC = $pdo->query("
    SELECT usuarios.nome AS nome, COUNT(vendas.id) AS total
    FROM vendas INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    GROUP BY usuarios.id, usuarios.nome ORDER BY total DESC LIMIT 1
");
$clienteMaisComprou = $stmtCMC->fetch(PDO::FETCH_ASSOC) ?: null;

$stmtUV = $pdo->query("
    SELECT usuarios.nome AS cliente, vendas.data_venda, vendas.valor
    FROM vendas INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    ORDER BY vendas.data_venda DESC LIMIT 1
");
$ultimaVenda = $stmtUV->fetch(PDO::FETCH_ASSOC) ?: null;

// ── Funções auxiliares para a view ───────────────────────────

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
        'cadastro_usuario' => ['green',  'fa-user-plus'],
        'edicao_usuario'   => ['purple', 'fa-pen'],
        'cadastro_produto' => ['orange', 'fa-box'],
        'edicao_produto'   => ['orange', 'fa-pen'],
        'exclusao_produto' => ['red',    'fa-trash'],
        'venda_cadastrada' => ['blue',   'fa-circle-dollar-to-slot'],
        'venda_editada'    => ['purple', 'fa-pen'],
        'venda_excluida'   => ['red',    'fa-trash'],
        'login'            => ['green',  'fa-right-to-bracket'],
        default            => ['gray',   'fa-bell'],
    };
}

function vendaDotCor(string $status): string
{
    return match ($status) {
        'concluida' => 'green',
        'pendente'  => 'orange',
        'cancelada' => 'red',
        default     => 'blue',
    };
}

function vendaStatusBadge(string $status): string
{
    return match ($status) {
        'concluida' => '<span class="badge-status-online" style="font-size:.65rem">Concluída</span>',
        'pendente'  => '<span class="badge-status-offline" style="font-size:.65rem">Pendente</span>',
        'cancelada' => '<span style="font-size:.65rem;color:var(--red)">Cancelada</span>',
        default     => '<span style="font-size:.65rem">' . htmlspecialchars($status) . '</span>',
    };
}
