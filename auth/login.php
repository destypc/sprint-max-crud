<?php
session_start();
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Login</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.png" type="image/x-icon">

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body>

    <div class="login-conteiner">

        <!-- ====== LADO ESQUERDO — Formulário ====== -->
        <div class="login-esquerda">

            <!-- Bolhas animadas de fundo -->
            <div class="bolha bolha--1"></div>
            <div class="bolha bolha--2"></div>
            <div class="bolha bolha--3"></div>
            <div class="bolha bolha--4"></div>

            <div class="login-formulario-conteiner">

                <!-- Logo -->
                <div class="marca">
                    <span>Sprint</span> <span class="destaque">Max</span>
                </div>

                <!-- Título -->
                <h1 class="login-titulo">Bem-vindo <span class="destaque">de volta</span></h1>
                <p class="login-subtitulo">Faça login para acessar seu painel.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <form id="loginForm" novalidate method="POST" action="/app/controller/loginController.php">

                    <!-- Email -->
                    <div class="grupo-formulario">
                        <label for="loginEmail">E-mail</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-envelope icone-entrada"></i>
                            <input type="email" id="loginEmail" name="email" placeholder="Digite seu e-mail" autocomplete="email">
                        </div>
                    </div>

                    <!-- Senha -->
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

                    <!-- Botão Entrar -->
                    <button type="submit" class="botao-sprint">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Entrar
                    </button>
                </form>

                <!-- Divider -->
                <div class="divisor">
                    <span>Ainda não tem uma conta?</span>
                </div>

                <!-- Botão Criar Conta -->
                <a href="/auth/cadastro.php" style="text-decoration: none;">
                    <button type="button" class="botao-contorno-sprint">
                        <i class="fa-solid fa-user-plus"></i>
                        Criar conta
                    </button>
                </a>

                <!-- Card Admin -->
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

        <!-- ====== LADO DIREITO — Visual ====== -->
        <div class="login-direita">

            <picture>
                <!-- Monitor grande / TV (1600px+) -->
                <source media="(min-width: 1600px)" srcset="/assets/img/sprint-max3.png">
                <!-- Monitor médio (1280px – 1599px) -->
                <source media="(min-width: 1280px)" srcset="/assets/img/sprint-max2.png">
                <!-- Monitor pequeno (992px – 1279px) -->
                <img src="/assets/img/sprint-max1.png" alt="Sprint Max">
            </picture>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JS Personalizado -->
    <script src="/assets/js/login.js"></script>

</body>

</html>