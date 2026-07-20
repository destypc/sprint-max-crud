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

// SQL embutido (o banco-migration.sql não é copiado para a imagem — está no
// .dockerignore). Mantém o schema junto do script protegido por chave.
$sql = <<<'SQL'
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    senha       VARCHAR(255) NOT NULL,
    tipo        ENUM('admin','usuario')   NOT NULL DEFAULT 'usuario',
    status      ENUM('ativo','inativo')   NOT NULL DEFAULT 'ativo',
    foto_perfil VARCHAR(255) NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS produtos (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(70)   NOT NULL,
    marca      VARCHAR(100)  NULL,
    cor        VARCHAR(50)   NULL,
    tags       VARCHAR(255)  NULL,
    visivel    TINYINT(1)    NOT NULL DEFAULT 1,
    categoria  VARCHAR(100)  NOT NULL,
    preco      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantidade INT           NOT NULL DEFAULT 0,
    descricao  TEXT          NULL,
    imagem     VARCHAR(255)  NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT          NULL,
    acao       VARCHAR(100) NOT NULL,
    descricao  VARCHAR(255) NOT NULL,
    data       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT           NOT NULL,
    total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status     ENUM('pendente','preparando','enviado','entregue','cancelado') NOT NULL DEFAULT 'pendente',
    observacao TEXT          NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedido_itens (
    id             INT           AUTO_INCREMENT PRIMARY KEY,
    pedido_id      INT           NOT NULL,
    produto_id     INT           NOT NULL,
    quantidade     INT           NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)  ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS favoritos (
    id         INT      AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT      NOT NULL,
    produto_id INT      NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_favorito (usuario_id, produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notificacoes (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT          NOT NULL,
    titulo     VARCHAR(120) NOT NULL,
    mensagem   VARCHAR(255) NOT NULL,
    lida       TINYINT(1)   NOT NULL DEFAULT 0,
    tipo       ENUM('info','sucesso','aviso','erro') NOT NULL DEFAULT 'info',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
    id         INT         AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(50) NOT NULL,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tag_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS produto_tags (
    produto_id INT NOT NULL,
    tag_id     INT NOT NULL,
    PRIMARY KEY (produto_id, tag_id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO tags (nome) VALUES
    ('Promoção'), ('Lançamento'), ('Exclusivo'), ('Mais Vendido'),
    ('Novidade'), ('Oferta'), ('Kit'), ('Edição Limitada');

CREATE TABLE IF NOT EXISTS recuperacao_senha (
    id         INT         AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT         NOT NULL,
    token      VARCHAR(64) NOT NULL,
    expira_em  DATETIME    NOT NULL,
    usado      TINYINT(1)  NOT NULL DEFAULT 0,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_recuperacao_token (token),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO usuarios (nome, email, senha, tipo, status) VALUES
    ('Administrador', 'admin@sprintmax.com',
     '$2y$10$4.ppy0htGhgNWmmdFIGWkOYfATmfRnmkIEhh8l/eSVJJL2btg2gqy',
     'admin', 'ativo');
SQL;

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
