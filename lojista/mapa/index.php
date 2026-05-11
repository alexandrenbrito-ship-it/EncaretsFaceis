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
    SELECT lc.*, cp.nome, cp.email, cp.ultimo_acesso
    FROM enc_localizacoes_clientes lc
    JOIN enc_clientes_pwa cp ON lc.cliente_pwa_id = cp.id
    WHERE lc.lojista_id = ?
    ORDER BY lc.ultima_atualizacao DESC
    LIMIT 100
");
$stmt->execute([$lojistaId]);
$localizacoes = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT DISTINCT cidade FROM enc_localizacoes_clientes 
    WHERE lojista_id = ? AND cidade IS NOT NULL
");
$stmt->execute([$lojistaId]);
$cidades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Clientes - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .nav-link { color: #495057; padding: 12px 20px; border-radius: 8px; margin: 2px 0; }
        .nav-link:hover, .nav-link.active { background: #eff6ff; color: #2563eb; }
        #map { height: 500px; border-radius: 12px; }
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
                    <a href="index.php" class="nav-link active"><i class="bi bi-geo-alt me-2"></i> Mapa</a>
                    <a href="../estatisticas/index.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Estatísticas</a>
                    <a href="../notificacoes/index.php" class="nav-link"><i class="bi bi-bell me-2"></i> Notificações</a>
                </nav>
            </div>
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Mapa de Clientes</h4>
                    <select class="form-select w-auto" id="filtro-cidade">
                        <option value="">Todas as cidades</option>
                        <?php foreach ($cidades as $c): ?>
                            <option value="<?= htmlspecialchars($c['cidade']) ?>"><?= htmlspecialchars($c['cidade']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Clientes Localizados (<?= count($localizacoes) ?>)</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Cidade</th>
                                    <th>Estado</th>
                                    <th>Último Acesso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($localizacoes as $loc): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($loc['cidade'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($loc['estado'] ?? '-') ?></td>
                                        <td><?= $loc['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($loc['ultimo_acesso'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($localizacoes)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Nenhum cliente localizou-se ainda
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const map = L.map('map').setView([-23.55, -46.63], 4);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const localizacoes = <?= json_encode($localizacoes) ?>;

        localizacoes.forEach(loc => {
            if (loc.latitude && loc.longitude) {
                L.marker([loc.latitude, loc.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${loc.cidade || 'Unknown'}</strong><br>
                        ${loc.estado || ''}<br>
                        <small>Último acesso: ${loc.ultimo_acesso || 'N/A'}</small>
                    `);
            }
        });

        if (localizacoes.length > 0) {
            const bounds = localizacoes
                .filter(l => l.latitude && l.longitude)
                .map(l => [l.latitude, l.longitude]);
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }
        }

        document.getElementById('filtro-cidade').addEventListener('change', function() {
            const cidade = this.value;
            const filtered = localizacoes.filter(l => !cidade || l.cidade === cidade);
            // Filrar marcadores no mapa
        });
    </script>
</body>
</html>