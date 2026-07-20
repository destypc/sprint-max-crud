<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

// Criação de tags é uma ação administrativa.
if (empty($_SESSION['user']) || $_SESSION['user']['tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Acesso negado.']);
    exit;
}

require_once __DIR__ . '/../../app/config/conexao.php';
require_once __DIR__ . '/../../app/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verificarCsrf()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token de segurança inválido. Recarregue a página.']);
    exit;
}

$conexao = Connection::getConnection();

garantirSessaoValida($conexao);

$acao    = $_POST['acao'] ?? '';

if ($acao === 'criar') {

    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '' || mb_strlen($nome) > 50) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'erro' => 'Informe um nome de tag válido (até 50 caracteres).']);
        exit;
    }

    if (!preg_match("#^[\pL\pN\s\\-&/]+$#u", $nome)) { // RegEx
        http_response_code(422);
        echo json_encode(['ok' => false, 'erro' => 'Nome de tag inválido: use apenas letras, números e espaços.']);
        exit;
    }

    try {
        $conexao->prepare("INSERT INTO tags (nome) VALUES (?)")->execute([$nome]);
        echo json_encode(['ok' => true, 'tag' => ['id' => (int) $conexao->lastInsertId(), 'nome' => $nome]]);
    } catch (PDOException $e) {
        // Nome duplicado (UNIQUE) — devolve a tag já existente para reuso.
        try {
            $stmt = $conexao->prepare("SELECT id, nome FROM tags WHERE nome = ?");
            $stmt->execute([$nome]);
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tag) {
                echo json_encode(['ok' => true, 'existente' => true, 'tag' => ['id' => (int) $tag['id'], 'nome' => $tag['nome']]]);
                exit;
            }
        } catch (PDOException $e2) {
        }
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Não foi possível criar a tag.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'erro' => 'Ação inválida.']);
