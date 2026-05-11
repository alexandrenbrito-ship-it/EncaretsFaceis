<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

define('SECRET_KEY', 'encartes2024_verificar');

$secret = $_GET['chave'] ?? '';

if ($secret !== SECRET_KEY) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Chave inválida'], JSON_PRETTY_PRINT);
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    
    $db = Database::getConnection();
    $relatorio = [];
    $statusGeral = true;
    
    $tabelasEsperadas = [
        'enc_usuarios' => ['id', 'nome', 'email', 'senha_hash', 'tipo', 'ativo', 'data_criacao'],
        'enc_planos' => ['id', 'nome', 'descricao', 'preco_mensal', 'limite_encartes', 'limite_notificacoes_mes', 'limite_imagens_por_galeria'],
        'enc_lojistas' => ['id', 'usuario_id', 'plano_id', 'nome_loja', 'subdominio', 'status_assinatura'],
        'enc_templates_encarte' => ['id', 'nome', 'descricao', 'estrutura_html', 'estrutura_css'],
        'enc_encartes' => ['id', 'lojista_id', 'titulo', 'slug', 'dados_completos', 'publicado', 'views'],
        'enc_clientes_pwa' => ['id', 'lojista_id', 'nome', 'email', 'endpoint_push', 'cidade', 'estado'],
        'enc_localizacoes_clientes' => ['id', 'cliente_pwa_id', 'lojista_id', 'latitude', 'longitude', 'cidade'],
        'enc_visualizacoes_encarte' => ['id', 'encarte_id', 'lojista_id', 'cidade', 'dispositivo', 'data_hora'],
        'enc_visualizacoes_vitrine' => ['id', 'lojista_id', 'cidade', 'dispositivo', 'data_hora'],
        'enc_notificacoes_push' => ['id', 'lojista_id', 'titulo', 'mensagem', 'status', 'data_envio'],
        'enc_pagamentos' => ['id', 'lojista_id', 'plano_id', 'valor', 'status', 'data_criacao'],
        'enc_configuracoes' => ['id', 'chave', 'valor'],
        'enc_logs_atividade' => ['id', 'usuario_id', 'lojista_id', 'tipo', 'acao', 'data_hora']
    ];
    
    $stmt = $db->query("SHOW TABLES");
    $tabelasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tabelasEsperadas as $tabela => $colunas) {
        if (!in_array($tabela, $tabelasExistentes)) {
            $relatorio[$tabela] = ['existe' => false, 'status' => 'FALTANDO'];
            $statusGeral = false;
        } else {
            $stmt = $db->query("SHOW COLUMNS FROM $tabela");
            $colunasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $colunasFaltando = array_diff($colunas, $colunasExistentes);
            
            $relatorio[$tabela] = [
                'existe' => true,
                'colunas' => count($colunasExistentes),
                'status' => empty($colunasFaltando) ? 'OK' : 'COLUNAS FALTANDO: ' . implode(', ', $colunasFaltando)
            ];
            
            if (!empty($colunasFaltando)) $statusGeral = false;
        }
    }
    
    echo json_encode([
        'sucesso' => $statusGeral,
        'mensagem' => $statusGeral ? 'Todas as tabelas OK!' : 'Alguns problemas encontrados',
        'total_esperadas' => count($tabelasEsperadas),
        'total_existem' => count($tabelasExistentes),
        'tabelas' => $relatorio,
        'data_verificacao' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()], JSON_PRETTY_PRINT);
}