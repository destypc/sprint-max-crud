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

    <!-- Favicon -->
    <link rel="shortcut icon" href="/assets/img/favicon.png" type="image/png">

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/cadastro.css">
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


                <!-- Título -->
                <h1 class="login-titulo">Criar <span class="destaque">conta</span></h1>
                <p class="login-subtitulo">Preencha os dados para se cadastrar.</p>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?> py-2" role="alert">
                        <i class="fa-solid fa-<?= $flash['tipo'] === 'erro' ? 'circle-exclamation' : 'circle-check' ?> me-2"></i>
                        <?= htmlspecialchars($flash['msg']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <form id="cadastroForm" novalidate method="POST" action="/app/controller/cadastroController.php">

                    <!-- Nome -->
                    <div class="grupo-formulario">
                        <label for="cadNome">Nome completo</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-user icone-entrada"></i>
                            <input type="text" id="cadNome" name="nome" placeholder="Seu nome completo" autocomplete="name">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="grupo-formulario">
                        <label for="cadEmail">E-mail</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-envelope icone-entrada"></i>
                            <input type="email" id="cadEmail" name="email" placeholder="Seu melhor e-mail" autocomplete="email">
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="grupo-formulario">
                        <label for="cadSenha">Senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="cadSenha" name="senha" placeholder="Crie uma senha" autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <!-- Indicador de força de senha -->
                        <div id="cadSenhaStrength" class="forca-conteiner">
                            <div class="forca-senha">
                                <div class="forca-barra"></div>
                                <div class="forca-barra"></div>
                                <div class="forca-barra"></div>
                            </div>
                            <p class="forca-rotulo"></p>
                        </div>
                    </div>

                    <!-- Confirmar Senha -->
                    <div class="grupo-formulario">
                        <label for="cadConfirmar">Confirmar senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input
                                type="password"
                                id="cadConfirmar"
                                name="confirmar_senha"
                                placeholder="Repita a senha"
                                autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botão Cadastrar -->
                    <button type="submit" class="botao-sprint">
                        <i class="fa-solid fa-user-check"></i>
                        Cadastrar
                    </button>
                </form>

                <!-- Divider -->
                <div class="divisor">
                    <span>Já possui uma conta?</span>
                </div>

                <!-- Botão Voltar ao Login -->
                <a href="/auth/login.php" class="botao-contorno-sprint">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Fazer login
                </a>

            </div>
        </div>

        <!-- ====== LADO DIREITO — Visual ====== -->
        <div class="login-direita login-direita--escura">

            <!-- Imagem hero (atleta) -->
            <div class="direita-fundo">
                <img src="/assets/img/sprint-max2.png" alt="">
            </div>

            <!-- Overlay gradiente para legibilidade -->
            <div class="direita-sobreposicao"></div>

            <!-- Decorações geométricas sutis -->
            <div class="circulo-geometrico circulo-geometrico--1"></div>
            <div class="linha-velocidade linha-velocidade--1"></div>
            <div class="linha-velocidade linha-velocidade--3"></div>

            <!-- Conteúdo principal -->
            <div class="direita-conteudo">

                <div></div>

                <!-- Tagline central -->
                <div class="direita-slogan">
                    <p class="slogan-sobrancelha">Sistema de gestão</p>
                    <h2>Desempenho.<br>Velocidade.<br><span>Resultado.</span></h2>
                </div>

                <!-- Feature items no rodapé -->
                <div class="direita-itens">
                    <span class="item">
                        <i class="fa-solid fa-chart-line"></i>
                        Vendas em tempo real
                    </span>
                    <span class="item">
                        <i class="fa-solid fa-box"></i>
                        Controle de estoque
                    </span>
                    <span class="item">
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
