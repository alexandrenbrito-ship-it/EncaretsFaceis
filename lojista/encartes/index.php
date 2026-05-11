<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Encarte.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$encarteModel = new Encarte();
$encartes = $encarteModel->getByLojista($lojistaId);
$estatisticas = $encarteModel->getEstatisticas($lojistaId);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Encartes - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-encarte {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 12px;
        }
        .card-encarte:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .status-ativo { background: #d4edda; color: #155724; }
        .status-inativo { background: #f8d7da; color: #721c24; }
        .stats-card {
            border-radius: 12px;
            border: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="/lojista/">
                <i class="bi bi-collection"></i> Encartes Pro
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted"><?= htmlspecialchars($_SESSION['lojista_nome']) ?></span>
                <a href="/lojista/login.php?logout=1" class="btn btn-outline-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Meus Encartes</h2>
            <a href="novo.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Novo Encarte
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">Total de Encartes</h6>
                                <h2 class="mb-0"><?= $estatisticas['total_encartes'] ?? 0 ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-file-earmark-post"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">Publicados</h6>
                                <h2 class="mb-0"><?= $estatisticas['publicados'] ?? 0 ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">Total de Visualizações</h6>
                                <h2 class="mb-0"><?= number_format($estatisticas['total_views'] ?? 0) ?></h2>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($encartes)): ?>
            <div class="text-center py-5">
                <div class="fs-1 text-muted mb-3">
                    <i class="bi bi-file-earmark-post"></i>
                </div>
                <h4 class="text-muted">Nenhum encarte criado ainda</h4>
                <p class="text-muted">Comece criando seu primeiro encarte digital</p>
                <a href="novo.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Criar Primeiro Encarte
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($encartes as $encarte): 
                    $dados = json_decode($encarte['dados_completos'], true);
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card card-encarte h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="status-badge <?= $encarte['publicado'] ? 'status-ativo' : 'status-inativo' ?>">
                                        <?= $encarte['publicado'] ? 'Publicado' : 'Rascunho' ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="bi bi-eye"></i> <?= number_format($encarte['views']) ?>
                                    </span>
                                </div>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($encarte['titulo']) ?></h5>
                                <p class="card-text text-muted small">
                                    <?= htmlspecialchars($encarte['descricao'] ?? 'Sem descrição') ?>
                                </p>
                                <?php if (!empty($dados['cabecalho']['imagem'])): ?>
                                    <img src="<?= htmlspecialchars($dados['cabecalho']['imagem']) ?>" 
                                         class="img-fluid rounded mb-3" style="max-height: 120px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar"></i> 
                                    Criado em <?= date('d/m/Y', strtotime($encarte['data_criacao'])) ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <div class="btn-group w-100">
                                    <a href="editor.php?id=<?= $encarte['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <?php if ($encarte['publicado']): ?>
                                        <a href="/public/?s=<?= $encarte['subdominio'] ?>&e=<?= $encarte['slug'] ?>" 
                                           target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> Ver
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="publicarEncarte(<?= $encarte['id'] ?>)">
                                            <i class="bi bi-upload"></i> Publicar
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="excluirEncarte(<?= $encarte['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function publicarEncarte(id) {
            if (confirm('Deseja publicar este encarte?')) {
                fetch('publicar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                }).then(() => location.reload());
            }
        }

        function excluirEncarte(id) {
            if (confirm('Tem certeza que deseja excluir este encarte? Esta ação não pode ser desfeita.')) {
                fetch('excluir.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>