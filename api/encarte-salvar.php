<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Encarte.php';

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$acao = $_POST['acao'] ?? '';

if ($acao === 'salvar_produto') {
    $encarteId = (int)($_POST['encarte_id'] ?? 0);
    
    if (!$encarteId) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID do encarte inválido']);
        exit;
    }
    
    $encarteModel = new Encarte();
    $encarte = $encarteModel->find($encarteId);
    
    if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
        echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
        exit;
    }
    
    $dados = json_decode($encarte['dados_completos'] ?? '{}', true) ?? [];
    if (!isset($dados['produtos'])) $dados['produtos'] = [];
    
    $nome = trim($_POST['nome'] ?? '');
    $preco_oferta = trim($_POST['preco_oferta'] ?? '');
    
    if (empty($nome) || empty($preco_oferta)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Nome e preço oferta são obrigatórios']);
        exit;
    }
    
    $produtos = $dados['produtos'];
    $produtos[] = [
        'id' => uniqid(),
        'nome' => $nome,
        'preco_original' => trim($_POST['preco_original'] ?? ''),
        'preco_oferta' => $preco_oferta,
        'imagem' => trim($_POST['imagem'] ?? ''),
        'balao' => [
            'cor' => $_POST['balao_cor'] ?? '#e94560',
            'formato' => $_POST['balao_formato'] ?? 'retangular',
            'texto' => $_POST['balao_texto'] ?? 'OFERTA'
        ]
    ];
    $dados['produtos'] = $produtos;
    
    $encarteModel->atualizar($encarteId, [
        'dados_completos' => json_encode($dados)
    ]);
    
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => 'Produto adicionado com sucesso!',
        'total_produtos' => count($produtos)
    ]);
    exit;
}

if ($acao === 'salvar') {
    $encarteId = (int)($_POST['encarte_id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $dadosJson = $_POST['dados'] ?? '{}';
    
    if (!$encarteId) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID do encarte inválido']);
        exit;
    }
    
    $encarteModel = new Encarte();
    $encarte = $encarteModel->find($encarteId);
    
    if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
        echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
        exit;
    }
    
    $encarteModel->atualizar($encarteId, [
        'titulo' => $titulo,
        'descricao' => $descricao,
        'dados_completos' => $dadosJson
    ]);
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Encarte salvo com sucesso!']);
    exit;
}

if ($acao === 'publicar') {
    $encarteId = (int)($_POST['encarte_id'] ?? 0);
    
    if (!$encarteId) {
        echo json_encode(['sucesso' => false, 'erro' => 'ID do encarte inválido']);
        exit;
    }
    
    $encarteModel = new Encarte();
    $encarte = $encarteModel->find($encarteId);
    
    if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
        echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
        exit;
    }
    
    $encarteModel->publicar($encarteId);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Encarte publicado!']);
    exit;
}

if ($acao === 'excluir_produto') {
    $encarteId = (int)($_POST['encarte_id'] ?? 0);
    $produtoId = $_POST['produto_id'] ?? '';
    
    if (!$encarteId || empty($produtoId)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
        exit;
    }
    
    $encarteModel = new Encarte();
    $encarte = $encarteModel->find($encarteId);
    
    if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
        echo json_encode(['sucesso' => false, 'erro' => 'Encarte não encontrado']);
        exit;
    }
    
    $dados = json_decode($encarte['dados_completos'] ?? '{}', true) ?? [];
    if (!isset($dados['produtos'])) $dados['produtos'] = [];
    
    $dados['produtos'] = array_filter($dados['produtos'], function($p) use ($produtoId) {
        return $p['id'] !== $produtoId;
    });
    
    $encarteModel->atualizar($encarteId, [
        'dados_completos' => json_encode($dados)
    ]);
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Produto excluído']);
    exit;
}

echo json_encode(['sucesso' => false, 'erro' => 'Ação não reconhecida']);