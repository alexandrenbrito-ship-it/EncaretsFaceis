<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Lojista.php';
require_once __DIR__ . '/../src/Middlewares/LimitCheck.php';

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$lojistaModel = new Lojista();
$lojista = $lojistaModel->find($lojistaId);

if (!$lojista) {
    echo json_encode(['sucesso' => false, 'erro' => 'Lojista não encontrado']);
    exit;
}

$planoModel = new \Src\Models\Plano();
$plano = $planoModel->find($lojista['plano_id']);
$limiteImagens = $plano['limite_imagens_por_galeria'] ?? 10;

$uploadPath = __DIR__ . '/../../assets/uploads/lojista_' . $lojistaId;

if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0755, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxSize = 5 * 1024 * 1024;

$uploaded = [];
$errors = [];

$qtdAtual = count(scandir($uploadPath)) - 2;

if ($limiteImagens !== -1 && $qtdAtual >= $limiteImagens) {
    echo json_encode([
        'sucesso' => false, 
        'erro' => "Limite de {$limiteImagens} imagens atingido. Faça upgrade do seu plano para continuar."
    ]);
    exit;
}

if (isset($_FILES['imagens'])) {
    $files = $_FILES['imagens'];
    $count = count($files['name']);
    
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "Erro ao enviar arquivo: " . $files['name'][$i];
            continue;
        }

        if ($files['size'][$i] > $maxSize) {
            $errors[] = "Arquivo muito grande: " . $files['name'][$i] . " (máx 5MB)";
            continue;
        }

        $mimeType = mime_content_type($files['tmp_name'][$i]);
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "Tipo de arquivo não permitido: " . $files['name'][$i];
            continue;
        }

        $extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "Extensão não permitida: " . $files['name'][$i];
            continue;
        }

        $newFilename = uniqid('img_') . '_' . time() . '.' . $extension;
        $destination = $uploadPath . '/' . $newFilename;

        if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
            $uploaded[] = [
                'nome' => $newFilename,
                'url' => UPLOAD_URL . 'lojista_' . $lojistaId . '/' . $newFilename
            ];
        } else {
            $errors[] = "Erro ao mover arquivo: " . $files['name'][$i];
        }
    }
}

if (empty($uploaded) && !empty($errors)) {
    echo json_encode(['sucesso' => false, 'erro' => implode('. ', $errors)]);
    exit;
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => count($uploaded) . ' imagem(ns) enviada(s)',
    'uploaded' => $uploaded,
    'errors' => $errors
]);