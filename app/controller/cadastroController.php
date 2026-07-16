<?php

session_start();

require_once __DIR__ . "/../../app/config/conexao.php";
require_once __DIR__ . "/../../app/config/helpers.php";

$conexao = Connection::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/cadastro.php");
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha']    ?? '';

if (empty($nome) || empty($email) || empty($senha)) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Preencha todos os campos.'];
    header("Location: /auth/cadastro.php");
    exit;
}

$checagemEmail = validarEmail($email);
if (!$checagemEmail['valido']) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => $checagemEmail['mensagem']];
    header("Location: /auth/cadastro.php");
    exit;
}

if ($senha !== $confirmarSenha) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'As senhas não coincidem.'];
    header("Location: /auth/cadastro.php");
    exit;
}

if (strlen($senha) < 6) {
    $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'A senha deve ter pelo menos 6 caracteres.'];
    header("Location: /auth/cadastro.php");
    exit;
}

try {
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Este e-mail já está cadastrado.'];
        header("Location: /auth/cadastro.php");
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conexao->prepare(
        "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)"
    );

    $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, ':tipo' => 'usuario']);

    registrarLog($conexao, 'cadastro_usuario', "Novo usuário \"{$nome}\" cadastrado", null);

    $_SESSION['flash'] = ['tipo' => 'sucesso', 'msg' => 'Conta criada com sucesso! Faça seu login.'];
    header("Location: /auth/login.php");
    exit;
}

 catch (PDOException $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Erro interno. Tente novamente.'];
    header("Location: /auth/cadastro.php");
    exit;
}
