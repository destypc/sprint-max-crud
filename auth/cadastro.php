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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/assets/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="/assets/css/cadastro.css">
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

                <h1 class="login-titulo">Criar <span class="destaque">conta</span></h1>
                <p class="login-subtitulo">Preencha os dados para se cadastrar.</p>

                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['tipo'] === 'erro' ? 'danger' : 'success' ?> py-2" role="alert">
                    <i class="fa-solid fa-<?= $flash['tipo'] === 'erro' ? 'circle-exclamation' : 'circle-check' ?> me-2"></i>
                    <?= htmlspecialchars($flash['msg']) ?>
                </div>
                <?php endif; ?>

                <form id="cadastroForm" novalidate method="POST" action="/app/controller/cadastroController.php">

                    <div class="grupo-formulario">
                        <label for="cadNome">Nome completo</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-user icone-entrada"></i>
                            <input type="text" id="cadNome" name="nome" placeholder="Seu nome completo" autocomplete="name">
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label for="cadEmail">E-mail</label>
                        <div class="entrada-conteiner">
                            <i class="fa-regular fa-envelope icone-entrada"></i>
                            <input type="email" id="cadEmail" name="email" placeholder="Seu melhor e-mail" autocomplete="email">
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label for="cadSenha">Senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="cadSenha" name="senha" placeholder="Crie uma senha" autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div id="cadSenhaStrength" class="forca-conteiner">
                            <div class="forca-senha">
                                <div class="forca-barra"></div>
                                <div class="forca-barra"></div>
                                <div class="forca-barra"></div>
                            </div>
                            <p class="forca-rotulo"></p>
                        </div>
                    </div>

                    <div class="grupo-formulario">
                        <label for="cadConfirmar">Confirmar senha</label>
                        <div class="entrada-conteiner">
                            <i class="fa-solid fa-lock icone-entrada"></i>
                            <input type="password" id="cadConfirmar" name="confirmar_senha" placeholder="Repita a senha" autocomplete="new-password">
                            <button type="button" class="alternar-senha" aria-label="Mostrar senha">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="botao-sprint">
                        <i class="fa-solid fa-user-check"></i>
                        Cadastrar
                    </button>
                </form>

                <div class="divisor">
                    <span>Já possui uma conta?</span>
                </div>

                <a href="/auth/login.php" class="botao-contorno-sprint">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Fazer login
                </a>

            </div>
        </div>

        <!-- Lado direito — visual -->
        <div class="login-direita login-direita--escura">

            <div class="direita-fundo">
                <img src="/assets/img/sprint-max2.png" alt="">
            </div>
            <div class="direita-sobreposicao"></div>

            <div class="circulo-geometrico circulo-geometrico--1"></div>
            <div class="linha-velocidade linha-velocidade--1"></div>
            <div class="linha-velocidade linha-velocidade--3"></div>

            <div class="direita-conteudo">

                <div></div>

                <div class="direita-slogan">
                    <p class="slogan-sobrancelha">Sistema de gestão</p>
                    <h2>Desempenho..<br>Velocidade.<br><span>Resultado.</span></h2>
                </div>

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
                        Gestão da equipe
                    </span>
                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/auth-comum.js"></script>
    <script src="/assets/js/cadastro.js"></script>

</body>

</html>
