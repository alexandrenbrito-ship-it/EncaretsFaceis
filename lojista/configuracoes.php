<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Lojista.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$lojistaModel = new Lojista();
$lojista = $lojistaModel->find($lojistaId);

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getConnection();
    
    $campos = [];
    $params = [];
    
    if (isset($_POST['nome_loja'])) {
        $campos[] = 'nome_loja = ?';
        $params[] = $_POST['nome_loja'];
    }
    if (isset($_POST['telefone'])) {
        $campos[] = 'telefone = ?';
        $params[] = $_POST['telefone'];
    }
    if (isset($_POST['endereco'])) {
        $campos[] = 'endereco = ?';
        $params[] = $_POST['endereco'];
    }
    
    if (!empty($campos)) {
        $params[] = $lojistaId;
        $stmt = $db->prepare("UPDATE enc_lojistas SET " . implode(', ', $campos) . " WHERE id = ?");
        $stmt->execute($params);
        $sucesso = 'Dados atualizados com sucesso!';
        
        $lojista = $lojistaModel->find($lojistaId);
    }
}

if (isset($_POST['salvar_pwa'])) {
    $configPwa = [
        'cor_primaria' => $_POST['cor_primaria'] ?? '#2563eb',
        'cor_secundaria' => $_POST['cor_secundaria'] ?? '#1d4ed8',
        'nome' => $lojista['nome_loja']
    ];
    
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE enc_lojistas SET config_pwa = ? WHERE id = ?");
    $stmt->execute([json_encode($configPwa), $lojistaId]);
    $sucesso = 'Configurações do PWA salvas!';
    
    $lojista = $lojistaModel->find($lojistaId);
}

$configPwa = json_decode($lojista['config_pwa'] ?? '{"cor_primaria":"#2563eb","cor_secundaria":"#1d4ed8"}', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
                    <a href="index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="encartes/index.php" class="nav-link"><i class="bi bi-file-earmark-post me-2"></i> Encartes</a>
                    <a href="mapa/index.php" class="nav-link"><i class="bi bi-geo-alt me-2"></i> Mapa</a>
                    <a href="estatisticas/index.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Estatísticas</a>
                    <a href="notificacoes/index.php" class="nav-link"><i class="bi bi-bell me-2"></i> Notificações</a>
                    <a href="configuracoes.php" class="nav-link active"><i class="bi bi-gear me-2"></i> Configurações</a>
                </nav>
            </div>
            <div class="col-md-10 p-4">
                <h4 class="fw-bold mb-4">Configurações da Loja</h4>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Dados da Loja</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nome da Loja</label>
                                        <input type="text" name="nome_loja" class="form-control" 
                                               value="<?= htmlspecialchars($lojista['nome_loja']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" name="telefone" class="form-control" 
                                               value="<?= htmlspecialchars($lojista['telefone'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Endereço</label>
                                        <textarea name="endereco" class="form-control" rows="2"><?= htmlspecialchars($lojista['endereco'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar Dados</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Configurações do PWA</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="salvar_pwa" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Cor Primária</label>
                                        <div class="d-flex gap-2">
                                            <input type="color" name="cor_primaria" class="form-control" 
                                                   value="<?= $configPwa['cor_primaria'] ?? '#2563eb' ?>" style="width: 60px;">
                                            <input type="text" class="form-control" 
                                                   value="<?= $configPwa['cor_primaria'] ?? '#2563eb' ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cor Secundária</label>
                                        <div class="d-flex gap-2">
                                            <input type="color" name="cor_secundaria" class="form-control" 
                                                   value="<?= $configPwa['cor_secundaria'] ?? '#1d4ed8' ?>" style="width: 60px;">
                                            <input type="text" class="form-control" 
                                                   value="<?= $configPwa['cor_secundaria'] ?? '#1d4ed8' ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Salvar Cores</button>
                                </form>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Informações da Conta</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Plano:</strong> <?= htmlspecialchars($lojista['plano_nome'] ?? 'Plano') ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?= $lojista['status_assinatura'] === 'ativa' ? 'success' : 'warning' ?>">
                                        <?= $lojista['status_assinatura'] ?>
                                    </span>
                                </p>
                                <p><strong>Subdomínio:</strong> <a href="http://<?= $lojista['subdominio'] ?>" target="_blank"><?= $lojista['subdominio'] ?></a></p>
                                <p><strong>Validade:</strong> <?= $lojista['data_validade'] ? date('d/m/Y', strtotime($lojista['data_validade'])) : '-' ?></p>
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