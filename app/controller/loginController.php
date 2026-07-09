<?php

session_start();

require_once __DIR__ . "/../../app/config/conexao.php";
require_once __DIR__ . "/../../app/config/helpers.php";

$conexao = Connection::getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha']       ?? '';

if (empty($email) || empty($senha)) {
    header("Location: /auth/login.php?erro=Preencha todos os campos.");
    exit;
}

$stmt = $conexao->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = :email");
$stmt->execute([':email' => $email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    header("Location: /auth/login.php?erro=E-mail ou senha incorretos.");
    exit;
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id'    => $usuario['id'],
    'nome'  => $usuario['nome'],
    'email' => $usuario['email'],
    'tipo'  => $usuario['tipo']
];

registrarLog($conexao, 'login', "Login realizado por \"{$usuario['nome']}\"", $usuario['id']);

if ($usuario['tipo'] === 'admin') {
    header("Location: /pages/dashboard.php");
} else {
    header("Location: /pages/vendas.php");
}
exit;
