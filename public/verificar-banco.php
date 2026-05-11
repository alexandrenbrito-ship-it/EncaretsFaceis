<?php
header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    
    $db = Database::getConnection();
    
    $stmt = $db->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $resultado = [
        'sucesso' => true,
        'tabelas_encontradas' => count($tabelas),
        'tabelas' => $tabelas,
        'tabelas_faltando' => []
    ];
    
    $esperadas = [
        'enc_usuarios',
        'enc_planos', 
        'enc_lojistas',
        'enc_templates_encarte',
        'enc_encartes',
        'enc_clientes_pwa',
        'enc_localizacoes_clientes',
        'enc_visualizacoes_encarte',
        'enc_visualizacoes_vitrine',
        'enc_notificacoes_push',
        'enc_pagamentos',
        'enc_configuracoes',
        'enc_logs_atividade'
    ];
    
    foreach ($esperadas as $esperada) {
        if (!in_array($esperada, $tabelas)) {
            $resultado['tabelas_faltando'][] = $esperada;
        }
    }
    
    if (!empty($resultado['tabelas_faltando'])) {
        $resultado['acao'] = 'Execute o script schema-atualizar.sql no phpMyAdmin';
    } else {
        $resultado['acao'] = 'Todas as tabelas OK';
    }
    
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage(),
        'possiveis_problemas' => [
            'Banco de dados não existe ou config.inc.php errado',
            'Tabelas não foram criadas'
        ]
    ], JSON_PRETTY_PRINT);
}