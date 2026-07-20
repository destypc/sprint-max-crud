-- ============================================================
-- Sprint Max — Schema completo do banco de dados
-- ============================================================
-- Este arquivo cria TODAS as tabelas do zero, na ordem correta
-- de dependências (chaves estrangeiras). É seguro rodar em um
-- banco novo (deploy no Railway/produção) e também re-rodar em um
-- banco já existente: todos os CREATE usam IF NOT EXISTS.
--
-- Compatível com MySQL 8+ e MariaDB. Não usa "ALTER ... IF NOT
-- EXISTS" (extensão só do MariaDB) — as tabelas já nascem completas.
--
-- Como usar:
--   • Railway/produção:  rode este SQL no banco recém-criado.
--   • Local (XAMPP):     crie o banco "crud-sistema" e importe aqui.
-- ============================================================

SET NAMES utf8mb4;

-- ── Tabelas base ────────────────────────────────────────────

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

-- ── Pedidos ─────────────────────────────────────────────────

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

-- ── Favoritos ───────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS favoritos (
    id         INT      AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT      NOT NULL,
    produto_id INT      NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_favorito (usuario_id, produto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Notificações ────────────────────────────────────────────

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

-- ── Sistema de Tags (catálogo reutilizável + relacionamento N:N) ──

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

-- Seed do catálogo com as tags padrão do sistema.
INSERT IGNORE INTO tags (nome) VALUES
    ('Promoção'), ('Lançamento'), ('Exclusivo'), ('Mais Vendido'),
    ('Novidade'), ('Oferta'), ('Kit'), ('Edição Limitada');

-- ── Recuperação de senha (tokens de redefinição) ────────────

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

-- ============================================================
-- Usuário administrador inicial
-- ------------------------------------------------------------
-- Sem este seed, um banco novo não teria nenhum admin e ninguém
-- conseguiria acessar o painel (o cadastro público cria apenas
-- contas do tipo "usuario").
--
--   E-mail: admin@sprintmax.com
--   Senha:  admin123
--
-- >>> TROQUE ESTA SENHA logo após o primeiro login (tela Perfil). <<<
-- ============================================================
INSERT IGNORE INTO usuarios (nome, email, senha, tipo, status) VALUES
    ('Administrador', 'admin@sprintmax.com',
     '$2y$10$4.ppy0htGhgNWmmdFIGWkOYfATmfRnmkIEhh8l/eSVJJL2btg2gqy',
     'admin', 'ativo');
