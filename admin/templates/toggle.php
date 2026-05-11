<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$ativo = (int)($_POST['ativo'] ?? 0);

if (!$id) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE enc_templates_encarte SET ativo = ? WHERE id = ?");
    $stmt->execute([$ativo, $id]);
    
    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}