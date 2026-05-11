<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Encarte.php';

header('Content-Type: application/json');

$lojistaId = (int)($_GET['lojista_id'] ?? 0);
$encarteId = (int)($_GET['id'] ?? 0);
$slug = $_GET['slug'] ?? '';

if (!$lojistaId && !isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Lojista não especificado']);
    exit;
}

$lojistaId = $lojistaId ?: $_SESSION['lojista_id'];

try {
    $encarteModel = new Encarte();

    if ($encarteId) {
        $encarte = $encarteModel->find($encarteId);
        
        if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
            echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
            exit;
        }

        echo json_encode([
            'sucesso' => true,
            'encarte' => [
                'id' => $encarte['id'],
                'titulo' => $encarte['titulo'],
                'slug' => $encarte['slug'],
                'descricao' => $encarte['descricao'],
                'dados_completos' => json_decode($encarte['dados_completos'], true),
                'publicado' => (bool) $encarte['publicado'],
                'views' => $encarte['views'],
                'data_criacao' => $encarte['data_criacao'],
                'data_publicacao' => $encarte['data_publicacao']
            ]
        ]);
    } 
    elseif ($slug) {
        $encarte = $encarteModel->getSlug($slug);
        
        if (!$encarte || $encarte['lojista_id'] != $lojistaId || !$encarte['publicado']) {
            echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado ou não publicado']);
            exit;
        }

        $encarteModel->incrementarViews($encarte['id']);

        echo json_encode([
            'sucesso' => true,
            'encarte' => [
                'id' => $encarte['id'],
                'titulo' => $encarte['titulo'],
                'slug' => $encarte['slug'],
                'dados_completos' => json_decode($encarte['dados_completos'], true),
                'views' => $encarte['views'] + 1
            ]
        ]);
    }
    else {
        $encartes = $encarteModel->findAll(['lojista_id' => $lojistaId], 'data_criacao DESC');
        
        $encartesFormatados = array_map(function($e) {
            return [
                'id' => $e['id'],
                'titulo' => $e['titulo'],
                'slug' => $e['slug'],
                'publicado' => (bool) $e['publicado'],
                'views' => $e['views'],
                'data_criacao' => $e['data_criacao']
            ];
        }, $encartes);

        echo json_encode([
            'sucesso' => true,
            'encartes' => $encartesFormatados,
            'total' => count($encartesFormatados)
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}