<?php
session_start();
header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];

$baseDir = '/home/u264329520/domains/encartesfaceis.online/public_html';
$uploadPath = $baseDir . '/assets/uploads/lojista_' . $lojistaId;

if (!is_dir($uploadPath)) {
    if (!mkdir($uploadPath, 0755, true)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Não criou pasta']);
        exit;
    }
}

if (!isset($_FILES['imagens'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum arquivo']);
    exit;
}

$files = $_FILES['imagens'];
$uploaded = 0;

for ($i = 0; $i < count($files['name']); $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
    if ($files['size'][$i] > 5 * 1024 * 1024) continue;
    
    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;
    
    $newFilename = 'img_' . time() . '_' . rand(100,999) . '.' . $ext;
    $destination = $uploadPath . '/' . $newFilename;
    
    if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
        $uploaded++;
    }
}

echo json_encode(['sucesso' => true, 'mensagem' => "$uploaded imagem(ns)"]);