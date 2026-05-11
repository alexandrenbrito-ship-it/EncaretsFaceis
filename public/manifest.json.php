<?php
header('Content-Type: application/json');

$subdominio = $_GET['s'] ?? '';

$lojista = null;
$configPwa = ['cor_primaria' => '#2563eb', 'cor_secundaria' => '#1d4ed8', 'nome' => 'Minha Loja'];

if ($subdominio && file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM enc_lojistas WHERE subdominio = ?");
        $stmt->execute([$subdominio]);
        $lojista = $stmt->fetch();
        
        if ($lojista && !empty($lojista['config_pwa'])) {
            $configPwa = json_decode($lojista['config_pwa'], true) ?? $configPwa;
            $configPwa['nome'] = $lojista['nome_loja'];
        }
    } catch (Exception $e) {
    }
}

$manifest = [
    'name' => $configPwa['nome'] . ' - Encartes',
    'short_name' => substr($configPwa['nome'], 0, 12),
    'description' => 'Encartes digitais e ofertas',
    'start_url' => '/public/?s=' . $subdominio,
    'display' => 'standalone',
    'background_color' => '#ffffff',
    'theme_color' => $configPwa['cor_primaria'],
    'icons' => [
        [
            'src' => '/assets/img/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png'
        ],
        [
            'src' => '/assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png'
        ]
    ]
];

echo json_encode($manifest);