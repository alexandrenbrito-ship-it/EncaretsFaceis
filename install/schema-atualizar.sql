-- ================================================
-- BANCO DE DADOS - ENCARTES FÁCEIS
-- Execute este script no phpMyAdmin
-- ================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ================================================
-- USUÁRIOS E AUTENTICAÇÃO
-- ================================================
DROP TABLE IF EXISTS enc_usuarios;
CREATE TABLE enc_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'lojista') NOT NULL DEFAULT 'lojista',
    ativo BOOLEAN DEFAULT TRUE,
    token_recuperacao VARCHAR(100) NULL,
    token_expiracao DATETIME NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL
);

-- ================================================
-- PLANOS DE ASSINATURA
-- ================================================
DROP TABLE IF EXISTS enc_planos;
CREATE TABLE enc_planos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    preco_mensal DECIMAL(10,2) NOT NULL,
    limite_encartes INT DEFAULT 5 COMMENT '-1 = ilimitado',
    limite_notificacoes_mes INT DEFAULT 500,
    limite_imagens_por_galeria INT DEFAULT 10,
    permite_mapa BOOLEAN DEFAULT TRUE,
    permite_estatisticas_avancadas BOOLEAN DEFAULT FALSE,
    permite_exportacao BOOLEAN DEFAULT FALSE,
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    ordem_exibicao INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- LOJISTAS (TENANTS)
-- ================================================
DROP TABLE IF EXISTS enc_lojistas;
CREATE TABLE enc_lojistas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    plano_id INT NOT NULL,
    nome_loja VARCHAR(150) NOT NULL,
    subdominio VARCHAR(100) UNIQUE NOT NULL,
    cnpj VARCHAR(20) NULL,
    telefone VARCHAR(20) NULL,
    endereco TEXT NULL,
    logo_url VARCHAR(255) NULL,
    status_assinatura ENUM('ativa','cancelada','vencida','trial') DEFAULT 'trial',
    data_inicio DATE NULL,
    data_validade DATE NULL,
    mp_subscription_id VARCHAR(100) NULL,
    config_pwa JSON NULL,
    recursos_consumidos JSON NULL,
    limite_custom JSON NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES enc_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES enc_planos(id)
);

-- ================================================
-- TEMPLATES DE ENCARTES
-- ================================================
DROP TABLE IF EXISTS enc_templates_encarte;
CREATE TABLE enc_templates_encarte (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50) NULL,
    estrutura_html TEXT NOT NULL,
    estrutura_css TEXT NULL,
    configuracao_blocos JSON NULL,
    thumbnail VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    uso_count INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- ENCARTES DOS LOJISTAS
-- ================================================
DROP TABLE IF EXISTS enc_encartes;
CREATE TABLE enc_encartes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lojista_id INT NOT NULL,
    template_id INT NULL,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    dados_completos JSON NOT NULL,
    publicado BOOLEAN DEFAULT FALSE,
    destaque BOOLEAN DEFAULT FALSE,
    data_publicacao DATETIME NULL,
    data_expiracao DATETIME NULL,
    views INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NULL,
    UNIQUE KEY unique_slug_lojista (lojista_id, slug),
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES enc_templates_encarte(id) ON DELETE SET NULL
);

-- ================================================
-- CLIENTES PWA
-- ================================================
DROP TABLE IF EXISTS enc_clientes_pwa;
CREATE TABLE enc_clientes_pwa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lojista_id INT NOT NULL,
    nome VARCHAR(100) NULL,
    email VARCHAR(150) NULL,
    endpoint_push TEXT NULL,
    push_p256dh TEXT NULL,
    push_auth TEXT NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    dispositivo VARCHAR(20) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE
);

-- ================================================
-- LOCALIZAÇÕES DOS CLIENTES
-- ================================================
DROP TABLE IF EXISTS enc_localizacoes_clientes;
CREATE TABLE enc_localizacoes_clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_pwa_id INT NOT NULL,
    lojista_id INT NOT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    pais VARCHAR(50) DEFAULT 'Brasil',
    precisao_metros INT NULL,
    consentimento_explicito BOOLEAN DEFAULT TRUE,
    data_autorizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao DATETIME NULL,
    FOREIGN KEY (cliente_pwa_id) REFERENCES enc_clientes_pwa(id) ON DELETE CASCADE,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE
);

-- ================================================
-- VISUALIZAÇÕES DETALHADAS
-- ================================================
DROP TABLE IF EXISTS enc_visualizacoes_encarte;
CREATE TABLE enc_visualizacoes_encarte (
    id INT PRIMARY KEY AUTO_INCREMENT,
    encarte_id INT NOT NULL,
    lojista_id INT NOT NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    pais VARCHAR(50) NULL,
    ip_hash VARCHAR(64) NULL,
    dispositivo ENUM('mobile','desktop','tablet') DEFAULT 'desktop',
    navegador VARCHAR(50) NULL,
    sistema_operacional VARCHAR(50) NULL,
    resolucao_tela VARCHAR(20) NULL,
    referencia VARCHAR(255) NULL,
    duracao_segundos INT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (encarte_id) REFERENCES enc_encartes(id) ON DELETE CASCADE,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE,
    INDEX idx_lojista_data (lojista_id, data_hora),
    INDEX idx_encarte_data (encarte_id, data_hora)
);

-- ================================================
-- VISUALIZAÇÕES DA VITRINE
-- ================================================
DROP TABLE IF EXISTS enc_visualizacoes_vitrine;
CREATE TABLE enc_visualizacoes_vitrine (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lojista_id INT NOT NULL,
    ip_hash VARCHAR(64) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    dispositivo ENUM('mobile','desktop','tablet') DEFAULT 'desktop',
    navegador VARCHAR(50) NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE,
    INDEX idx_lojista_data (lojista_id, data_hora)
);

-- ================================================
-- NOTIFICAÇÕES PUSH
-- ================================================
DROP TABLE IF EXISTS enc_notificacoes_push;
CREATE TABLE enc_notificacoes_push (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lojista_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    icone_url VARCHAR(255) NULL,
    url_destino VARCHAR(255) NULL,
    segmento_cidade VARCHAR(100) NULL,
    total_enviado INT DEFAULT 0,
    total_falhas INT DEFAULT 0,
    status ENUM('pendente','enviando','concluido','erro') DEFAULT 'pendente',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id) ON DELETE CASCADE
);

-- ================================================
-- PAGAMENTOS E ASSINATURAS
-- ================================================
DROP TABLE IF EXISTS enc_pagamentos;
CREATE TABLE enc_pagamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lojista_id INT NOT NULL,
    plano_id INT NOT NULL,
    mp_payment_id VARCHAR(100) NULL,
    mp_subscription_id VARCHAR(100) NULL,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('pendente','aprovado','recusado','cancelado','estornado') DEFAULT 'pendente',
    periodo_inicio DATE NULL,
    periodo_fim DATE NULL,
    dados_webhook JSON NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lojista_id) REFERENCES enc_lojistas(id),
    FOREIGN KEY (plano_id) REFERENCES enc_planos(id)
);

-- ================================================
-- CONFIGURAÇÕES GLOBAIS
-- ================================================
DROP TABLE IF EXISTS enc_configuracoes;
CREATE TABLE enc_configuracoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NULL,
    descricao VARCHAR(255) NULL,
    data_atualizacao DATETIME NULL
);

-- ================================================
-- LOGS DE ATIVIDADE
-- ================================================
DROP TABLE IF EXISTS enc_logs_atividade;
CREATE TABLE enc_logs_atividade (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NULL,
    lojista_id INT NULL,
    tipo ENUM('admin','lojista','sistema','webhook') DEFAULT 'sistema',
    acao VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    ip VARCHAR(45) NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_lojista (lojista_id)
);

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================
-- DADOS INICIAIS (SEED)
-- ================================================

-- Inserir planos padrão (apenas se não existirem)
INSERT INTO enc_planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) 
SELECT 'Básico', 'Ideal para pequenos negócios', 29.90, 5, 500, 10, TRUE, FALSE, FALSE, FALSE, 1
WHERE NOT EXISTS (SELECT 1 FROM enc_planos WHERE nome = 'Básico');

INSERT INTO enc_planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) 
SELECT 'Profissional', 'Para lojas em crescimento', 79.90, 20, 2000, 50, TRUE, TRUE, FALSE, TRUE, 2
WHERE NOT EXISTS (SELECT 1 FROM enc_planos WHERE nome = 'Profissional');

INSERT INTO enc_planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) 
SELECT 'Enterprise', 'Sem limites, controle total', 199.90, -1, 10000, -1, TRUE, TRUE, TRUE, FALSE, 3
WHERE NOT EXISTS (SELECT 1 FROM enc_planos WHERE nome = 'Enterprise');

-- Inserir configurações padrão
INSERT INTO enc_configuracoes (chave, valor, descricao) 
SELECT 'sistema_nome', 'Encartes Fáceis', 'Nome do sistema'
WHERE NOT EXISTS (SELECT 1 FROM enc_configuracoes WHERE chave = 'sistema_nome');

INSERT INTO enc_configuracoes (chave, valor, descricao) 
SELECT 'url_base', 'https://encartesfaceis.online', 'URL raiz da aplicação'
WHERE NOT EXISTS (SELECT 1 FROM enc_configuracoes WHERE chave = 'url_base');

-- Template exemplo
INSERT INTO enc_templates_encarte (nome, descricao, categoria, estrutura_html, estrutura_css, configuracao_blocos) 
SELECT 'Supermercado Clássico', 'Layout padrão para supermercados', 'supermercado',
'<div class="encarte"><header class="enc-header">{{cabecalho_titulo}}</header><section class="enc-produtos">{{produtos}}</section><footer class="enc-rodape">{{rodape_texto}}</footer></div>',
'.encarte{font-family:Arial,sans-serif;max-width:800px;margin:0 auto}.enc-header{background:{{cor_primaria}};color:#fff;padding:20px;text-align:center}.enc-produtos{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:20px}.enc-rodape{background:#333;color:#fff;padding:10px;text-align:center}',
'{"blocos":["cabecalho","produtos","galeria","rodape"],"permite_adicionar_produtos":true,"max_produtos_por_linha":4}'
WHERE NOT EXISTS (SELECT 1 FROM enc_templates_encarte WHERE nome = 'Supermercado Clássico');

-- ================================================
--Resultado
-- ================================================
SELECT 'Tabelas criadas com sucesso!' AS mensagem;
SELECT COUNT(*) AS total_tabelas FROM information_schema.tables WHERE table_schema = DATABASE();