<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Encarte.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$encarteId = (int)($_POST['id'] ?? 0);

if (!$encarteId) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

$encarteModel = new Encarte();
$encarte = $encarteModel->find($encarteId);

if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
    exit;
}

$resultado = $encarteModel->publicar($encarteId);

if ($resultado) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Encarte publicado com sucesso!']);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao publicar encarte']);
}