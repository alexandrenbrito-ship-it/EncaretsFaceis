<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Encarte.php';
require_once __DIR__ . '/../src/Models/Lojista.php';
require_once __DIR__ . '/../src/Models/Plano.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$lojistaModel = new Lojista();
$lojista = $lojistaModel->find($lojistaId);
$consumo = $lojistaModel->getConsumo($lojistaId);

$encarteModel = new Encarte();
$estatisticas = $encarteModel->getEstatisticas($lojistaId);
$encartesRecentes = $encarteModel->getByLojista($lojistaId);
$encartesPublicados = $encarteModel->getPublicados($lojistaId);

$planoModel = new Plano();
$plano = $planoModel->find($lojista['plano_id']);

$limiteEncartes = $plano['limite_encartes'] ?? 5;
$encartesUsados = $consumo['encartes_usados'] ?? 0;
$limitePush = $plano['limite_notificacoes_mes'] ?? 500;
$pushEnviados = $consumo['push_enviados_mes'] ?? 0;

function verificarLimite($usado, $limite) {
    if ($limite == -1) return ['usado' => $usado, 'limite' => 'Ilimitado', 'percentual' => 0];
    $percentual = min(100, ($usado / $limite) * 100);
    return ['usado' => $usado, 'limite' => $limite, 'percentual' => $percentual];
}

$usoEncartes = verificarLimite($encartesUsados, $limiteEncartes);
$usoPush = verificarLimite($pushEnviados, $limitePush);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .nav-link {
            color: #495057;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
        }
        .nav-link:hover, .nav-link.active {
            background: #eff6ff;
            color: #2563eb;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .progress { height: 8px; border-radius: 4px; }
        .usage-bar {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
        }
        .usage-bar-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 border-bottom">
                    <h5 class="text-primary fw-bold mb-0">
                        <i class="bi bi-collection"></i> Encartes Pro
                    </h5>
                </div>
                <nav class="nav flex-column p-2">
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="encartes/index.php" class="nav-link">
                        <i class="bi bi-file-earmark-post me-2"></i> Encartes
                    </a>
                    <a href="mapa/index.php" class="nav-link">
                        <i class="bi bi-geo-alt me-2"></i> Mapa de Clientes
                    </a>
                    <a href="estatisticas/index.php" class="nav-link">
                        <i class="bi bi-graph-up me-2"></i> Estatísticas
                    </a>
                    <a href="notificacoes/index.php" class="nav-link">
                        <i class="bi bi-bell me-2"></i> Notificações
                    </a>
                    <hr>
                    <a href="login.php?logout=1" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                    </a>
                </nav>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Olá, <?= htmlspecialchars($_SESSION['lojista_nome']) ?>!</h4>
                        <p class="text-muted mb-0">Bem-vindo ao seu painel</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?= $lojista['status_assinatura'] === 'ativa' ? 'success' : ($lojista['status_assinatura'] === 'trial' ? 'warning' : 'danger') ?>">
                            <?= strtoupper($plano['nome']) ?> - <?= $lojista['status_assinatura'] ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Encartes</h6>
                                        <h2 class="mb-0"><?= $estatisticas['total_encartes'] ?? 0 ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50"><i class="bi bi-file-earmark-post"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Publicados</h6>
                                        <h2 class="mb-0"><?= $estatisticas['publicados'] ?? 0 ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50"><i class="bi bi-check-circle"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Visualizações</h6>
                                        <h2 class="mb-0"><?= number_format($estatisticas['total_views'] ?? 0) ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50"><i class="bi bi-eye"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Inscritos PWA</h6>
                                        <h2 class="mb-0">-</h2>
                                    </div>
                                    <div class="fs-1 opacity-50"><i class="bi bi-people"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Uso de Encartes</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= $usoEncartes['usado'] ?> de <?= $usoEncartes['limite'] ?></span>
                                    <span class="text-muted"><?= number_format($usoEncartes['percentual'], 0) ?>%</span>
                                </div>
                                <div class="usage-bar">
                                    <div class="usage-bar-fill <?= $usoEncartes['percentual'] > 80 ? 'bg-danger' : 'bg-primary' ?>" 
                                         style="width: <?= $usoEncartes['percentual'] ?>%"></div>
                                </div>
                                <?php if ($usoEncartes['percentual'] > 80): ?>
                                    <div class="mt-2">
                                        <a href="#" class="text-danger small">Fazer upgrade do plano</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Notificações Push</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= $usoPush['usado'] ?> de <?= $usoPush['limite'] ?></span>
                                    <span class="text-muted"><?= number_format($usoPush['percentual'], 0) ?>%</span>
                                </div>
                                <div class="usage-bar">
                                    <div class="usage-bar-fill <?= $usoPush['percentual'] > 80 ? 'bg-danger' : 'bg-success' ?>" 
                                         style="width: <?= $usoPush['percentual'] ?>%"></div>
                                </div>
                                <?php if ($usoPush['percentual'] > 80): ?>
                                    <div class="mt-2">
                                        <a href="#" class="text-danger small">Fazer upgrade do plano</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Últimos Encartes</h6>
                                <a href="encartes/index.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($encartesRecentes)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-file-earmark-post text-muted fs-1"></i>
                                        <p class="text-muted mb-0">Nenhum encarte criado</p>
                                        <a href="encartes/novo.php" class="btn btn-primary btn-sm mt-2">Criar primeiro encarte</a>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($encartesRecentes, 0, 5) as $encarte): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($encarte['titulo']) ?></h6>
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($encarte['data_criacao'])) ?></small>
                                                </div>
                                                <span class="badge bg-<?= $encarte['publicado'] ? 'success' : 'secondary' ?>">
                                                    <?= $encarte['publicado'] ? 'Publicado' : 'Rascunho' ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Ações Rápidas</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="encartes/novo.php" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-2"></i> Novo Encarte
                                    </a>
                                    <a href="notificacoes/index.php" class="btn btn-outline-primary">
                                        <i class="bi bi-bell me-2"></i> Enviar Notificação
                                    </a>
                                    <a href="mapa/index.php" class="btn btn-outline-primary">
                                        <i class="bi bi-geo-alt me-2"></i> Ver Mapa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>