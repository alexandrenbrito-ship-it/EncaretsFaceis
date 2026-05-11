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
$periodo = $_GET['periodo'] ?? 'mes';

try {
    $db = Database::getConnection();

    $wherePeriodo = '';
    if ($periodo === 'mes') {
        $wherePeriodo = "AND MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
    } elseif ($periodo === 'semana') {
        $wherePeriodo = "AND data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($periodo === 'ano') {
        $wherePeriodo = "AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
    }

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM enc_visualizacoes_encarte WHERE lojista_id = ? $wherePeriodo");
    $stmt->execute([$lojistaId]);
    $totalViews = $stmt->fetch()['total'];

    $stmt = $db->prepare("
        SELECT dispositivo, COUNT(*) as total 
        FROM enc_visualizacoes_encarte 
        WHERE lojista_id = ? $wherePeriodo
        GROUP BY dispositivo
    ");
    $stmt->execute([$lojistaId]);
    $porDispositivo = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT cidade, COUNT(*) as total 
        FROM enc_visualizacoes_encarte 
        WHERE lojista_id = ? AND cidade IS NOT NULL $wherePeriodo
        GROUP BY cidade
        ORDER BY total DESC
        LIMIT 10
    ");
    $stmt->execute([$lojistaId]);
    $porCidade = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT DATE(data_hora) as data, COUNT(*) as total 
        FROM enc_visualizacoes_encarte 
        WHERE lojista_id = ? $wherePeriodo
        GROUP BY DATE(data_hora)
        ORDER BY data DESC
        LIMIT 30
    ");
    $stmt->execute([$lojistaId]);
    $porDia = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT e.titulo, COUNT(v.id) as visualizacoes
        FROM enc_encartes e
        LEFT JOIN enc_visualizacoes_encarte v ON e.id = v.encarte_id $wherePeriodo
        WHERE e.lojista_id = ?
        GROUP BY e.id, e.titulo
        ORDER BY visualizacoes DESC
        LIMIT 10
    ");
    $stmt->execute([$lojistaId]);
    $porEncarte = $stmt->fetchAll();

    echo json_encode([
        'sucesso' => true,
        'total_views' => $totalViews,
        'por_dispositivo' => $porDispositivo,
        'por_cidade' => $porCidade,
        'por_dia' => $porDia,
        'por_encarte' => $porEncarte
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}