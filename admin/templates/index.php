<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM enc_templates_encarte ORDER BY nome ASC");
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Templates - Admin</title>
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
                    <a href="../planos/index.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Planos</a>
                    <a href="index.php" class="nav-link active"><i class="bi bi-layers me-2"></i> Templates</a>
                </nav>
            </div>
            <div class="col-md-10 ms-auto p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white fw-bold mb-0">Templates de Encartes</h4>
                    <a href="criar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Template</a>
                </div>

                <?php if (empty($templates)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-layers text-white-50 fs-1"></i>
                        <h5 class="text-white-50 mt-3">Nenhum template criado</h5>
                        <a href="criar.php" class="btn btn-primary mt-2">Criar primeiro template</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($templates as $template): ?>
                            <div class="col-md-4 mb-4">
                                <div class="stat-card p-4 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="text-white fw-bold mb-0"><?= htmlspecialchars($template['nome']) ?></h5>
                                        <span class="badge bg-<?= $template['ativo'] ? 'success' : 'secondary' ?>">
                                            <?= $template['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </div>
                                    <p class="text-white-50 small"><?= htmlspecialchars($template['descricao'] ?? 'Sem descrição') ?></p>
                                    <div class="mb-3">
                                        <span class="badge bg-primary"><?= htmlspecialchars($template['categoria'] ?? 'Geral') ?></span>
                                        <span class="badge bg-info"><?= $template['uso_count'] ?> usos</span>
                                    </div>
                                    <hr class="border-secondary">
                                    <div class="d-flex gap-2">
                                        <a href="editar.php?id=<?= $template['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <button class="btn btn-sm btn-outline-<?= $template['ativo'] ? 'warning' : 'success' ?>" 
                                                onclick="toggleTemplate(<?= $template['id'] ?>, <?= $template['ativo'] ? '0' : '1' ?>)">
                                            <i class="bi bi-<?= $template['ativo'] ? 'pause' : 'play' ?>-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTemplate(id, ativo) {
            fetch('toggle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&ativo=' + ativo
            }).then(() => location.reload());
        }
    </script>
</body>
</html>