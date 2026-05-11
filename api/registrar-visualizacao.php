<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$encarteId = (int)($_POST['encarte_id'] ?? 0);
$lojistaId = (int)($_POST['lojista_id'] ?? 0);

if (!$encarteId || !$lojistaId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
    exit;
}

try {
    $db = Database::getConnection();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ipHash = hash('sha256', $ip);

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $dispositivo = 'desktop';
    if (preg_match('/mobile/i', $userAgent)) {
        $dispositivo = 'mobile';
    } elseif (preg_match('/tablet/i', $userAgent)) {
        $dispositivo = 'tablet';
    }

    $navegador = 'Outro';
    if (preg_match('/chrome/i', $userAgent)) $navegador = 'Chrome';
    elseif (preg_match('/firefox/i', $userAgent)) $navegador = 'Firefox';
    elseif (preg_match('/safari/i', $userAgent)) $navegador = 'Safari';
    elseif (preg_match('/edge/i', $userAgent)) $navegador = 'Edge';

    $cidade = null;
    $estado = null;

    try {
        $geoApi = file_get_contents('https://ip-api.com/json/' . $ip . '?fields=status,country,region,city');
        $geoData = json_decode($geoApi, true);
        if ($geoData && $geoData['status'] === 'success') {
            $cidade = $geoData['city'] ?? null;
            $estado = $geoData['region'] ?? null;
        }
    } catch (Exception $e) {
    }

    $stmt = $db->prepare("
        INSERT INTO enc_visualizacoes_encarte 
        (encarte_id, lojista_id, ip_hash, cidade, estado, dispositivo, navegador) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$encarteId, $lojistaId, $ipHash, $cidade, $estado, $dispositivo, $navegador]);

    $stmt = $db->prepare("UPDATE enc_encartes SET views = views + 1 WHERE id = ?");
    $stmt->execute([$encarteId]);

    echo json_encode(['sucesso' => true]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}