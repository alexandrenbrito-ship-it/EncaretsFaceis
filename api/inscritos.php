<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];

try {
    $db = Database::getConnection();
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as total FROM enc_clientes_pwa 
        WHERE lojista_id = ? AND ativo = 1
    ");
    $stmt->execute([$lojistaId]);
    $total = $stmt->fetch()['total'];

    $stmt = $db->prepare("
        SELECT cidade, COUNT(*) as total 
        FROM enc_clientes_pwa 
        WHERE lojista_id = ? AND ativo = 1 AND cidade IS NOT NULL
        GROUP BY cidade
        ORDER BY total DESC
    ");
    $stmt->execute([$lojistaId]);
    $porCidade = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT dispositivo, COUNT(*) as total 
        FROM enc_clientes_pwa 
        WHERE lojista_id = ? AND ativo = 1
        GROUP BY dispositivo
    ");
    $stmt->execute([$lojistaId]);
    $porDispositivo = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT * FROM enc_clientes_pwa 
        WHERE lojista_id = ? AND ativo = 1
        ORDER BY data_cadastro DESC
        LIMIT 50
    ");
    $stmt->execute([$lojistaId]);
    $inscritos = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'total' => $total,
        'por_cidade' => $porCidade,
        'por_dispositivo' => $porDispositivo,
        'inscritos' => $inscritos
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}