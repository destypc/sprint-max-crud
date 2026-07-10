<?php

function registrarLog(PDO $pdo, string $acao, string $descricao, ?int $usuario_id = null): void
{
    try {
        $pdo->prepare("INSERT INTO logs (usuario_id, acao, descricao, data) VALUES (?, ?, ?, NOW())")
            ->execute([$usuario_id, $acao, $descricao]);
    } catch (PDOException $e) {
        // falha silenciosa — mantém o sistema funcional mesmo sem a tabela logs
    }
}

function resolverStatusProduto(int $qtd): string
{
    if ($qtd === 0) return 'sem_estoque';
    if ($qtd <= 5)  return 'baixo_estoque';
    return 'ativo';
}

/**
 * Faz upload de uma imagem para a pasta /uploads/.
 * Retorna o caminho web "/uploads/arquivo.ext" ou false em caso de erro.
 */
function uploadImagem(array $arquivo, string $prefixo = 'produto'): string|false
{
    if ($arquivo['error'] !== UPLOAD_ERR_OK) return false;
    if ($arquivo['size'] > 5 * 1024 * 1024) return false;

    $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) return false;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) return false;

    $nome    = $prefixo . '_' . uniqid() . '.' . $ext;
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
