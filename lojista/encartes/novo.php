<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Encarte.php';
require_once __DIR__ . '/../../src/Models/Template.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$lojistaNome = $_SESSION['lojista_nome'];
$subdominio = $_SESSION['lojista_subdominio'];

$templateModel = new Template();
$templates = $templateModel->findAll(['ativo' => 1], 'nome ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $templateId = !empty($_POST['template_id']) ? (int) $_POST['template_id'] : null;

    if (empty($titulo)) {
        $erro = 'O título do encarte é obrigatório';
    } else {
        $encarteModel = new Encarte();
        
        $dadosIniciais = [
            'lojista_id' => $lojistaId,
            'template_id' => $templateId,
            'titulo' => $titulo,
            'slug' => $encarteModel->gerarSlug($titulo, $lojistaId),
            'descricao' => $descricao,
            'dados_completos' => json_encode([
                'cabecalho' => [
                    'titulo' => $titulo,
                    'subtitulo' => '',
                    'imagem' => '',
                    'cor_fundo' => '#2563eb',
                    'cor_texto' => '#ffffff'
                ],
                'produtos' => [],
                'galeria' => [],
                'rodape' => [
                    'texto' => '',
                    'telefone' => '',
                    'endereco' => '',
                    'imagem' => ''
                ],
                'configuracao' => [
                    'layout' => 'grid',
                    'colunas' => 3,
                    'mostrar_preco_original' => true,
                    'estilo_balao' => 'retangular'
                ]
            ]),
            'publicado' => 0,
            'destaque' => 0
        ];

        $encarteId = $encarteModel->criar($dadosIniciais);

        if ($templateId) {
            $templateModel->incrementarUso($templateId);
        }

        header('Location: editor.php?id=' . $encarteId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Encarte - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .template-card {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .template-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
        }
        .template-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .template-preview {
            height: 120px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px 8px 0 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="/lojista/">
                <i class="bi bi-collection"></i> Encartes Pro
            </a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-plus-circle text-primary"></i> Criar Novo Encarte
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Título do Encarte *</label>
                                <input type="text" name="titulo" class="form-control form-control-lg" 
                                       placeholder="Ex: Ofertas da Semana" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Descrição (opcional)</label>
                                <textarea name="descricao" class="form-control" rows="2" 
                                          placeholder="Breve descrição do encarte"></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Escolher Template (opcional)</label>
                                <p class="text-muted small">Comece do zero ou escolha um modelo base</p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="template-card card h-100" onclick="selectTemplate(null)">
                                            <div class="template-preview">
                                                <i class="bi bi-layers fs-1 text-secondary"></i>
                                            </div>
                                            <div class="card-body text-center">
                                                <h6 class="mb-0">Em Branco</h6>
                                                <small class="text-muted">Começar do zero</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($templates as $template): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="template-card card h-100" onclick="selectTemplate(<?= $template['id'] ?>)">
                                                <div class="template-preview">
                                                    <i class="bi bi-grid-3x3-gap fs-1 text-secondary"></i>
                                                </div>
                                                <div class="card-body text-center">
                                                    <h6 class="mb-0"><?= htmlspecialchars($template['nome']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($template['categoria'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="template_id" id="template_id" value="">
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Continuar para Editor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedTemplate = null;

        function selectTemplate(id) {
            document.querySelectorAll('.template-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            if (id === null) {
                document.querySelector('.template-card').classList.add('selected');
            } else {
                event.currentTarget.classList.add('selected');
            }
            
            document.getElementById('template_id').value = id || '';
        }
    </script>
</body>
</html>