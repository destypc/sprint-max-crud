<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../../app/config/conexao.php';

$conexao = Connection::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {

    $nome   = trim($_POST['nome']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $senha  = $_POST['senha']       ?? '';
    $tipo   = $_POST['tipo']        ?? 'usuario';
    $status = $_POST['status']      ?? 'ativo';

    if (empty($nome) || empty($email) || empty($senha)) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Preencha todos os campos obrigatórios.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'E-mail inválido.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    if (strlen($senha) < 6) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'A senha deve ter pelo menos 6 caracteres.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    $tipo   = in_array($tipo,   ['admin', 'usuario']) ? $tipo   : 'usuario';
    $status = in_array($status, ['ativo', 'inativo']) ? $status : 'ativo';

    try {
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Este e-mail já está cadastrado.'];
            header('Location: /pages/usuarios.php');
            exit;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // Tenta inserir com coluna status (fallback sem status se a coluna não existir)
        try {
            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha, tipo, status) VALUES (:nome, :email, :senha, :tipo, :status)"
            );
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash, ':tipo' => $tipo, ':status' => $status]);
        } catch (PDOException $e2) {
            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)"
            );
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash, ':tipo' => $tipo]);
        }

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'msg' => "Usuário {$nome} criado com sucesso."];
    } catch (PDOException $e) {
        error_log("usuariosController criar: " . $e->getMessage());
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Erro ao criar usuário. Tente novamente.'];
    }

    header('Location: /pages/usuarios.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'editar') {

    $id     = (int)($_POST['id']    ?? 0);
    $nome   = trim($_POST['nome']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $tipo   = $_POST['tipo']        ?? 'usuario';
    $status = $_POST['status']      ?? 'ativo';
    $senha  = $_POST['senha']       ?? '';

    if ($id <= 0 || empty($nome) || empty($email)) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Dados inválidos.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'E-mail inválido.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    $tipo   = in_array($tipo,   ['admin', 'usuario']) ? $tipo   : 'usuario';
    $status = in_array($status, ['ativo', 'inativo']) ? $status : 'ativo';

    try {
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Este e-mail já está em uso por outro usuário.'];
            header('Location: /pages/usuarios.php');
            exit;
        }

        if (!empty($senha)) {
            if (strlen($senha) < 6) {
                $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'A nova senha deve ter pelo menos 6 caracteres.'];
                header('Location: /pages/usuarios.php');
                exit;
            }
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            try {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios SET nome=:nome, email=:email, senha=:senha, tipo=:tipo, status=:status WHERE id=:id"
                );
                $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash, ':tipo' => $tipo, ':status' => $status, ':id' => $id]);
            } catch (PDOException $e2) {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios SET nome=:nome, email=:email, senha=:senha, tipo=:tipo WHERE id=:id"
                );
                $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $hash, ':tipo' => $tipo, ':id' => $id]);
            }
        } else {
            try {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios SET nome=:nome, email=:email, tipo=:tipo, status=:status WHERE id=:id"
                );
                $stmt->execute([':nome' => $nome, ':email' => $email, ':tipo' => $tipo, ':status' => $status, ':id' => $id]);
            } catch (PDOException $e2) {
                $stmt = $conexao->prepare(
                    "UPDATE usuarios SET nome=:nome, email=:email, tipo=:tipo WHERE id=:id"
                );
                $stmt->execute([':nome' => $nome, ':email' => $email, ':tipo' => $tipo, ':id' => $id]);
            }
        }

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'msg' => "Usuário {$nome} atualizado com sucesso."];
    } catch (PDOException $e) {
        error_log("usuariosController editar: " . $e->getMessage());
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Erro ao atualizar usuário.'];
    }

    header('Location: /pages/usuarios.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0 || $id === (int)$_SESSION['user']['id']) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Não é possível excluir este usuário.'];
        header('Location: /pages/usuarios.php');
        exit;
    }

    try {
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['flash'] = ['tipo' => 'sucesso', 'msg' => 'Usuário excluído com sucesso.'];
    } catch (PDOException $e) {
        error_log("usuariosController excluir: " . $e->getMessage());
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Erro ao excluir usuário.'];
    }

    header('Location: /pages/usuarios.php');
    exit;
}

$usuario_logado = $_SESSION['user'];
$flash          = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$current_page   = 'usuarios';
$page_title     = 'Usuários';
$breadcrumb     = [['label' => 'Usuários']];

$busca      = trim($_GET['busca']   ?? '');
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 10;
$offset     = ($pagina - 1) * $por_pagina;
$usuarios   = [];
$total      = 0;
$total_paginas = 1;

try {
    if ($busca !== '') {
        $like = '%' . $busca . '%';
        $c = $conexao->prepare("SELECT COUNT(*) FROM usuarios WHERE nome LIKE :n OR email LIKE :e OR tipo LIKE :t");
        $c->execute([':n' => $like, ':e' => $like, ':t' => $like]);
        $total = (int) $c->fetchColumn();

        $s = $conexao->prepare("SELECT * FROM usuarios WHERE nome LIKE :n OR email LIKE :e OR tipo LIKE :t ORDER BY id DESC LIMIT :lim OFFSET :off");
        $s->bindValue(':n',   $like,       PDO::PARAM_STR);
        $s->bindValue(':e',   $like,       PDO::PARAM_STR);
        $s->bindValue(':t',   $like,       PDO::PARAM_STR);
        $s->bindValue(':lim', $por_pagina, PDO::PARAM_INT);
        $s->bindValue(':off', $offset,     PDO::PARAM_INT);
        $s->execute();
    } else {
        $total = (int) $conexao->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

        $s = $conexao->prepare("SELECT * FROM usuarios ORDER BY id DESC LIMIT :lim OFFSET :off");
        $s->bindValue(':lim', $por_pagina, PDO::PARAM_INT);
        $s->bindValue(':off', $offset,     PDO::PARAM_INT);
        $s->execute();
    }

    $usuarios      = $s->fetchAll(PDO::FETCH_ASSOC);
    $total_paginas = max(1, (int) ceil($total / $por_pagina));
} catch (PDOException $e) {
    error_log('usuariosController GET: ' . $e->getMessage());
}

$inicio = $total > 0 ? $offset + 1 : 0;
$fim    = min($offset + $por_pagina, $total);

// Funções auxiliares para a view
function avatarUrl(string $nome): string
{
    return 'https://ui-avatars.com/api/?name=' . urlencode($nome)
        . '&background=F97316&color=fff&bold=true&size=80';
}

function statusBadge(string $status): string
{
    return $status === 'ativo'
        ? '<span class="badge badge-green">Ativo</span>'
        : '<span class="badge badge-orange">Inativo</span>';
}

function tipoBadge(string $tipo): string
{
    $label = $tipo === 'admin' ? 'Administrador' : 'Usuário';
    $cls   = $tipo === 'admin' ? 'admin'         : 'usuario';
    return "<span class=\"badge-tipo {$cls}\">{$label}</span>";
}
