-- ============================================
-- FAESMA - Seed Data
-- Sample data for initial setup
-- ============================================

USE faesma_db;

INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'FAESMA - Faculdade Alcance de Ensino Superior do Maranhão', 'text', 'Nome do site'),
('site_email', 'contato@faesma.com.br', 'email', 'E-mail principal'),
('site_phone', '(98) 98848-7847', 'text', 'Telefone principal'),
('site_address', 'Rua Fé em Deus, 150, 65055-190 - Jardim São Cristóvão, São Luís - MA', 'text', 'Endereço'),
('facebook_url', 'https://facebook.com/faesma', 'url', 'URL do Facebook'),
('instagram_url', 'https://www.instagram.com/faculdadefaesma', 'url', 'URL do Instagram');

INSERT INTO pages (titulo, slug, conteudo, status) VALUES
('Sobre a FAESMA', 
 'sobre', 
 '<h2>História</h2><p>A FAESMA - Faculdade Alcance de Ensino Superior do Maranhão foi fundada com o objetivo de democratizar o acesso ao ensino superior de qualidade no Maranhão.</p><h2>Missão</h2><p>Nossa missão é formar profissionais competentes, éticos e comprometidos com o desenvolvimento social.</p><h2>Visão</h2><p>Ser reconhecida como referência em ensino superior no Maranhão.</p>',
 'publicado'),

('Política de Privacidade', 
 'privacidade', 
 '<h2>Política de Privacidade</h2><p>A FAESMA respeita a privacidade de seus visitantes e alunos...</p>',
 'publicado');

INSERT INTO course_modalities (nome, slug, descricao) VALUES
('EAD', 'ead', 'Ensino a distância 100% online'),
('Híbrido', 'hibrido', 'Combinação de aulas presenciais e online');

INSERT INTO course_categories (nome, slug, descricao, ordem) VALUES
('Graduação', 'graduacao', 'Cursos de bacharelado e licenciatura', 1),
('Pós-graduação', 'pos-graduacao', 'Cursos de especialização e MBA', 2);