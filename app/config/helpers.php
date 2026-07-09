<?php

/**
 * helpers.php — Funções utilitárias compartilhadas entre controllers.
 * Sprint Max
 */

/**
 * Grava uma entrada na tabela de logs do sistema.
 * Falha silenciosamente se a tabela ainda não existir.
 */
function registrarLog(PDO $pdo, string $acao, string $descricao, ?int $usuario_id = null): void
{
    try {
        $pdo->prepare("INSERT INTO logs (usuario_id, acao, descricao, data) VALUES (?, ?, ?, NOW())")
            ->execute([$usuario_id, $acao, $descricao]);
    } catch (PDOException $e) {
        // Silently fail — mantém o sistema funcional mesmo sem a tabela logs
    }
}
