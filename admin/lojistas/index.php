<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->query("
    SELECT l.*, u.nome as usuario_nome, u.email, p.nome as plano_nome, p.limite_encartes, p.limite_notificacoes_mes
    FROM enc_lojistas l
    JOIN enc_usuarios u ON l.usuario_id = u.id
    JOIN enc_planos p ON l.plano_id = p.id
    ORDER BY l.data_criacao DESC
");
$lojistas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lojistas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #1a1a2e; min-height: 100vh; }
        .sidebar { background: #16213e; min-height: 100vh; position: fixed; width: 250px; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; border-radius: 8px; margin: 2px 10px; }
        .nav-link:hover, .nav-link.active { background: rgba(233, 69, 96, 0.2); color: #e94560; }
        .stat-card { background: #16213e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; }
        .progress { height: 8px; border-radius: 4px; }
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
                    <a href="index.php" class="nav-link active"><i class="bi bi-shop me-2"></i> Lojistas</a>
                    <a href="../planos/index.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Planos</a>
                    <a href="../templates/index.php" class="nav-link"><i class="bi bi-layers me-2"></i> Templates</a>
                </nav>
            </div>
            <div class="col-md-10 ms-auto p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white fw-bold mb-0">Lojistas</h4>
                    <a href="criar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Lojista</a>
                </div>

                <div class="card bg-transparent border-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Loja</th>
                                    <th>Plano</th>
                                    <th>Status</th>
                                    <th>Uso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lojistas as $lojista): 
                                    $recursos = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
                                    $encartesUsados = $recursos['encartes_usados'] ?? 0;
                                    $limiteEncartes = $lojista['limite_encartes'];
                                    $percentualEncartes = $limiteEncartes == -1 ? 0 : min(100, ($encartesUsados / $limiteEncartes) * 100);
                                    
                                    $pushUsados = $recursos['push_enviados_mes'] ?? 0;
                                    $limitePush = $lojista['limite_notificacoes_mes'];
                                    $percentualPush = min(100, ($pushUsados / $limitePush) * 100);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($lojista['nome_loja']) ?></strong>
                                            <br><small class="text-white-50"><?= htmlspecialchars($lojista['email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($lojista['plano_nome']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $lojista['status_assinatura'] === 'ativa' ? 'success' : ($lojista['status_assinatura'] === 'trial' ? 'warning' : 'danger') ?>">
                                                <?= $lojista['status_assinatura'] ?>
                                            </span>
                                        </td>
                                        <td style="min-width: 200px;">
                                            <small class="text-white-50">Encartes: <?= $encartesUsados ?>/<?= $limiteEncartes == -1 ? '∞' : $limiteEncartes ?></small>
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar <?= $percentualEncartes > 80 ? 'bg-danger' : 'bg-primary' ?>" 
                                                     style="width: <?= $percentualEncartes ?>%"></div>
                                            </div>
                                            <small class="text-white-50">Push: <?= $pushUsados ?>/<?= $limitePush ?></small>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar <?= $percentualPush > 80 ? 'bg-danger' : 'bg-success' ?>" 
                                                     style="width: <?= $percentualPush ?>%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="editar.php?id=<?= $lojista['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
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
</body>
</html>