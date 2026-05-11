<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$lojistaId = (int)($data['lojista_id'] ?? 0);
$subscription = $data['subscription'] ?? null;

if (!$lojistaId || !$subscription) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
    exit;
}

try {
    $db = Database::getConnection();

    $endpoint = $subscription['endpoint'] ?? '';
    $keys = $subscription['keys'] ?? [];
    $p256dh = $keys['p256dh'] ?? '';
    $auth = $keys['auth'] ?? '';

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        $geoApi = file_get_contents('https://ip-api.com/json/' . $ip);
        $geoData = json_decode($geoApi, true);
        $cidade = $geoData['city'] ?? null;
        $estado = $geoData['region'] ?? null;
    } catch (Exception $e) {
        $cidade = null;
        $estado = null;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $dispositivo = 'desktop';
    if (preg_match('/mobile/i', $userAgent)) {
        $dispositivo = 'mobile';
    } elseif (preg_match('/tablet/i', $userAgent)) {
        $dispositivo = 'tablet';
    }

    $stmt = $db->prepare("
        INSERT INTO enc_clientes_pwa 
        (lojista_id, endpoint_push, push_p256dh, push_auth, cidade, estado, dispositivo) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$lojistaId, $endpoint, $p256dh, $auth, $cidade, $estado, $dispositivo]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Inscrito com sucesso']);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}