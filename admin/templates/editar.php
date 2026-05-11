<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM enc_templates_encarte WHERE id = ?");
$stmt->execute([$id]);
$template = $stmt->fetch();

if (!$template) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $estruturaHtml = $_POST['estrutura_html'] ?? '';
    $estruturaCss = $_POST['estrutura_css'] ?? '';
    $configBlocos = $_POST['config_blocos'] ?? '{}';

    if (empty($nome) || empty($estruturaHtml)) {
        $erro = 'Preencha o nome e a estrutura HTML do template';
    } else {
        $stmt = $db->prepare("
            UPDATE enc_templates_encarte SET nome = ?, descricao = ?, categoria = ?, 
                estrutura_html = ?, estrutura_css = ?, configuracao_blocos = ?
            WHERE id = ?
        ");
        $stmt->execute([$nome, $descricao, $categoria, $estruturaHtml, $estruturaCss, $configBlocos, $id]);
        
        $sucesso = 'Template atualizado com sucesso!';
        
        $stmt = $db->prepare("SELECT * FROM enc_templates_encarte WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Template - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #1a1a2e; min-height: 100vh; padding: 40px; }
        .card-form { background: #16213e; border-radius: 12px; padding: 30px; border: 1px solid rgba(255,255,255,0.1); }
        .nav-tabs .nav-link { color: #adb5bd; border: none; }
        .nav-tabs .nav-link.active { color: white; border-bottom: 2px solid #e94560; background: transparent; }
        textarea.form-control { font-family: monospace; min-height: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card-form">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-white mb-0">Editar Template</h4>
                        <a href="index.php" class="btn btn-secondary btn-sm">Voltar</a>
                    </div>
                    
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nome *</label>
                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($template['nome']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Categoria</label>
                                <select name="categoria" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="supermercado" <?= $template['categoria'] == 'supermercado' ? 'selected' : '' ?>>Supermercado</option>
                                    <option value="farmacia" <?= $template['categoria'] == 'farmacia' ? 'selected' : '' ?>>Farmácia</option>
                                    <option value="moda" <?= $template['categoria'] == 'moda' ? 'selected' : '' ?>>Moda</option>
                                    <option value="eletronicos" <?= $template['categoria'] == 'eletronicos' ? 'selected' : '' ?>>Eletrônicos</option>
                                    <option value="restaurante" <?= $template['categoria'] == 'restaurante' ? 'selected' : '' ?>>Restaurante</option>
                                    <option value="outros" <?= $template['categoria'] == 'outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Descrição</label>
                            <input type="text" name="descricao" class="form-control" value="<?= htmlspecialchars($template['descricao'] ?? '') ?>">
                        </div>

                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-html">HTML</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-css">CSS</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-blocos">Blocos</button></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-html">
                                <div class="mb-3">
                                    <label class="form-label text-white">Estrutura HTML *</label>
                                    <textarea name="estrutura_html" class="form-control"><?= htmlspecialchars($template['estrutura_html']) ?></textarea>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-css">
                                <div class="mb-3">
                                    <label class="form-label text-white">CSS</label>
                                    <textarea name="estrutura_css" class="form-control"><?= htmlspecialchars($template['estrutura_css'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-blocos">
                                <div class="mb-3">
                                    <label class="form-label text-white">Configuração de Blocos (JSON)</label>
                                    <textarea name="config_blocos" class="form-control"><?= htmlspecialchars($template['configuracao_blocos'] ?? '{"blocos":[]}') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>