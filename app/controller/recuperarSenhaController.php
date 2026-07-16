<?php

session_start();

require_once __DIR__ . '/../../app/config/conexao.php';
require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/mailer.php';

$conexao = Connection::getConnection();
$acao    = $_POST['acao'] ?? '';

/* ── Solicitar recuperação: gera o token e leva à redefinição ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'solicitar') {

    $email = trim($_POST['email'] ?? '');

    $checagem = validarEmail($email);
    if (!$checagem['valido']) {
        $_SESSION['flash_recuperar'] = ['tipo' => 'erro', 'msg' => $checagem['mensagem']];
        header('Location: /auth/recuperar.php');
        exit;
    }

    // Sem SMTP configurado não há como entregar o link — avisa o administrador.
    if (!emailAtivo()) {
        $_SESSION['flash_recuperar'] = [
            'tipo' => 'erro',
            'msg'  => 'O envio de e-mail ainda não foi configurado. Preencha app/config/email.php.'
        ];
        header('Location: /auth/recuperar.php');
        exit;
    }

    // Mensagem neutra: não revela se o e-mail existe (evita enumeração de contas).
    $mensagemNeutra = [
        'tipo' => 'sucesso',
        'msg'  => 'Se existir uma conta com este e-mail, enviamos um link para redefinir a senha.'
    ];

    try {
        $stmt = $conexao->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', time() + 3600); // válido por 1 hora

            $conexao->prepare("INSERT INTO recuperacao_senha (usuario_id, token, expira_em) VALUES (?, ?, ?)")
                ->execute([$usuario['id'], $token, $expira]);

            // Monta o link absoluto de redefinição a partir do host da requisição.
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $link      = $protocolo . '://' . $host . '/auth/redefinir.php?token=' . urlencode($token);

            enviarEmail(
                $email,
                $usuario['nome'],
                'Recuperação de senha — Sprint Max',
                corpoEmailRecuperacao($usuario['nome'], $link)
            );

            registrarLog($conexao, 'recuperar_senha', "Solicitação de recuperação de senha de \"{$usuario['nome']}\"", (int) $usuario['id']);
        }

        $_SESSION['flash_recuperar'] = $mensagemNeutra;
        header('Location: /auth/recuperar.php');
        exit;
    } catch (PDOException $e) {
        error_log('recuperarSenha solicitar: ' . $e->getMessage());
        $_SESSION['flash_recuperar'] = ['tipo' => 'erro', 'msg' => 'Erro ao processar a solicitação. Tente novamente.'];
        header('Location: /auth/recuperar.php');
        exit;
    }
}

/* ── Redefinir: valida o token e grava a nova senha ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acao === 'redefinir') {

    $token     = trim($_POST['token'] ?? '');
    $senha     = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';
    $redirect  = '/auth/redefinir.php?token=' . urlencode($token);

    if ($senha !== $confirmar) {
        $_SESSION['flash_redefinir'] = ['tipo' => 'erro', 'msg' => 'As senhas não coincidem.'];
        header('Location: ' . $redirect);
        exit;
    }

    if (strlen($senha) < 6) {
        $_SESSION['flash_redefinir'] = ['tipo' => 'erro', 'msg' => 'A senha deve ter pelo menos 6 caracteres.'];
        header('Location: ' . $redirect);
        exit;
    }

    try {
        $stmt = $conexao->prepare("SELECT id, usuario_id, expira_em, usado FROM recuperacao_senha WHERE token = ?");
        $stmt->execute([$token]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registro || (int) $registro['usado'] === 1 || strtotime($registro['expira_em']) < time()) {
            $_SESSION['flash'] = ['tipo' => 'erro', 'msg' => 'Link de recuperação inválido ou expirado.'];
            header('Location: /auth/login.php');
            exit;
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")->execute([$hash, $registro['usuario_id']]);
        $conexao->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?")->execute([$registro['id']]);

        registrarLog($conexao, 'redefinir_senha', 'Senha redefinida via recuperação', (int) $registro['usuario_id']);

        $_SESSION['flash'] = ['tipo' => 'sucesso', 'msg' => 'Senha redefinida com sucesso! Faça login.'];
        header('Location: /auth/login.php');
        exit;
    } catch (PDOException $e) {
        error_log('recuperarSenha redefinir: ' . $e->getMessage());
        $_SESSION['flash_redefinir'] = ['tipo' => 'erro', 'msg' => 'Erro ao redefinir a senha. Tente novamente.'];
        header('Location: ' . $redirect);
        exit;
    }
}

header('Location: /auth/login.php');
exit;
