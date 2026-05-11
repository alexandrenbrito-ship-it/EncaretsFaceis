<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$lojistaId = (int)($data['lojista_id'] ?? 0);
$lat = (float)($data['lat'] ?? 0);
$lng = (float)($data['lng'] ?? 0);

if (!$lojistaId || !$lat || !$lng) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
    exit;
}

try {
    $db = Database::getConnection();

    $cidade = null;
    $estado = null;

    try {
        $nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lng;
        $context = stream_context_create(['http' => ['header' => 'User-Agent: EncartesPro/1.0']]);
        $geoData = json_decode(file_get_contents($nominatimUrl, false, $context), true);
        
        if ($geoData && isset($geoData['address'])) {
            $cidade = $geoData['address']['city'] ?? $geoData['address']['town'] ?? $geoData['address']['village'] ?? null;
            $estado = $geoData['address']['state'] ?? null;
            if ($estado && strlen($estado) > 2) {
                $estado = substr($estado, 0, 2);
            }
        }
    } catch (Exception $e) {
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ipHash = hash('sha256', $ip);

    $stmt = $db->prepare("
        SELECT id FROM enc_clientes_pwa 
        WHERE lojista_id = ? AND endpoint_push IS NOT NULL 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$lojistaId]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $stmt = $db->prepare("
            INSERT INTO enc_localizacoes_clientes 
            (cliente_pwa_id, lojista_id, latitude, longitude, cidade, estado, consentimento_explicito) 
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$cliente['id'], $lojistaId, $lat, $lng, $cidade, $estado]);
    }

    echo json_encode(['sucesso' => true]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}