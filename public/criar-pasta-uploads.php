<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/config.php';
    
    $uploadsPath = __DIR__ . '/../assets/uploads/';
    
    if (!is_dir($uploadsPath)) {
        mkdir($uploadsPath, 0755, true);
    }
    
    echo json_encode([
        'sucesso' => true,
        'path' => $uploadsPath,
        'created' => true
    ]);
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}