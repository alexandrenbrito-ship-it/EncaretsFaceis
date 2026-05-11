<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('SECRET_KEY', 'encartes2024');

$secret = $_GET['chave'] ?? $_POST['chave'] ?? '';

if ($secret !== SECRET_KEY) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Chave de acesso inválida'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    
    $db = Database::getConnection();
    $resultados = [];
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tabelas = [
        "enc_usuarios" => "CREATE TABLE IF NOT EXISTS enc_usuarios (
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
    )",
    
    "enc_planos" => "CREATE TABLE IF NOT EXISTS enc_planos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(50) NOT NULL,
        descricao TEXT,
        preco_mensal DECIMAL(10,2) NOT NULL,
        limite_encartes INT DEFAULT 5,
        limite_notificacoes_mes INT DEFAULT 500,
        limite_imagens_por_galeria INT DEFAULT 10,
        permite_mapa BOOLEAN DEFAULT TRUE,
        permite_estatisticas_avancadas BOOLEAN DEFAULT FALSE,
        permite_exportacao BOOLEAN DEFAULT FALSE,
        destaque BOOLEAN DEFAULT FALSE,
        ativo BOOLEAN DEFAULT TRUE,
        ordem_exibicao INT DEFAULT 0,
        data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    
    "enc_lojistas" => "CREATE TABLE IF NOT EXISTS enc_lojistas (
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
    )",
    
    "enc_templates_encarte" => "CREATE TABLE IF NOT EXISTS enc_templates_encarte (
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
    )",
    
    "enc_encartes" => "CREATE TABLE IF NOT EXISTS enc_encartes (
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
    )",
    
    "enc_clientes_pwa" => "CREATE TABLE IF NOT EXISTS enc_clientes_pwa (
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
    )",
    
    "enc_localizacoes_clientes" => "CREATE TABLE IF NOT EXISTS enc_localizacoes_clientes (
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
    )",
    
    "enc_visualizacoes_encarte" => "CREATE TABLE IF NOT EXISTS enc_visualizacoes_encarte (
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
    )",
    
    "enc_visualizacoes_vitrine" => "CREATE TABLE IF NOT EXISTS enc_visualizacoes_vitrine (
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
    )",
    
    "enc_notificacoes_push" => "CREATE TABLE IF NOT EXISTS enc_notificacoes_push (
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
    )",
    
    "enc_pagamentos" => "CREATE TABLE IF NOT EXISTS enc_pagamentos (
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
    )",
    
    "enc_configuracoes" => "CREATE TABLE IF NOT EXISTS enc_configuracoes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT NULL,
        descricao VARCHAR(255) NULL,
        data_atualizacao DATETIME NULL
    )",
    
    "enc_logs_atividade" => "CREATE TABLE IF NOT EXISTS enc_logs_atividade (
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
    )"
    ];
    
    foreach ($tabelas as $nome => $sql) {
        try {
            $db->exec($sql);
            $resultados[$nome] = 'Criada/Verificada com sucesso';
        } catch (Exception $e) {
            $resultados[$nome] = 'Erro: ' . $e->getMessage();
        }
    }
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    $stmt = $db->query("SHOW TABLES");
    $tabelasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Schema atualizado com sucesso',
        'tabelas' => $resultados,
        'total_tabelas' => count($tabelasExistentes),
        'tabelas_no_banco' => $tabelasExistentes
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao atualizar schema: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}