<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../../app/config/conexao.php';
require_once __DIR__ . '/../../app/config/helpers.php';

$conexao = Connection::getConnection();

// Alternar visibilidade do produto (botão olho na tabela)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'toggle_visivel') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmtV = $conexao->prepare("SELECT COALESCE(visivel, 1) FROM produtos WHERE id = ?");
            $stmtV->execute([$id]);
            $novo = ((int)$stmtV->fetchColumn()) === 1 ? 0 : 1;
            $conexao->prepare("UPDATE produtos SET visivel = ? WHERE id = ?")->execute([$novo, $id]);
            $msg = $novo === 1 ? 'Produto agora está visível na loja.' : 'Produto ocultado da loja.';
            $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar visibilidade.'];
        }
    }
    header('Location: /pages/produtos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            // Tenta remover imagem do disco (coluna pode não existir antes da migração)
            try {
                $stmtImg = $conexao->prepare("SELECT imagem FROM produtos WHERE id = ?");
                $stmtImg->execute([$id]);
                excluirImagem($stmtImg->fetchColumn() ?: null);
            } catch (PDOException $e) { /* coluna imagem não existe ainda */
            }

            // Remove referências em pedido_itens antes de excluir o produto
            // (FK ON DELETE RESTRICT impede exclusão se o produto tiver sido pedido)
            try {
                $conexao->prepare("DELETE FROM pedido_itens WHERE produto_id = ?")->execute([$id]);
            } catch (PDOException $e) { /* tabela pedido_itens pode não existir */
            }

            $conexao->prepare("DELETE FROM produtos WHERE id = ?")->execute([$id]);
            registrarLog($conexao, 'exclusao_produto', "Produto #{$id} excluído", $_SESSION['user']['id'] ?? null);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produto excluído com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao excluir produto.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Produto não encontrado.'];
    }
    header('Location: /pages/produtos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'editar') {

    $id         = (int) ($_POST['id'] ?? 0);
    $nome       = trim($_POST['nome']      ?? '');
    $marca      = trim($_POST['marca']     ?? '');
    $cor        = trim($_POST['cor']       ?? '');
    $tags       = trim($_POST['tags']      ?? '');
    $visivel    = in_array((int)($_POST['visivel'] ?? 1), [0, 1]) ? (int)$_POST['visivel'] : 1;
    $categoria  = trim($_POST['categoria'] ?? '');
    $preco      = trim(str_replace(',', '.', $_POST['preco'] ?? ''));
    $quantidade = (int) ($_POST['quantidade'] ?? 0);

    if ($id <= 0 || $nome === '' || $categoria === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preencha todos os campos obrigatórios.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if (!preg_match("#^[\pL\pN\s\\-.'()&/]+$#u", $nome)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nome inválido: não utilize símbolos especiais.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if (mb_strlen($nome) > 70) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nome muito longo (máximo 70 caracteres).'];
        header('Location: /pages/produtos.php');
        exit;
    }

    $preco = (float) $preco;
    if ($preco < 0.01 || $preco > 999999.99) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preço inválido. Informe um valor entre R$ 0,01 e R$ 999.999,99.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if ($quantidade < 0 || $quantidade > 99999) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Quantidade inválida. Informe um valor entre 0 e 99.999.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    // Upload de nova imagem (opcional na edição)
    $novaImagem = null;
    if (!empty($_FILES['imagem']['name'])) {
        $novaImagem = uploadImagem($_FILES['imagem']);
        if ($novaImagem === false) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Imagem inválida. Use JPG, PNG ou WEBP (máx. 5 MB, dimensões entre 300×300 e 4000×4000 px).'];
            header('Location: /pages/produtos.php');
            exit;
        }
    }

    try {
        // Tenta com colunas novas (marca, cor, imagem)
        try {
            if ($novaImagem !== null) {
                // Tenta remover imagem antiga do disco
                try {
                    $stmtImg = $conexao->prepare("SELECT imagem FROM produtos WHERE id = ?");
                    $stmtImg->execute([$id]);
                    excluirImagem($stmtImg->fetchColumn() ?: null);
                } catch (PDOException $e) { /* coluna imagem não existe ainda */
                }

                $conexao->prepare("UPDATE produtos SET nome=?, marca=?, cor=?, tags=?, visivel=?, categoria=?, preco=?, quantidade=?, imagem=? WHERE id=?")
                    ->execute([$nome, $marca, $cor, $tags, $visivel, $categoria, $preco, $quantidade, $novaImagem, $id]);
            } else {
                $conexao->prepare("UPDATE produtos SET nome=?, marca=?, cor=?, tags=?, visivel=?, categoria=?, preco=?, quantidade=? WHERE id=?")
                    ->execute([$nome, $marca, $cor, $tags, $visivel, $categoria, $preco, $quantidade, $id]);
            }
        } catch (PDOException $e) {
            // Fallback: banco sem migração — usa apenas colunas originais
            $conexao->prepare("UPDATE produtos SET nome=?, categoria=?, preco=?, quantidade=? WHERE id=?")
                ->execute([$nome, $categoria, $preco, $quantidade, $id]);
        }
        registrarLog($conexao, 'edicao_produto', "Produto \"{$nome}\" atualizado", $_SESSION['user']['id'] ?? null);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produto atualizado com sucesso!'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar produto.'];
    }
    header('Location: /pages/produtos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome       = trim($_POST['nome']      ?? '');
    $marca      = trim($_POST['marca']     ?? '');
    $cor        = trim($_POST['cor']       ?? '');
    $tags       = trim($_POST['tags']      ?? '');
    $visivel    = in_array((int)($_POST['visivel'] ?? 1), [0, 1]) ? (int)$_POST['visivel'] : 1;
    $categoria  = trim($_POST['categoria'] ?? '');
    $preco      = trim(str_replace(',', '.', $_POST['preco'] ?? ''));
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $descricao  = trim($_POST['descricao'] ?? '');

    if ($nome === '' || $categoria === '' || $preco === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preencha todos os campos obrigatórios.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if (!preg_match("#^[\pL\pN\s\\-.'()&/]+$#u", $nome)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nome inválido: não utilize símbolos especiais (@, #, $, !, etc.).'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if (mb_strlen($nome) > 70) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nome muito longo (máximo 70 caracteres).'];
        header('Location: /pages/produtos.php');
        exit;
    }

    $preco = (float) $preco;
    if ($preco < 0.01 || $preco > 999999.99) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preço inválido. Informe um valor entre R$ 0,01 e R$ 999.999,99.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    if ($quantidade < 0 || $quantidade > 99999) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Quantidade inválida. Informe um valor entre 0 e 99.999.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    // Upload de imagem (opcional)
    $imagem = null;
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = uploadImagem($_FILES['imagem']);
        if ($imagem === false) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Imagem inválida. Use JPG, PNG ou WEBP (máx. 5 MB).'];
            header('Location: /pages/produtos.php');
            exit;
        }
    }

    try {
        // Tenta com colunas novas (marca, cor, imagem)
        try {
            if ($imagem !== null) {
                $conexao->prepare("INSERT INTO produtos (nome, marca, cor, tags, visivel, categoria, preco, quantidade, descricao, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$nome, $marca, $cor, $tags, $visivel, $categoria, $preco, $quantidade, $descricao, $imagem]);
            } else {
                $conexao->prepare("INSERT INTO produtos (nome, marca, cor, tags, visivel, categoria, preco, quantidade, descricao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$nome, $marca, $cor, $tags, $visivel, $categoria, $preco, $quantidade, $descricao]);
            }
        } catch (PDOException $e) {
            // Fallback: banco sem migração — usa apenas colunas originais
            $conexao->prepare("INSERT INTO produtos (nome, categoria, preco, quantidade, descricao) VALUES (?, ?, ?, ?, ?)")
                ->execute([$nome, $categoria, $preco, $quantidade, $descricao]);
        }
        registrarLog($conexao, 'cadastro_produto', "Produto \"{$nome}\" cadastrado", $_SESSION['user']['id'] ?? null);
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao cadastrar produto. Tente novamente.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produto cadastrado com sucesso!'];
    header('Location: /pages/produtos.php');
    exit;
}

$usuario_logado = $_SESSION['user'];
$current_page   = 'produtos';
$page_title     = 'Produtos';
$trilhaNavegacao     = [['label' => 'Produtos']];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$busca      = trim($_GET['busca'] ?? '');
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 20;
$offset     = ($pagina - 1) * $por_pagina;
$total      = 0;

if ($busca !== '') {
    $like = "%{$busca}%";

    $count = $conexao->prepare("SELECT COUNT(*) FROM produtos WHERE nome LIKE :n OR categoria LIKE :c");
    $count->execute([':n' => $like, ':c' => $like]);
    $total = (int)$count->fetchColumn();

    $stmt = $conexao->prepare(
        "SELECT * FROM produtos WHERE nome LIKE :n OR categoria LIKE :c ORDER BY id DESC LIMIT :lim OFFSET :off"
    );
    $stmt->bindValue(':n',   $like,       PDO::PARAM_STR);
    $stmt->bindValue(':c',   $like,       PDO::PARAM_STR);
    $stmt->bindValue(':lim', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset,     PDO::PARAM_INT);
    $stmt->execute();
} else {
    $count = $conexao->query("SELECT COUNT(*) FROM produtos");
    $total = (int)$count->fetchColumn();

    $stmt = $conexao->prepare("SELECT * FROM produtos ORDER BY id DESC LIMIT :lim OFFSET :off");
    $stmt->bindValue(':lim', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset,     PDO::PARAM_INT);
    $stmt->execute();
}

$produtos       = $stmt->fetchAll();
$total_produtos = $total;
$total_paginas  = max(1, (int)ceil($total / $por_pagina));
$inicio         = $total > 0 ? $offset + 1 : 0;
$fim            = min($offset + $por_pagina, $total);

function precoFormatado(float $preco): string
{
    return 'R$ ' . number_format($preco, 2, ',', '.');
}
