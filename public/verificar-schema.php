<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

define('SECRET_KEY', 'encartes2024');

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
        'enc_usuarios' => [
            'colunas' => ['id', 'nome', 'email', 'senha_hash', 'tipo', 'ativo', 'token_recuperacao', 'token_expiracao', 'data_criacao', 'ultimo_acesso'],
            'indices' => ['PRIMARY', 'email']
        ],
        'enc_planos' => [
            'colunas' => ['id', 'nome', 'descricao', 'preco_mensal', 'limite_encartes', 'limite_notificacoes_mes', 'limite_imagens_por_galeria', 'permite_mapa', 'permite_estatisticas_avancadas', 'permite_exportacao', 'destaque', 'ativo', 'ordem_exibicao', 'data_criacao'],
            'indices' => ['PRIMARY']
        ],
        'enc_lojistas' => [
            'colunas' => ['id', 'usuario_id', 'plano_id', 'nome_loja', 'subdominio', 'cnpj', 'telefone', 'endereco', 'logo_url', 'status_assinatura', 'data_inicio', 'data_validade', 'mp_subscription_id', 'config_pwa', 'recursos_consumidos', 'limite_custom', 'data_criacao'],
            'indices' => ['PRIMARY', 'subdominio', 'usuario_id', 'plano_id']
        ],
        'enc_templates_encarte' => [
            'colunas' => ['id', 'nome', 'descricao', 'categoria', 'estrutura_html', 'estrutura_css', 'configuracao_blocos', 'thumbnail', 'ativo', 'uso_count', 'data_criacao'],
            'indices' => ['PRIMARY']
        ],
        'enc_encartes' => [
            'colunas' => ['id', 'lojista_id', 'template_id', 'titulo', 'slug', 'descricao', 'dados_completos', 'publicado', 'destaque', 'data_publicacao', 'data_expiracao', 'views', 'data_criacao', 'data_atualizacao'],
            'indices' => ['PRIMARY', 'slug', 'lojista_id', 'template_id', 'unique_slug_lojista']
        ],
        'enc_clientes_pwa' => [
            'colunas' => ['id', 'lojista_id', 'nome', 'email', 'endpoint_push', 'push_p256dh', 'push_auth', 'cidade', 'estado', 'dispositivo', 'ativo', 'data_cadastro', 'ultimo_acesso'],
            'indices' => ['PRIMARY', 'lojista_id']
        ],
        'enc_localizacoes_clientes' => [
            'colunas' => ['id', 'cliente_pwa_id', 'lojista_id', 'latitude', 'longitude', 'cidade', 'estado', 'pais', 'precisao_metros', 'consentimento_explicito', 'data_autorizacao', 'ultima_atualizacao'],
            'indices' => ['PRIMARY', 'cliente_pwa_id', 'lojista_id']
        ],
        'enc_visualizacoes_encarte' => [
            'colunas' => ['id', 'encarte_id', 'lojista_id', 'cidade', 'estado', 'pais', 'ip_hash', 'dispositivo', 'navegador', 'sistema_operacional', 'resolucao_tela', 'referencia', 'duracao_segundos', 'data_hora'],
            'indices' => ['PRIMARY', 'encarte_id', 'lojista_id', 'idx_lojista_data', 'idx_encarte_data']
        ],
        'enc_visualizacoes_vitrine' => [
            'colunas' => ['id', 'lojista_id', 'ip_hash', 'cidade', 'estado', 'dispositivo', 'navegador', 'data_hora'],
            'indices' => ['PRIMARY', 'lojista_id', 'idx_lojista_data']
        ],
        'enc_notificacoes_push' => [
            'colunas' => ['id', 'lojista_id', 'titulo', 'mensagem', 'icone_url', 'url_destino', 'segmento_cidade', 'total_enviado', 'total_falhas', 'status', 'data_envio'],
            'indices' => ['PRIMARY', 'lojista_id']
        ],
        'enc_pagamentos' => [
            'colunas' => ['id', 'lojista_id', 'plano_id', 'mp_payment_id', 'mp_subscription_id', 'valor', 'status', 'periodo_inicio', 'periodo_fim', 'dados_webhook', 'data_criacao'],
            'indices' => ['PRIMARY', 'lojista_id', 'plano_id']
        ],
        'enc_configuracoes' => [
            'colunas' => ['id', 'chave', 'valor', 'descricao', 'data_atualizacao'],
            'indices' => ['PRIMARY', 'chave']
        ],
        'enc_logs_atividade' => [
            'colunas' => ['id', 'usuario_id', 'lojista_id', 'tipo', 'acao', 'descricao', 'ip', 'data_hora'],
            'indices' => ['PRIMARY', 'usuario_id', 'lojista_id', 'idx_usuario', 'idx_lojista']
        ]
    ];
    
    $stmt = $db->query("SHOW TABLES");
    $tabelasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tabelasEsperadas as $tabela => $esperado) {
        $tabelaInfo = ['existe' => false, 'colunas' => [], 'indices' => [], 'problemas' => []];
        
        if (!in_array($tabela, $tabelasExistentes)) {
            $tabelaInfo['problemas'][] = 'Tabela não existe';
            $statusGeral = false;
        } else {
            $tabelaInfo['existe'] = true;
            
            $stmt = $db->query("SHOW COLUMNS FROM $tabela");
            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $tabelaInfo['colunas'] = $colunas;
            
            $colunasFaltando = array_diff($esperado['colunas'], $colunas);
            if (!empty($colunasFaltando)) {
                $tabelaInfo['problemas'][] = 'Colunas faltando: ' . implode(', ', $colunasFaltando);
                $statusGeral = false;
            }
            
            $stmt = $db->query("SHOW INDEX FROM $tabela");
            $indices = $stmt->fetchAll();
            $indicesNomes = array_unique(array_column($indices, 'Key_name'));
            $tabelaInfo['indices'] = $indicesNomes;
            
            $indicesFaltando = array_diff($esperado['indices'], $indicesNomes);
            if (!empty($indicesFaltando)) {
                $tabelaInfo['problemas'][] = 'Índices faltando: ' . implode(', ', $indicesFaltando);
                $statusGeral = false;
            }
        }
        
        $relatorio[$tabela] = $tabelaInfo;
    }
    
    $tabelasExtras = array_diff($tabelasExistentes, array_keys($tabelasEsperadas));
    if (!empty($tabelasExtras)) {
        $relatorio['_tabelas_extras'] = $tabelasExtras;
    }
    
    echo json_encode([
        'sucesso' => $statusGeral,
        'mensagem' => $statusGeral ? 'Todas as tabelas estão corretas' : 'Alguns problemas foram encontrados',
        'total_esperadas' => count($tabelasEsperadas),
        'total_existem' => count($tabelasExistentes),
        'tabelas' => $relatorio
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], JSON_PRETTY_PRINT);
}