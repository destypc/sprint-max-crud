<?php

function registrarLog(PDO $conexao, string $acao, string $descricao, ?int $usuario_id = null): void
{
    try {
        $conexao->prepare("INSERT INTO logs (usuario_id, acao, descricao, data) VALUES (?, ?, ?, NOW())")
            ->execute([$usuario_id, $acao, $descricao]);
    } catch (PDOException $erro) {
        // falha silenciosa — mantém o sistema funcional mesmo sem a tabela logs
    }
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

    $nome    = $prefixo . '_' . uniqid() . '.' . $extensao;
    $destino = __DIR__ . '/../../uploads/' . $nome;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) return false;

    return '/uploads/' . $nome;
}

/**
 * Remove um arquivo de imagem do disco (falha silenciosamente).
 */
function excluirImagem(?string $caminho): void
{
    if (empty($caminho)) return;
    $fisico = __DIR__ . '/../../' . ltrim($caminho, '/');
    if (file_exists($fisico)) @unlink($fisico);
}
