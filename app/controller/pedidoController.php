<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = Connection::getConnection();

exigirCsrf();

$usuario_id = (int) $_SESSION['user']['id'];

// ── FINALIZAR COMPRA ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'finalizar') {

    if (empty($_SESSION['cart'])) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Seu carrinho está vazio.'];
        header('Location: /pages/carrinho.php');
        exit;
    }

    $ids          = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, nome, preco, quantidade AS estoque
        FROM produtos
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $mapa = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $mapa[$p['id']] = $p;
    }

    // Montar itens e verificar estoque
    $itens = [];
    $total = 0.0;

    foreach ($_SESSION['cart'] as $prod_id => $qtd) {
        $prod_id = (int) $prod_id;
        $qtd     = (int) $qtd;
        if (!isset($mapa[$prod_id]) || $qtd <= 0) continue;

        $prod = $mapa[$prod_id];
        if ($qtd > $prod['estoque']) {
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => "Estoque insuficiente para \"{$prod['nome']}\". Disponível: {$prod['estoque']} unidade(s).",
            ];
            header('Location: /pages/carrinho.php');
            exit;
        }

        $subtotal = round($prod['preco'] * $qtd, 2);
        $total   += $subtotal;
        $itens[]  = [
            'produto_id'     => $prod_id,
            'nome'           => $prod['nome'],
            'quantidade'     => $qtd,
            'preco_unitario' => $prod['preco'],
            'subtotal'       => $subtotal,
            'estoque_atual'  => $prod['estoque'],
        ];
    }

    if (empty($itens)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nenhum item válido no carrinho.'];
        header('Location: /pages/carrinho.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Criar pedido
        $pdo->prepare("INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, 'pendente')")
            ->execute([$usuario_id, $total]);
        $pedido_id = (int) $pdo->lastInsertId();

        // Inserir itens e diminuir estoque
        $stmtItem = $pdo->prepare("
            INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($itens as $item) {
            $stmtItem->execute([
                $pedido_id,
                $item['produto_id'],
                $item['quantidade'],
                $item['preco_unitario'],
                $item['subtotal'],
            ]);

            $novaQtd = $item['estoque_atual'] - $item['quantidade'];
            $pdo->prepare("UPDATE produtos SET quantidade = ? WHERE id = ?")
                ->execute([$novaQtd, $item['produto_id']]);
        }

        $pdo->commit();

        // Limpar carrinho
        $_SESSION['cart'] = [];

        // Notificação para o usuário
        try {
            $pdo->prepare("
                INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo)
                VALUES (?, 'Pedido realizado!', ?, 'sucesso')
            ")->execute([$usuario_id, "Seu pedido #{$pedido_id} foi recebido e está pendente."]);
        } catch (PDOException $e) { /* silencioso se a tabela não existir */
        }

        registrarLog(
            $pdo,
            'pedido_realizado',
            "Pedido #{$pedido_id} criado — R$ " . number_format($total, 2, ',', '.'),
            $usuario_id
        );

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Pedido #{$pedido_id} realizado com sucesso!"];
        header('Location: /pages/pedidos.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('pedidoController finalizar: ' . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao processar o pedido. Tente novamente.'];
        header('Location: /pages/carrinho.php');
        exit;
    }
}

// ── ATUALIZAR STATUS (admin) ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar_status') {

    if (($_SESSION['user']['tipo'] ?? '') !== 'admin') {
        header('Location: /pages/pedidos.php');
        exit;
    }

    $pedido_id  = (int) ($_POST['pedido_id'] ?? 0);
    $novoStatus = $_POST['status'] ?? '';
    $statusValidos = ['pendente', 'preparando', 'enviado', 'entregue', 'cancelado'];

    if ($pedido_id > 0 && in_array($novoStatus, $statusValidos, true)) {
        try {
            $stmtPed = $pdo->prepare("SELECT usuario_id FROM pedidos WHERE id = ?");
            $stmtPed->execute([$pedido_id]);
            $pedido = $stmtPed->fetch(PDO::FETCH_ASSOC);

            $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?")->execute([$novoStatus, $pedido_id]);

            if ($pedido) {
                $msgs = [
                    'preparando' => "Seu pedido #{$pedido_id} está sendo preparado.",
                    'enviado'    => "Seu pedido #{$pedido_id} foi enviado!",
                    'entregue'   => "Seu pedido #{$pedido_id} foi entregue. Obrigado!",
                    'cancelado'  => "Seu pedido #{$pedido_id} foi cancelado.",
                ];
                if (isset($msgs[$novoStatus])) {
                    try {
                        $pdo->prepare("
                            INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo)
                            VALUES (?, 'Atualização de pedido', ?, 'info')
                        ")->execute([$pedido['usuario_id'], $msgs[$novoStatus]]);
                    } catch (PDOException $e) { /* silencioso */
                    }
                }
            }

            registrarLog(
                $pdo,
                'status_pedido',
                "Pedido #{$pedido_id} → {$novoStatus}",
                $_SESSION['user']['id']
            );

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Pedido #{$pedido_id} atualizado para \"{$novoStatus}\"."];
        } catch (PDOException $e) {
            error_log('pedidoController status: ' . $e->getMessage());
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar status.'];
        }
    }

    header('Location: /pages/pedidos.php');
    exit;
}

// ── EXCLUIR PEDIDO (admin) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir_pedido') {

    if (($_SESSION['user']['tipo'] ?? '') !== 'admin') {
        header('Location: /pages/pedidos.php');
        exit;
    }

    $pedido_id = (int) ($_POST['pedido_id'] ?? 0);

    if ($pedido_id <= 0) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pedido inválido.'];
        header('Location: /pages/pedidos.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1) Remove os itens do pedido; 2) remove o pedido.
        // A ordem evita violação de chave estrangeira em pedido_itens.
        $pdo->prepare("DELETE FROM pedido_itens WHERE pedido_id = ?")->execute([$pedido_id]);
        $pdo->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$pedido_id]);

        $pdo->commit();

        registrarLog(
            $pdo,
            'exclusao_pedido',
            "Pedido #{$pedido_id} excluído",
            $_SESSION['user']['id'] ?? null
        );

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Pedido #{$pedido_id} excluído com sucesso!"];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('pedidoController excluir_pedido: ' . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao excluir o pedido. Tente novamente.'];
    }

    header('Location: /pages/pedidos.php');
    exit;
}

header('Location: /pages/pedidos.php');
exit;
