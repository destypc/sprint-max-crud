<?php
session_start();
$erro  = $_GET['erro'] ?? '';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body>

    <div class="login-conteiner">

        <!-- Lado esquerdo — formulário -->
        <div class="login-esquerda">

            <div class="bolha bolha--1"></div>
            <div class="bolha bolha--2"></div>
            <div class="bolha bolha--3"></div>
            <div class="bolha bolha--4"></div>

            <div class="login-formulario-conteiner">

                <div class="marca">
                    <span>Sprint</span> <span class="destaque">Max</span>
                </div>

                <h1 class="login-titulo">Bem-vindo <span class="destaque">de volta</span></h1>
                <p class="login-subtitulo">Faça login para acessar seu painel.</p>

                <?php if ($erro): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($erro) ?>
                </div>
                <?php endif; ?>

                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?> py-2" role="alert">
                    <i class="fa-solid fa-<?= $flash['tipo'] === 'erro' ? 'circle-exclamation' : 'circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
                <?php endif; ?>

                <form id="loginForm" novalidate method="POST" action="/app/controller/loginController.php">

                    <div class="grupo-formulario">
                        <label for="loginEmail">E-mail</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-envelope icone-entrada"></i>
                            <input type="email" id="loginEmail" name="email" placeholder="Digite seu e-mail" autocomplete="email">
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label for="loginSenha">Senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="loginSenha" name="senha" placeholder="Digite sua senha" autocomplete="current-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="esqueci-senha">
                        <a href="/auth/recuperar.php">Esqueci minha senha</a>
                    </div>

                    <button type="submit" class="botao-sprint">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Entrar
                    </button>
                </form>

                <div class="divisor">
                    <span>Ainda não tem uma conta?</span>
                </div>

                <a href="/auth/cadastro.php" style="text-decoration: none;">
                    <button type="button" class="botao-contorno-sprint">
                        <i class="fa-solid fa-user-plus"></i>
                        Criar conta
                    </button>
                </a>

                <!-- Credenciais de admin para teste -->
                <div class="cartao-admin">
                    <div class="icone-escudo">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="informacoes-admin">
                        <h6>Administrador para testes</h6>
                        <p>
                            <span>E-mail:</span> admin@gmail.com<br>
                            <span>Senha:</span> 123456
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Lado direito — visual responsivo -->
        <div class="login-direita">
            <picture>
                <source media="(min-width: 1600px)" srcset="/assets/img/sprint-max3.png">
                <source media="(min-width: 1280px)" srcset="/assets/img/sprint-max2.png">
                <img src="/assets/img/sprint-max1.png" alt="Sprint Max">
            </picture>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/auth-comum.js"></script>
    <script src="/assets/js/login.js"></script>

</body>

</html>
