<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$db = Database::getConnection();

$stmt = $db->prepare("
    SELECT dispositivo, COUNT(*) as total 
    FROM enc_visualizacoes_encarte 
    WHERE lojista_id = ? 
    AND MONTH(data_hora) = MONTH(CURRENT_DATE())
    GROUP BY dispositivo
");
$stmt->execute([$lojistaId]);
$dispositivos = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT cidade, COUNT(*) as total 
    FROM enc_visualizacoes_encarte 
    WHERE lojista_id = ? AND cidade IS NOT NULL
    AND MONTH(data_hora) = MONTH(CURRENT_DATE())
    GROUP BY cidade
    ORDER BY total DESC
    LIMIT 10
");
$stmt->execute([$lojistaId]);
$cidades = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT e.titulo, SUM(v.views) as total_views
    FROM enc_encartes e
    LEFT JOIN (
        SELECT encarte_id, COUNT(*) as views 
        FROM enc_visualizacoes_encarte 
        WHERE lojista_id = ? AND MONTH(data_hora) = MONTH(CURRENT_DATE())
        GROUP BY encarte_id
    ) v ON e.id = v.encarte_id
    WHERE e.lojista_id = ?
    GROUP BY e.id, e.titulo
    ORDER BY total_views DESC
    LIMIT 10
");
$stmt->execute([$lojistaId, $lojistaId]);
$encartesViews = $stmt->fetchAll();

$stmt = $db->query("SELECT COUNT(*) as total FROM enc_visualizacoes_encarte WHERE lojista_id = $lojistaId AND MONTH(data_hora) = MONTH(CURRENT_DATE())");
$totalViews = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .nav-link { color: #495057; padding: 12px 20px; border-radius: 8px; margin: 2px 0; }
        .nav-link:hover, .nav-link.active { background: #eff6ff; color: #2563eb; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 border-bottom">
                    <h5 class="text-primary fw-bold mb-0"><i class="bi bi-collection"></i> Encartes Pro</h5>
                </div>
                <nav class="nav flex-column p-2">
                    <a href="../index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="../index.php" class="nav-link"><i class="bi bi-file-earmark-post me-2"></i> Encartes</a>
                    <a href="../mapa/index.php" class="nav-link"><i class="bi bi-geo-alt me-2"></i> Mapa</a>
                    <a href="index.php" class="nav-link active"><i class="bi bi-graph-up me-2"></i> Estatísticas</a>
                    <a href="../notificacoes/index.php" class="nav-link"><i class="bi bi-bell me-2"></i> Notificações</a>
                    <a href="../galeria/index.php" class="nav-link"><i class="bi bi-images me-2"></i> Galeria</a>
                </nav>
            </div>
            <div class="col-md-10 p-4">
                <h4 class="fw-bold mb-4">Estatísticas do Mês</h4>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5>Total de Visualizações: <strong><?= number_format($totalViews) ?></strong></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Por Dispositivo</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDispositivos"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Top Cidades</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartCidades"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Visualizações por Encarte</h6>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Encarte</th>
                                    <th>Visualizações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($encartesViews as $encarte): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($encarte['titulo']) ?></td>
                                        <td><?= number_format($encarte['total_views'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        new Chart(document.getElementById('chartDispositivos'), {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [
                        <?= array_sum(array_map(fn($d) => $d['dispositivo'] === 'desktop' ? $d['total'] : 0, $dispositivos)) ?>,
                        <?= array_sum(array_map(fn($d) => $d['dispositivo'] === 'mobile' ? $d['total'] : 0, $dispositivos)) ?>,
                        <?= array_sum(array_map(fn($d) => $d['dispositivo'] === 'tablet' ? $d['total'] : 0, $dispositivos)) ?>
                    ],
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b']
                }]
            }
        });

        new Chart(document.getElementById('chartCidades'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($cidades, 'cidade')) ?>,
                datasets: [{
                    label: 'Visualizações',
                    data: <?= json_encode(array_column($cidades, 'total')) ?>,
                    backgroundColor: '#2563eb'
                }]
            },
            options: {
                indexAxis: 'y'
            }
        });
    </script>
</body>
</html>