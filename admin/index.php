<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getConnection();

$stmt = $db->query("SELECT COUNT(*) as total FROM enc_lojistas");
$totalLojistas = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM enc_encartes");
$totalEncartes = $stmt->fetch()['total'];

$stmt = $db->query("
    SELECT COALESCE(SUM(valor), 0) as total 
    FROM enc_pagamentos 
    WHERE status = 'aprovado' 
    AND MONTH(data_criacao) = MONTH(CURRENT_DATE())
    AND YEAR(data_criacao) = YEAR(CURRENT_DATE())
");
$receitaMes = $stmt->fetch()['total'];

$stmt = $db->query("
    SELECT p.id, p.nome as plano, l.nome_loja, l.subdominio, l.status_assinatura, l.data_criacao
    FROM enc_lojistas l
    JOIN enc_planos p ON l.plano_id = p.id
    ORDER BY l.data_criacao DESC
    LIMIT 10
");
$lojistasRecentes = $stmt->fetchAll();

$stmt = $db->query("
    SELECT COUNT(*) as ativo FROM enc_lojistas WHERE status_assinatura = 'ativa'
");
$lojistasAtivos = $stmt->fetch()['ativo'];

$stmt = $db->query("
    SELECT COUNT(*) as trial FROM enc_lojistas WHERE status_assinatura = 'trial'
");
$lojistasTrial = $stmt->fetch()['trial'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #1a1a2e; min-height: 100vh; }
        .sidebar {
            background: #16213e;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(233, 69, 96, 0.2);
            color: #e94560;
        }
        .stat-card {
            background: #16213e;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
        }
        .table { color: white; }
        .table thead { background: #0f3460; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-4 text-center border-bottom border-secondary">
                    <h5 class="text-danger fw-bold mb-0">
                        <i class="bi bi-shield-lock"></i> Encartes Pro
                    </h5>
                    <small class="text-white-50">Painel Admin</small>
                </div>
                <nav class="nav flex-column py-3">
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="lojistas/index.php" class="nav-link">
                        <i class="bi bi-shop me-2"></i> Lojistas
                    </a>
                    <a href="planos/index.php" class="nav-link">
                        <i class="bi bi-box-seam me-2"></i> Planos
                    </a>
                    <a href="templates/index.php" class="nav-link">
                        <i class="bi bi-layers me-2"></i> Templates
                    </a>
                    <hr class="border-secondary mx-3">
                    <a href="/encartes/lojista/login.php" class="nav-link" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Ver Vitrine
                    </a>
                    <a href="login.php?logout=1" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                    </a>
                </nav>
            </div>

            <div class="col-md-10 ms-auto p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white fw-bold mb-0">Dashboard</h4>
                    <div class="text-white-50">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin_nome']) ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-white-50">Total Lojistas</small>
                                    <h2 class="text-white mb-0"><?= $totalLojistas ?></h2>
                                </div>
                                <div class="fs-1 text-danger opacity-50">
                                    <i class="bi bi-shop"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-white-50">Encartes Criados</small>
                                    <h2 class="text-white mb-0"><?= $totalEncartes ?></h2>
                                </div>
                                <div class="fs-1 text-primary opacity-50">
                                    <i class="bi bi-file-earmark-post"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-white-50">Receita do Mês</small>
                                    <h2 class="text-success mb-0">R$ <?= number_format($receitaMes, 2, ',', '.') ?></h2>
                                </div>
                                <div class="fs-1 text-success opacity-50">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-white-50">Lojas Ativas</small>
                                    <h2 class="text-white mb-0"><?= $lojistasAtivos ?></h2>
                                    <small class="text-warning"><?= $lojistasTrial ?> em trial</small>
                                </div>
                                <div class="fs-1 text-warning opacity-50">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="stat-card p-3">
                            <h6 class="text-white mb-3">
                                <i class="bi bi-clock-history me-2"></i> Lojistas Recentes
                            </h6>
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Loja</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lojistasRecentes as $lojista): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($lojista['nome_loja']) ?></strong>
                                                <br><small class="text-white-50"><?= htmlspecialchars($lojista['subdominio']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($lojista['plano']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $lojista['status_assinatura'] === 'ativa' ? 'success' : ($lojista['status_assinatura'] === 'trial' ? 'warning' : 'danger') ?>">
                                                    <?= $lojista['status_assinatura'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($lojista['data_criacao'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($lojistasRecentes)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-white-50 py-4">
                                                Nenhum lojista cadastrado ainda
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>