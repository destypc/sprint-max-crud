<?php

session_start();

if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../../app/config/conexao.php';
require_once __DIR__ . '/../../app/config/helpers.php';

$conexao = Connection::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? 'cadastrar';

    /* ── Excluir ─────────────────────────────────────────────── */
    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
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

    /* ── Editar ──────────────────────────────────────────────── */
    if ($acao === 'editar') {
        $id        = (int) ($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome']      ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $preco     = trim(str_replace(',', '.', $_POST['preco'] ?? ''));
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

        if ($quantidade === 0) {
            $status = 'sem_estoque';
        } elseif ($quantidade <= 5) {
            $status = 'baixo_estoque';
        } else {
            $status = 'ativo';
        }

        try {
            $sql = $conexao->prepare("
                UPDATE produtos SET nome=?, categoria=?, preco=?, quantidade=?, status=?
                WHERE id=?
            ");
            $sql->execute([$nome, $categoria, $preco, $quantidade, $status, $id]);
            registrarLog($conexao, 'edicao_produto', "Produto \"{$nome}\" atualizado", $_SESSION['user']['id'] ?? null);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produto atualizado com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar produto.'];
        }
        header('Location: /pages/produtos.php');
        exit;
    }

    /* ── Cadastrar ───────────────────────────────────────────── */
    $nome      = trim($_POST['nome']      ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco     = trim(str_replace(',', '.', $_POST['preco'] ?? ''));
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');

    // Campos obrigatórios
    if ($nome === '' || $categoria === '' || $preco === '') {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preencha todos os campos obrigatórios.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    // Nome: apenas letras (incluindo acentuadas), números, espaços e pontuação básica
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

    // Preço: entre R$ 0,01 e R$ 999.999,99
    $preco = (float) $preco;
    if ($preco < 0.01 || $preco > 999999.99) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Preço inválido. Informe um valor entre R$ 0,01 e R$ 999.999,99.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    // Quantidade: entre 0 e 99.999
    if ($quantidade < 0 || $quantidade > 99999) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Quantidade inválida. Informe um valor entre 0 e 99.999.'];
        header('Location: /pages/produtos.php');
        exit;
    }

    // Status automático
    if ($quantidade === 0) {
        $status = 'sem_estoque';
    } elseif ($quantidade <= 5) {
        $status = 'baixo_estoque';
    } else {
        $status = 'ativo';
    }

    try {
        $sql = $conexao->prepare("
            INSERT INTO produtos (nome, categoria, preco, quantidade, descricao, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $sql->execute([$nome, $categoria, $preco, $quantidade, $descricao, $status]);
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
$breadcrumb     = [['label' => 'Produtos']];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Pesquisa

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

// Bucando produtos nos bancos

function statusBadgeProduto(string $status): string
{
    return match ($status) {
        'ativo'         => '<span class="badge badge-green">Ativo</span>',
        'baixo_estoque' => '<span class="badge badge-yellow">Baixo estoque</span>',
        'sem_estoque'   => '<span class="badge badge-red">Sem estoque</span>',
        default         => '<span class="badge badge-orange">' . htmlspecialchars($status) . '</span>',
    };
}

function precoFormatado(float $preco): string
{
    return 'R$ ' . number_format($preco, 2, ',', '.');
}
