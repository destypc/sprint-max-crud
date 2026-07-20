<?php
/**
 * SCRIPT TEMPORÁRIO DE MIGRAÇÃO — rode UMA vez e depois remova.
 *
 * Executa o banco-migration.sql de dentro do app (que conecta ao MySQL pela
 * rede interna da Railway), contornando o proxy público bloqueado.
 *
 * Uso:  https://SEU-DOMINIO/migrate.php?key=sm_setup_2026_kR9xQ
 *
 * Protegido por uma chave simples. Após concluir, este arquivo deve ser
 * excluído do repositório (será removido no próximo commit).
 */

header('Content-Type: text/plain; charset=utf-8');

$CHAVE = 'sm_setup_2026_kR9xQ';

if (($_GET['key'] ?? '') !== $CHAVE) {
    http_response_code(403);
    exit("Acesso negado. Informe ?key=... correto.\n");
}

require_once __DIR__ . '/app/config/conexao.php';

echo "== Migração Sprint Max ==\n";

try {
    $pdo = Connection::getConnection();
    $host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
    $db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'crud-sistema';
    echo "Conectado em host={$host} banco={$db}\n\n";
} catch (Throwable $e) {
    http_response_code(500);
    exit("FALHA ao conectar no banco: " . $e->getMessage()
        . "\n\nVerifique se as variáveis MYSQLHOST/MYSQLPORT/MYSQLDATABASE/"
        . "MYSQLUSER/MYSQLPASSWORD estão definidas no serviço do APP.\n");
}

$arquivo = __DIR__ . '/banco-migration.sql';
if (!is_file($arquivo)) {
    http_response_code(500);
    exit("Arquivo banco-migration.sql não encontrado.\n");
}

$sql = file_get_contents($arquivo);
// Remove linhas de comentário (-- ...) e quebra em comandos individuais.
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$comandos = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');

$ok = 0;
$erros = 0;
foreach ($comandos as $i => $cmd) {
    try {
        $pdo->exec($cmd);
        $ok++;
    } catch (Throwable $e) {
        $erros++;
        echo "ERRO no comando #" . ($i + 1) . ": " . $e->getMessage() . "\n";
        echo "  SQL: " . substr(preg_replace('/\s+/', ' ', $cmd), 0, 120) . "...\n";
    }
}

echo "\nComandos executados com sucesso: {$ok} | com erro: {$erros}\n\n";

// Relatório final
try {
    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas no banco (" . count($tabelas) . "): " . implode(', ', $tabelas) . "\n";

    $admin = $pdo->query("SELECT email FROM usuarios WHERE tipo = 'admin' LIMIT 1")->fetchColumn();
    echo "Admin encontrado: " . ($admin ?: '(nenhum)') . "\n";
} catch (Throwable $e) {
    echo "Aviso ao gerar relatório: " . $e->getMessage() . "\n";
}

echo "\n== Fim. Se tudo certo, avise para eu remover este arquivo. ==\n";
