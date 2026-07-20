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

$id_usuario = (int) $_SESSION['user']['id'];
$redirect = filter_var($_POST['redirect'] ?? '/pages/perfil.php', FILTER_SANITIZE_URL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['acao'] ?? '') !== 'atualizar') {
    header('Location: ' . $redirect);
    exit;
}

$nome = trim($_POST['nome'] ?? '');

if (empty($nome)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'O nome não pode estar em branco.'];
    header('Location: ' . $redirect);
    exit;
}

if (mb_strlen($nome) > 120) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nome muito longo (máximo 120 caracteres).'];
    header('Location: ' . $redirect);
    exit;
}

// Upload de foto de perfil (opcional, requer migração do banco)
$fotoPerfil = null;
if (!empty($_FILES['foto_perfil']['name'])) {
    $resultado = uploadImagem($_FILES['foto_perfil'], 'perfil');
    if ($resultado === false) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Foto inválida. Use JPG, PNG ou WEBP (máx. 5 MB).'];
        header('Location: ' . $redirect);
        exit;
    }
    // Tenta remover foto antiga (coluna pode não existir antes da migração)
    try {
        $stmtOld = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        $stmtOld->execute([$id_usuario]);
        excluirImagem($stmtOld->fetchColumn() ?: null);
    } catch (PDOException $e) { /* coluna não existe ainda */
    }
    $fotoPerfil = $resultado;
}

// Mudança de senha (opcional)
$novaSenha      = $_POST['nova_senha']      ?? '';
$senhaAtual     = $_POST['senha_atual']     ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if (!empty($novaSenha)) {
    $stmtSenha = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmtSenha->execute([$id_usuario]);
    $senha_banco = $stmtSenha->fetchColumn();

    if (!password_verify($senhaAtual, $senha_banco)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Senha atual incorreta.'];
        header('Location: ' . $redirect);
        exit;
    }

    if (strlen($novaSenha) < 6) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'];
        header('Location: ' . $redirect);
        exit;
    }

    if ($novaSenha !== $confirmarSenha) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'As novas senhas não coincidem.'];
        header('Location: ' . $redirect);
        exit;
    }
}

try {
    if (!empty($novaSenha) && $fotoPerfil !== null) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        // Tenta com foto_perfil; se coluna não existir, salva sem ela
        try {
            $pdo->prepare("UPDATE usuarios SET nome=?, senha=?, foto_perfil=? WHERE id=?")
                ->execute([$nome, $hash, $fotoPerfil, $id_usuario]);
        } catch (PDOException $e2) {
            $pdo->prepare("UPDATE usuarios SET nome=?, senha=? WHERE id=?")
                ->execute([$nome, $hash, $id_usuario]);
        }
    } elseif (!empty($novaSenha)) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET nome=?, senha=? WHERE id=?")->execute([$nome, $hash, $id_usuario]);
    } elseif ($fotoPerfil !== null) {
        try {
            $pdo->prepare("UPDATE usuarios SET nome=?, foto_perfil=? WHERE id=?")->execute([$nome, $fotoPerfil, $id_usuario]);
        } catch (PDOException $e2) {
            $pdo->prepare("UPDATE usuarios SET nome=? WHERE id=?")->execute([$nome, $id_usuario]);
        }
    } else {
        $pdo->prepare("UPDATE usuarios SET nome=? WHERE id=?")->execute([$nome, $id_usuario]);
    }

    // Atualiza sessão
    $_SESSION['user']['nome'] = $nome;
    if ($fotoPerfil !== null) {
        $_SESSION['user']['foto_perfil'] = $fotoPerfil;
    }

    registrarLog($pdo, 'edicao_usuario', "Perfil atualizado", $id_usuario);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Perfil atualizado com sucesso!'];
} catch (PDOException $e) {
    error_log('perfilController: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erro ao atualizar perfil.'];
}

header('Location: ' . $redirect);
exit;
