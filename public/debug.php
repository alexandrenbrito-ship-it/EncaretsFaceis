<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$resultado = [
    'php_version' => PHP_VERSION,
    'extensiones' => get_loaded_extensions(),
    'erros' => []
];

try {
    require_once __DIR__ . '/../config/config.php';
    $resultado['config_loaded'] = true;
} catch (Exception $e) {
    $resultado['erros'][] = 'config: ' . $e->getMessage();
}

try {
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getConnection();
    $resultado['database_connected'] = true;
    
    $stmt = $db->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $resultado['tabelas'] = $tabelas;
} catch (Exception $e) {
    $resultado['erros'][] = 'database: ' . $e->getMessage();
}

$uploadPath = __DIR__ . '/../../assets/uploads/';
$resultado['upload_path'] = $uploadPath;
$resultado['upload_path_exists'] = is_dir($uploadPath);

echo json_encode($resultado, JSON_PRETTY_PRINT);