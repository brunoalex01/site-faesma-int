-- ============================================
-- FAESMA - Migração: Adicionar campos da view remota
-- Data: 2026-01-30
-- ============================================
-- 
-- Esta migração adiciona campos à tabela course_curriculum
-- que correspondem aos campos da view remota disciplinas_curso_site
-- mas que não existiam na tabela local.
--
-- View remota (disciplinas_curso_site):
-- - disciplina_nome    -> disciplina (JÁ EXISTE)
-- - carga_horaria      -> carga_horaria (JÁ EXISTE)
-- - modulo             -> semestre (JÁ EXISTE)
-- - duracao            -> duracao (NOVO)
-- - modalidade         -> modalidade (NOVO)
-- - cod_externo        -> cod_externo_remoto (NOVO)
-- - id                 -> id_curso_remoto (NOVO)
-- - curso_nome         -> curso_nome_remoto (NOVO)
-- ============================================

USE faesma_db;

-- Adicionar campos faltantes
ALTER TABLE course_curriculum
    ADD COLUMN duracao VARCHAR(100) NULL COMMENT 'Duração da disciplina/módulo (da view remota)' AFTER ementa,
    ADD COLUMN modalidade VARCHAR(255) NULL COMMENT 'Modalidade (EAD, Presencial, etc)' AFTER duracao,
    ADD COLUMN cod_externo_remoto VARCHAR(15) NULL COMMENT 'Código externo do curso (sigla da view remota)' AFTER modalidade,
    ADD COLUMN id_curso_remoto VARCHAR(100) NULL COMMENT 'ID do curso na view remota' AFTER cod_externo_remoto,
    ADD COLUMN curso_nome_remoto VARCHAR(255) NULL COMMENT 'Nome do curso (da view remota)' AFTER id_curso_remoto;

-- Adicionar índice para facilitar buscas por id_curso_remoto
ALTER TABLE course_curriculum
    ADD INDEX idx_id_curso_remoto (id_curso_remoto);

-- ============================================
-- Verificação após migração
-- ============================================
-- Execute para confirmar que os campos foram criados:
-- DESCRIBE course_curriculum;
