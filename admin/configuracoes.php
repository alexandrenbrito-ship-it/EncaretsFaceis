<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getConnection();
$erro = '';
$sucesso = '';

$stmt = $db->query("SELECT * FROM enc_configuracoes");
$configs = $stmt->fetchAll();
$configsArray = array_column($configs, 'valor', 'chave');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'sistema_nome' => $_POST['sistema_nome'] ?? '',
        'url_base' => $_POST['url_base'] ?? '',
        'mp_public_key' => $_POST['mp_public_key'] ?? '',
        'mp_access_token' => $_POST['mp_access_token'] ?? '',
        'mp_modo' => $_POST['mp_modo'] ?? 'sandbox',
        'geoip_api' => $_POST['geoip_api'] ?? ''
    ];

    foreach ($fields as $chave => $valor) {
        $stmt = $db->prepare("
            INSERT INTO enc_configuracoes (chave, valor) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        $stmt->execute([$chave, $valor]);
    }

    $sucesso = 'Configurações salvas com sucesso!';
    
    $stmt = $db->query("SELECT * FROM enc_configuracoes");
    $configs = $stmt->fetchAll();
    $configsArray = array_column($configs, 'valor', 'chave');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; min-height: 100vh; padding: 40px; }
        .card-form { background: #16213e; border-radius: 12px; padding: 30px; border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-form">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-white mb-0">Configurações do Sistema</h4>
                        <a href="index.php" class="btn btn-secondary btn-sm">Voltar</a>
                    </div>
                    
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <h6 class="text-white mb-3 mt-4">Geral</h6>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Nome do Sistema</label>
                            <input type="text" name="sistema_nome" class="form-control" 
                                   value="<?= htmlspecialchars($configsArray['sistema_nome'] ?? 'Encartes Pro') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">URL Base</label>
                            <input type="url" name="url_base" class="form-control" 
                                   value="<?= htmlspecialchars($configsArray['url_base'] ?? '') ?>" placeholder="https://...">
                        </div>

                        <hr class="border-secondary">
                        
                        <h6 class="text-white mb-3">Mercado Pago</h6>

                        <div class="mb-3">
                            <label class="form-label text-white">Public Key</label>
                            <input type="text" name="mp_public_key" class="form-control" 
                                   value="<?= htmlspecialchars($configsArray['mp_public_key'] ?? '') ?>" placeholder="APP_USR-...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Access Token</label>
                            <input type="text" name="mp_access_token" class="form-control" 
                                   value="<?= htmlspecialchars($configsArray['mp_access_token'] ?? '') ?>" placeholder="APP_USR-...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Modo</label>
                            <select name="mp_modo" class="form-select">
                                <option value="sandbox" <?= ($configsArray['mp_modo'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Teste)</option>
                                <option value="production" <?= ($configsArray['mp_modo'] ?? '') === 'production' ? 'selected' : '' ?>>Produção</option>
                            </select>
                        </div>

                        <hr class="border-secondary">
                        
                        <h6 class="text-white mb-3">APIs</h6>

                        <div class="mb-3">
                            <label class="form-label text-white">API de Geolocalização</label>
                            <input type="url" name="geoip_api" class="form-control" 
                                   value="<?= htmlspecialchars($configsArray['geoip_api'] ?? 'https://ip-api.com/json/') ?>">
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>