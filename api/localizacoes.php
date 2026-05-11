<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$lojistaId = (int)($_GET['lojista_id'] ?? 0);

if (!$lojistaId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetro obrigatório']);
    exit;
}

try {
    $db = Database::getConnection();
    
    $stmt = $db->prepare("
        SELECT lc.*, cp.nome, cp.email
        FROM enc_localizacoes_clientes lc
        JOIN enc_clientes_pwa cp ON lc.cliente_pwa_id = cp.id
        WHERE lc.lojista_id = ?
        ORDER BY lc.ultima_atualizacao DESC
        LIMIT 100
    ");
    $stmt->execute([$lojistaId]);
    $localizacoes = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'localizacoes' => $localizacoes
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}