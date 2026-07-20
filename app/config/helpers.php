<?php

/* ── Proteção CSRF ───────────────────────────────────────────
 * Gera um token por sessão e o valida nas requisições POST que
 * alteram estado. Formulários incluem o token via csrfCampo();
 * requisições AJAX o lêem da <meta name="csrf-token"> (head.php).
 */

function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo hidden com o token, para colar dentro dos <form>. */
function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES) . '">';
}

/** Confere o token enviado (POST header X-CSRF-Token ou campo csrf_token). */
function verificarCsrf(): bool
{
    $enviado = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($enviado)
        && $enviado !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $enviado);
}

/**
 * Bloqueia a requisição se for POST sem token CSRF válido.
 * Chame no topo dos controllers que processam formulários.
 * Em vez de uma página 403 seca, redireciona de volta com um aviso amigável
 * (cobre o caso de sessão expirada / página desatualizada por cache).
 */
function exigirCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verificarCsrf()) {
        $_SESSION['flash'] = [
            'type'    => 'error',
            'message' => 'Sua sessão expirou ou a página estava desatualizada. Recarregue a página (Ctrl+F5) e tente novamente.',
        ];
        // Volta para a página de origem, usando só o caminho interno do
        // referer (nunca a URL completa) para evitar open redirect.
        $destino = '/pages/home.php';
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if (is_string($ref) && $ref !== '') {
            $caminho = parse_url($ref, PHP_URL_PATH);
            if (is_string($caminho) && isset($caminho[0]) && $caminho[0] === '/') {
                $query   = parse_url($ref, PHP_URL_QUERY);
                $destino = $caminho . ($query ? '?' . $query : '');
            }
        }
        header('Location: ' . $destino);
        exit;
    }
}

/**
 * E-mail do administrador principal ("super admin"): o único que pode
 * excluir outras contas de administrador.
 */
const SUPER_ADMIN_EMAIL = 'admin@gmail.com';

/** Indica se o usuário (por padrão o logado) é o super admin. */
function ehSuperAdmin(?array $usuario = null): bool
{
    $usuario = $usuario ?? ($_SESSION['user'] ?? null);
    return is_array($usuario)
        && strcasecmp($usuario['email'] ?? '', SUPER_ADMIN_EMAIL) === 0;
}

/**
 * Revalida a sessão contra o banco: se o usuário logado foi EXCLUÍDO ou
 * DESATIVADO pelo admin, encerra a sessão na hora e manda para o login.
 * Deve ser chamada no topo de cada página/controller autenticado, antes de
 * qualquer saída (HTML). Não faz nada se ninguém estiver logado.
 */
function garantirSessaoValida(PDO $pdo): void
{
    if (empty($_SESSION['user']['id'])) {
        return; // Não logado — o controle de login de cada página trata disso.
    }

    $id = (int) $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("SELECT status FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn(); // false se não existir; 'ativo'/'inativo' caso exista
    } catch (PDOException $e) {
        // Banco sem a coluna status: valida apenas a existência do usuário.
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $status = ($stmt->fetchColumn() === false) ? false : 'ativo';
        } catch (PDOException $e2) {
            return; // Falha de conexão: não desloga por precaução.
        }
    }

    // Conta excluída (sumiu do banco) ou desativada → encerra a sessão.
    if ($status === false || $status === 'inativo') {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $motivo = ($status === 'inativo')
            ? 'Sua conta foi desativada. Contate o administrador.'
            : 'Sua conta foi encerrada.';
        header('Location: /auth/login.php?erro=' . urlencode($motivo));
        exit;
    }
}

function registrarLog(PDO $conexao, string $acao, string $descricao, ?int $usuario_id = null): void
{
    try {
        $conexao->prepare("INSERT INTO logs (usuario_id, acao, descricao, data) VALUES (?, ?, ?, NOW())")->execute([$usuario_id, $acao, $descricao]);
    } catch (PDOException $erro) {
    }
}

/**
 * Valida um e-mail em duas camadas: formato (FILTER_VALIDATE_EMAIL) e
 * existência do domínio (registro MX, com fallback para A/AAAA).
 * Não envia e-mail — apenas verifica se o endereço pode receber mensagens.
 * Retorna ['valido' => bool, 'mensagem' => string].
 */
function validarEmail(string $email): array
{
    $email = trim($email);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valido' => false, 'mensagem' => 'Digite um e-mail válido.'];
    }

    $dominio = substr(strrchr($email, '@'), 1);

    // Só verifica DNS quando a função existe e há resolução de nomes disponível.
    // Aceita MX ou, como fallback, um registro A/AAAA (domínios que recebem e-mail sem MX dedicado).
    if (function_exists('checkdnsrr') && $dominio !== '') {
        $temMx = checkdnsrr($dominio, 'MX');
        $temA  = checkdnsrr($dominio, 'A') || checkdnsrr($dominio, 'AAAA');
        if (!$temMx && !$temA) {
            return ['valido' => false, 'mensagem' => 'O domínio do e-mail não parece existir.'];
        }
    }

    return ['valido' => true, 'mensagem' => ''];
}

/**
 * Faz upload de uma imagem para a pasta /uploads/.
 * Retorna o caminho web "/uploads/arquivo.ext" ou false em caso de erro.
 */
function uploadImagem(array $arquivo, string $prefixo = 'produto'): string|false
{
    if ($arquivo['error'] !== UPLOAD_ERR_OK) return false;
    if ($arquivo['size'] > 5 * 1024 * 1024) return false;

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'webp'], true)) return false;

    $identificador_arquivo = finfo_open(FILEINFO_MIME_TYPE);
    $tipo_mime = finfo_file($identificador_arquivo, $arquivo['tmp_name']);
    finfo_close($identificador_arquivo);
    if (!in_array($tipo_mime, ['image/jpeg', 'image/png', 'image/webp'], true)) return false;

    // ── Validação de dimensões ──────────────────────────────────
    $dimensoes = @getimagesize($arquivo['tmp_name']);
    if ($dimensoes === false) return false;

    [$largura, $altura] = $dimensoes;
    $largura_minima = 300;
    $altura_minima  = 300;
    $largura_maxima = 4000;
    $altura_maxima  = 4000;

    if ($largura < $largura_minima || $altura < $altura_minima) return false;
    if ($largura > $largura_maxima || $altura > $altura_maxima) return false;
    // ───────────────────────────────────────────────────────────

    $nome = $prefixo . '_' . uniqid() . '.' . $extensao;
    $destino = __DIR__ . '/../../uploads/' . $nome;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) return false;

    return '/uploads/' . $nome;
}


//  Remove um arquivo de imagem do disco (falha silenciosamente).

function excluirImagem(?string $caminho): void{
    if (empty($caminho)) return;
    $fisico = __DIR__ . '/../../' . ltrim($caminho, '/');
    if (file_exists($fisico)) @unlink($fisico);
}
