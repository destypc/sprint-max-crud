-- ============================================================
-- Sprint Max -- Execute este SQL no phpMyAdmin agora
-- Banco: crud-sistema > aba SQL > Executar
-- Estas instrucoes nao causam nenhum erro.
-- ============================================================

-- Garante que os acentos das tags sejam gravados corretamente,
-- independente do charset padrao do cliente (ex.: cp850 no console Windows).
SET NAMES utf8mb4;

-- TABELAS NOVAS (CREATE IF NOT EXISTS = seguro, nunca da erro)

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

-- 9. Tags e visibilidade nos produtos (apenas se nao existirem)
ALTER TABLE produtos ADD COLUMN IF NOT EXISTS tags    VARCHAR(255) NULL;
ALTER TABLE produtos ADD COLUMN IF NOT EXISTS visivel TINYINT(1)   NOT NULL DEFAULT 1;

-- 10. Sprint Max — Remove o sistema de status de PRODUTO

ALTER TABLE produtos DROP COLUMN IF EXISTS status;

-- ============================================================
-- 11. Sistema de Tags — catalogo reutilizavel + relacionamento
-- ============================================================

-- Catalogo de tags: cada tag existe uma unica vez e pode ser
-- reutilizada por qualquer produto.
CREATE TABLE IF NOT EXISTS tags (
    id         INT         AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(50) NOT NULL,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tag_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relacionamento N:N entre produtos e tags.
CREATE TABLE IF NOT EXISTS produto_tags (
    produto_id INT NOT NULL,
    tag_id     INT NOT NULL,
    PRIMARY KEY (produto_id, tag_id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed do catalogo com as tags padrao do sistema.
INSERT IGNORE INTO tags (nome) VALUES
    ('Promoção'), ('Lançamento'), ('Exclusivo'), ('Mais Vendido'),
    ('Novidade'), ('Oferta'), ('Kit'), ('Edição Limitada');

-- Migra as tags ja gravadas em produtos.tags (CSV) para o catalogo.
INSERT IGNORE INTO tags (nome)
SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.tags, ',', n.num), ',', -1)) AS nome
FROM produtos p
JOIN (SELECT 1 num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) n
  ON n.num <= (LENGTH(p.tags) - LENGTH(REPLACE(p.tags, ',', '')) + 1)
WHERE p.tags IS NOT NULL AND p.tags <> ''
  AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.tags, ',', n.num), ',', -1)) <> '';

-- Recria os vinculos produto <-> tag a partir do CSV existente.
INSERT IGNORE INTO produto_tags (produto_id, tag_id)
SELECT p.id, t.id
FROM produtos p
JOIN (SELECT 1 num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) n
  ON n.num <= (LENGTH(p.tags) - LENGTH(REPLACE(p.tags, ',', '')) + 1)
JOIN tags t ON t.nome = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p.tags, ',', n.num), ',', -1))
WHERE p.tags IS NOT NULL AND p.tags <> '';

-- ============================================================
-- 12. Recuperacao de senha (tokens de redefinicao)
-- ============================================================
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
