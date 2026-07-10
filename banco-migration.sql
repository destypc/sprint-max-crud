-- ============================================================
-- Sprint Max -- Execute este SQL no phpMyAdmin agora
-- Banco: crud-sistema > aba SQL > Executar
-- Estas instrucoes nao causam nenhum erro.
-- ============================================================

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
ALTER TABLE produtos ADD COLUMN tags    VARCHAR(255) NULL;
ALTER TABLE produtos ADD COLUMN visivel TINYINT(1)   NOT NULL DEFAULT 1;