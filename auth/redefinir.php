<?php
session_start();
$flash = $_SESSION['flash_redefinir'] ?? null;
unset($_SESSION['flash_redefinir']);
$token = trim($_GET['token'] ?? '');

// Sem token não há o que redefinir.
if ($token === '') {
    header('Location: /auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Redefinir senha</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body>

    <div class="login-conteiner">
        <div class="login-esquerda">

            <div class="bolha bolha--1"></div>
            <div class="bolha bolha--2"></div>
            <div class="bolha bolha--3"></div>
            <div class="bolha bolha--4"></div>

            <div class="login-formulario-conteiner">

                <div class="marca">
                    <span>Sprint</span> <span class="destaque">Max</span>
                </div>

                <h1 class="login-titulo">Nova <span class="destaque">senha</span></h1>
                <p class="login-subtitulo">Defina uma nova senha para sua conta.</p>

                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : ($flash['tipo'] === 'info' ? 'info' : 'success') ?> py-2" role="alert">
                    <i class="fa-solid fa-<?= $flash['tipo'] === 'erro' ? 'circle-exclamation' : 'circle-info' ?> me-2"></i>
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
                <?php endif; ?>

                <form id="redefinirForm" novalidate method="POST" action="/app/controller/recuperarSenhaController.php">
                    <input type="hidden" name="acao" value="redefinir">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="grupo-formulario">
                        <label for="novaSenha">Nova senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="novaSenha" name="senha" placeholder="Crie uma nova senha" autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label for="confirmarSenha">Confirmar senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="confirmarSenha" name="confirmar_senha" placeholder="Repita a nova senha" autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="botao-sprint">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Redefinir senha
                    </button>
                </form>

                <div class="divisor">
                    <span>Mudou de ideia?</span>
                </div>

                <a href="/auth/login.php" class="botao-contorno-sprint">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Voltar para o login
                </a>

            </div>
        </div>

        <div class="login-direita">
            <picture>
                <source media="(min-width: 1600px)" srcset="/assets/img/sprint-max3.png">
                <source media="(min-width: 1280px)" srcset="/assets/img/sprint-max2.png">
                <img src="/assets/img/sprint-max1.png" alt="Sprint Max">
            </picture>
        </div>
    </div>

    <script src="/assets/js/auth-comum.js"></script>

</body>

</html>
