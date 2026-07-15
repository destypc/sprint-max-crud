-- ============================================================
-- Sprint Max — Remove o sistema de status de PRODUTO
-- Banco: crud-sistema > aba SQL > Executar
--
-- O status do produto era 100% derivado da coluna `quantidade`
-- (0 = sem estoque, 1..5 = baixo estoque, >5 = disponível), portanto
-- a coluna era redundante. Os indicadores de estoque da loja passaram
-- a ser calculados diretamente a partir de `quantidade`.
--
-- Seguro: DROP COLUMN IF EXISTS não causa erro se a coluna já não existir.
-- ============================================================

ALTER TABLE produtos DROP COLUMN IF EXISTS status;
