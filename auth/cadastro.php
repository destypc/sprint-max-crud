<?php
session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprint Max — Cadastro</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/cadastro.css">
</head>

<body>

    <div class="login-wrapper">

        <!-- ====== LADO ESQUERDO — Formulário ====== -->
        <div class="login-left">

            <!-- Bolhas animadas de fundo -->
            <div class="bubble bubble--1"></div>
            <div class="bubble bubble--2"></div>
            <div class="bubble bubble--3"></div>
            <div class="bubble bubble--4"></div>

            <div class="login-form-container">

                <!-- Logo -->
                <div class="brand-full">
                    <img src="/assets/img/logo-name.png" alt="Sprint Max">
                </div>

                <!-- Título -->
                <h1 class="login-title">Criar <span class="accent">conta</span></h1>
                <p class="login-subtitle">Preencha os dados para se cadastrar.</p>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?> py-2" role="alert">
                        <i class="fa-solid fa-<?= $flash['tipo'] === 'erro' ? 'circle-exclamation' : 'circle-check' ?> me-2"></i>
                        <?= htmlspecialchars($flash['msg']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <form id="cadastroForm" novalidate method="POST" action="/app/controller/cadastroController.php">

                    <!-- Nome -->
                    <div class="form-group">
                        <label for="cadNome">Nome completo</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input type="text" id="cadNome" name="nome" placeholder="Seu nome completo" autocomplete="name">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="cadEmail">E-mail</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input type="email" id="cadEmail" name="email" placeholder="Seu melhor e-mail" autocomplete="email">
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="form-group">
                        <label for="cadSenha">Senha</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="cadSenha" name="senha" placeholder="Crie uma senha" autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <!-- Indicador de força de senha -->
                        <div id="cadSenhaStrength" class="strength-wrapper">
                            <div class="password-strength">
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                            </div>
                            <p class="strength-label"></p>
                        </div>
                    </div>

                    <!-- Confirmar Senha -->
                    <div class="form-group">
                        <label for="cadConfirmar">Confirmar senha</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="cadConfirmar"
                                name="confirmar_senha"
                                placeholder="Repita a senha"
                                autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botão Cadastrar -->
                    <button type="submit" class="btn-sprint">
                        <i class="fa-solid fa-user-check"></i>
                        Cadastrar
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>Já possui uma conta?</span>
                </div>

                <!-- Botão Voltar ao Login -->
                <a href="/auth/login.php" class="btn-outline-sprint">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Fazer login
                </a>

            </div>
        </div>

        <!-- ====== LADO DIREITO — Visual ====== -->
        <div class="login-right login-right--dark">

            <!-- Imagem hero (atleta) -->
            <div class="right-bg">
                <img src="/assets/img/sprint-max2.png" alt="">
            </div>

            <!-- Overlay gradiente para legibilidade -->
            <div class="right-overlay"></div>

            <!-- Decorações geométricas sutis -->
            <div class="geo-circle geo-circle--1"></div>
            <div class="speed-line speed-line--1"></div>
            <div class="speed-line speed-line--3"></div>

            <!-- Conteúdo principal -->
            <div class="right-content">

                <div></div>

                <!-- Tagline central -->
                <div class="right-tagline">
                    <p class="tagline-eyebrow">Sistema de gestão</p>
                    <h2>Desempenho.<br>Velocidade.<br><span>Resultado.</span></h2>
                </div>

                <!-- Feature pills no rodapé -->
                <div class="right-pills">
                    <span class="pill">
                        <i class="fa-solid fa-chart-line"></i>
                        Vendas em tempo real
                    </span>
                    <span class="pill">
                        <i class="fa-solid fa-box"></i>
                        Controle de estoque
                    </span>
                    <span class="pill">
                        <i class="fa-solid fa-users"></i>
                        Gestão de equipe
                    </span>
                </div>

            </div>

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