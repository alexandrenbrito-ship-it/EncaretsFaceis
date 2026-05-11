<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
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
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO enc_templates_encarte (nome, descricao, categoria, estrutura_html, estrutura_css, configuracao_blocos)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $descricao, $categoria, $estruturaHtml, $estruturaCss, $configBlocos]);
        
        $sucesso = 'Template criado com sucesso!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Template - Admin</title>
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
                        <h4 class="text-white mb-0">Novo Template</h4>
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
                                <input type="text" name="nome" class="form-control" required placeholder="Ex: Supermercado Moderno">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Categoria</label>
                                <select name="categoria" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="supermercado">Supermercado</option>
                                    <option value="farmacia">Farmácia</option>
                                    <option value="moda">Moda</option>
                                    <option value="eletronicos">Eletrônicos</option>
                                    <option value="restaurante">Restaurante</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Descrição</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Breve descrição do template">
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
                                    <textarea name="estrutura_html" class="form-control" placeholder="<div class='encarte'>{{content}}</div>"></textarea>
                                    <small class="text-white-50">Use {{variavel}} para placeholders</small>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-css">
                                <div class="mb-3">
                                    <label class="form-label text-white">CSS</label>
                                    <textarea name="estrutura_css" class="form-control" placeholder=".encarte { width: 100%; }"></textarea>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-blocos">
                                <div class="mb-3">
                                    <label class="form-label text-white">Configuração de Blocos (JSON)</label>
                                    <textarea name="config_blocos" class="form-control">{"blocos":["cabecalho","produtos","galeria","rodape"],"permite_adicionar_produtos":true,"max_produtos_por_linha":4}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Criar Template</button>
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