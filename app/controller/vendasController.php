<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Connection::getConnection();

// ── POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? 'cadastrar';

    /* Excluir venda */
    if ($acao === 'excluir_venda') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Restaurar estoque antes de excluir
                $stmtV = $pdo->prepare("SELECT produto_id, quantidade FROM vendas WHERE id = ?");
                $stmtV->execute([$id]);
                $v = $stmtV->fetch(PDO::FETCH_ASSOC);
                if ($v) {
                    $qtdFinal = (int)$pdo->query("SELECT quantidade FROM produtos WHERE id = {$v['produto_id']}")->fetchColumn() + $v['quantidade'];
                    $st = $qtdFinal === 0 ? 'sem_estoque' : ($qtdFinal <= 5 ? 'baixo_estoque' : 'ativo');
                    $pdo->prepare("UPDATE produtos SET quantidade = ?, status = ? WHERE id = ?")->execute([$qtdFinal, $st, $v['produto_id']]);
                }
                $pdo->prepare("DELETE FROM vendas WHERE id = ?")->execute([$id]);
                registrarLog($pdo, 'venda_excluida', "Venda #{$id} excluída", $_SESSION['user']['id'] ?? null);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Venda excluída com sucesso!'];
            } catch (PDOException $e) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao excluir venda.'];
            }
        }
        header('Location: /pages/vendas.php');
        exit;
    }

    /* Editar venda */
    if ($acao === 'editar_venda') {
        $id      = (int)   ($_POST['id']         ?? 0);
        $novaQtd = (int)   ($_POST['quantidade']  ?? 0);
        $novoVal = (float) ($_POST['valor']       ?? 0);
        $novoSt  = $_POST['status'] ?? 'pendente';

        if ($id <= 0 || $novaQtd <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Dados inválidos.'];
            header('Location: /pages/vendas.php');
            exit;
        }

        $stmtOld = $pdo->prepare("SELECT produto_id, quantidade FROM vendas WHERE id = ?");
        $stmtOld->execute([$id]);
        $vendaOld = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$vendaOld) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Venda não encontrada.'];
            header('Location: /pages/vendas.php');
            exit;
        }

        // Restaurar estoque antigo
        $estoqueAtual = (int)$pdo->query("SELECT quantidade FROM produtos WHERE id = {$vendaOld['produto_id']}")->fetchColumn();
        $estoqueRestaurado = $estoqueAtual + $vendaOld['quantidade'];

        if ($novaQtd > $estoqueRestaurado) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Estoque insuficiente. Disponível: {$estoqueRestaurado} unidade(s)."];
            header('Location: /pages/vendas.php');
            exit;
        }

        try {
            $qtdFinal = $estoqueRestaurado - $novaQtd;
            $prodSt   = $qtdFinal === 0 ? 'sem_estoque' : ($qtdFinal <= 5 ? 'baixo_estoque' : 'ativo');
            $pdo->prepare("UPDATE produtos SET quantidade = ?, status = ? WHERE id = ?")->execute([$qtdFinal, $prodSt, $vendaOld['produto_id']]);
            $pdo->prepare("UPDATE vendas SET quantidade = ?, valor = ?, status = ? WHERE id = ?")->execute([$novaQtd, $novoVal, $novoSt, $id]);
            registrarLog($pdo, 'venda_editada', "Venda #{$id} atualizada", $_SESSION['user']['id'] ?? null);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Venda atualizada com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar venda.'];
        }
        header('Location: /pages/vendas.php');
        exit;
    }

    /* Cadastrar venda */
    $cliente    = trim($_POST['cliente']    ?? '');
    $produto    = trim($_POST['produto']    ?? '');
    $quantidade = (int)   ($_POST['quantidade'] ?? 0);
    $valor      = (float) ($_POST['valor']      ?? 0);
    $status     = $_POST['status'] ?? 'pendente';

    if ($cliente === '' || $produto === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preencha todos os campos obrigatórios.'];
        header('Location: /pages/vendas.php');
        exit;
    }

    // Buscar usuário pelo nome
    $stmtU = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
    $stmtU->execute([$cliente]);
    $usuario = $stmtU->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Cliente não encontrado no sistema.'];
        header('Location: /pages/vendas.php');
        exit;
    }

    // Buscar produto pelo nome
    $stmtP = $pdo->prepare("SELECT id FROM produtos WHERE nome = ?");
    $stmtP->execute([$produto]);
    $produtoBanco = $stmtP->fetch(PDO::FETCH_ASSOC);

    if (!$produtoBanco) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Produto não encontrado no sistema.'];
        header('Location: /pages/vendas.php');
        exit;
    }

    // Verificar estoque disponível
    $estoqueAtual = (int) $pdo->query("SELECT quantidade FROM produtos WHERE id = {$produtoBanco['id']}")->fetchColumn();

    if ($quantidade <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'A quantidade deve ser maior que zero.'];
        header('Location: /pages/vendas.php');
        exit;
    }

    if ($estoqueAtual < $quantidade) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Estoque insuficiente. Apenas {$estoqueAtual} unidade(s) disponível(is) de \"{$produto}\"."];
        header('Location: /pages/vendas.php');
        exit;
    }

    try {
        $pdo->prepare("
            INSERT INTO vendas (usuario_id, produto_id, quantidade, valor, status, data_venda)
            VALUES (?, ?, ?, ?, ?, NOW())
        ")->execute([$usuario['id'], $produtoBanco['id'], $quantidade, $valor, $status]);

        // Deduzir estoque e recalcular status do produto
        $qtdFinal   = $estoqueAtual - $quantidade;
        $prodStatus = $qtdFinal === 0 ? 'sem_estoque' : ($qtdFinal <= 5 ? 'baixo_estoque' : 'ativo');
        $pdo->prepare("UPDATE produtos SET quantidade = ?, status = ? WHERE id = ?")->execute([$qtdFinal, $prodStatus, $produtoBanco['id']]);

        registrarLog($pdo, 'venda_cadastrada', "Venda de \"{$produto}\" para \"{$cliente}\" — R$ " . number_format($valor, 2, ',', '.'), $_SESSION['user']['id'] ?? null);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Venda cadastrada com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao cadastrar venda.'];
    }

    header('Location: /pages/vendas.php');
    exit;
}

// ── GET: buscar dados para a view ─────────────────────────────────

// Lista completa de vendas
$stmt = $pdo->query("
    SELECT
        vendas.id,
        vendas.quantidade,
        vendas.valor,
        vendas.status,
        vendas.data_venda,
        usuarios.nome AS cliente,
        produtos.nome  AS produto
    FROM vendas
    INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    INNER JOIN produtos  ON produtos.id  = vendas.produto_id
    ORDER BY vendas.id DESC
");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totais gerais
$totalVendas = count($vendas);
$faturamento = array_sum(array_column($vendas, 'valor'));
$ticketMedio = $totalVendas > 0 ? $faturamento / $totalVendas : 0;

// Vendas realizadas hoje (contagem)
$hoje = date('Y-m-d');
$vendasHoje = 0;
foreach ($vendas as $v) {
    if (date('Y-m-d', strtotime($v['data_venda'])) === $hoje) {
        $vendasHoje++;
    }
}

// Total em valor vendido hoje
$stmtHoje = $pdo->query("SELECT SUM(valor) AS total FROM vendas WHERE DATE(data_venda) = CURDATE()");
$totalHoje = (float) ($stmtHoje->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// Produto mais vendido
$stmtProd = $pdo->query("
    SELECT produtos.nome AS produto, SUM(vendas.quantidade) AS qtd
    FROM vendas
    INNER JOIN produtos ON produtos.id = vendas.produto_id
    GROUP BY produtos.id, produtos.nome
    ORDER BY qtd DESC
    LIMIT 1
");
$produtoMaisVendido = $stmtProd->fetch(PDO::FETCH_ASSOC) ?: null;

// Última venda realizada
$stmtUltima = $pdo->query("
    SELECT usuarios.nome AS cliente, vendas.data_venda
    FROM vendas
    INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    ORDER BY vendas.data_venda DESC
    LIMIT 1
");
$ultimaVenda = $stmtUltima->fetch(PDO::FETCH_ASSOC) ?: null;

// Cliente que mais comprou
$stmtCliente = $pdo->query("
    SELECT usuarios.nome AS cliente, COUNT(vendas.id) AS total_compras
    FROM vendas
    INNER JOIN usuarios ON usuarios.id = vendas.usuario_id
    GROUP BY usuarios.id, usuarios.nome
    ORDER BY total_compras DESC
    LIMIT 1
");
$clienteMaisComprou = $stmtCliente->fetch(PDO::FETCH_ASSOC) ?: null;

// Flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Listas para os selects do modal
$stmtCL = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
$clientesLista = $stmtCL->fetchAll(PDO::FETCH_ASSOC);

$stmtPL = $pdo->query("SELECT id, nome, preco, quantidade FROM produtos WHERE quantidade > 0 ORDER BY nome ASC");
$produtosLista = $stmtPL->fetchAll(PDO::FETCH_ASSOC);
