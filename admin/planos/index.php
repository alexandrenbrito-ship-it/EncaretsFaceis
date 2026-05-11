<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM enc_planos ORDER BY ordem_exibicao ASC");
$planos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #1a1a2e; min-height: 100vh; }
        .sidebar { background: #16213e; min-height: 100vh; position: fixed; width: 250px; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 2px 10px; }
        .nav-link:hover, .nav-link.active { background: rgba(233, 69, 96, 0.2); color: #e94560; }
        .stat-card { background: #16213e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="p-4 text-center border-bottom border-secondary">
                    <h5 class="text-danger fw-bold mb-0"><i class="bi bi-shield-lock"></i> Encartes Pro</h5>
                </div>
                <nav class="nav flex-column py-3">
                    <a href="../index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="../lojistas/index.php" class="nav-link"><i class="bi bi-shop me-2"></i> Lojistas</a>
                    <a href="index.php" class="nav-link active"><i class="bi bi-box-seam me-2"></i> Planos</a>
                    <a href="../templates/index.php" class="nav-link"><i class="bi bi-layers me-2"></i> Templates</a>
                </nav>
            </div>
            <div class="col-md-10 ms-auto p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white fw-bold mb-0">Planos de Assinatura</h4>
                    <a href="criar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Plano</a>
                </div>

                <div class="row">
                    <?php foreach ($planos as $plano): ?>
                        <div class="col-md-4 mb-4">
                            <div class="stat-card p-4 h-100">
                                <?php if ($plano['destaque']): ?>
                                    <span class="badge bg-danger mb-2">Destaque</span>
                                <?php endif; ?>
                                <h5 class="text-white fw-bold"><?= htmlspecialchars($plano['nome']) ?></h5>
                                <p class="text-white-50"><?= htmlspecialchars($plano['descricao']) ?></p>
                                <h3 class="text-primary">
                                    R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?>
                                    <small class="text-white-50 fs-6">/mês</small>
                                </h3>
                                <hr class="border-secondary">
                                <ul class="list-unstyled text-white-50 small">
                                    <li class="mb-2">
                                        <i class="bi bi-<?= $plano['limite_encartes'] == -1 ? 'infinity' : 'hash' ?>"></i>
                                        Encartes: <?= $plano['limite_encartes'] == -1 ? 'Ilimitado' : $plano['limite_encartes'] ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-bell"></i>
                                        Notificações: <?= number_format($plano['limite_notificacoes_mes']) ?>/mês
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-<?= $plano['permite_mapa'] ? 'check' : 'x' ?>-circle"></i>
                                        Mapa de clientes
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-<?= $plano['permite_estatisticas_avancadas'] ? 'check' : 'x' ?>-circle"></i>
                                        Estatísticas avançadas
                                    </li>
                                    <li>
                                        <i class="bi bi-<?= $plano['permite_exportacao'] ? 'check' : 'x' ?>-circle"></i>
                                        Exportação
                                    </li>
                                </ul>
                                <div class="d-flex gap-2 mt-3">
                                    <a href="editar.php?id=<?= $plano['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>