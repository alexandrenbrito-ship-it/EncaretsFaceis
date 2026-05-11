<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$uploadPath = __DIR__ . '/../../assets/uploads/lojista_' . $lojistaId;
$uploadUrl = UPLOAD_URL . 'lojista_' . $lojistaId;

$imagens = [];

if (is_dir($uploadPath)) {
    $files = scandir($uploadPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($uploadPath . '/' . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $imagens[] = [
                    'nome' => $file,
                    'url' => $uploadUrl . '/' . $file,
                    'caminho' => $uploadPath . '/' . $file
                ];
            }
        }
    }
}

echo json_encode([
    'sucesso' => true,
    'imagens' => $imagens,
    'total' => count($imagens)
]);