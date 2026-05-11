<?php
session_start();

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

$lojistaId = $_SESSION['lojista_id'];

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT plano_id FROM enc_lojistas WHERE id = ?");
    $stmt->execute([$lojistaId]);
    $lojista = $stmt->fetch();
    
    if ($lojista) {
        $stmt2 = $db->prepare("SELECT limite_imagens_por_galeria FROM enc_planos WHERE id = ?");
        $stmt2->execute([$lojista['plano_id']]);
        $plano = $stmt2->fetch();
        $limiteImagens = $plano['limite_imagens_por_galeria'] ?? 10;
    } else {
        $limiteImagens = 10;
    }
} catch (Exception $e) {
    $limiteImagens = 10;
}

$uploadPath = dirname(__DIR__, 2) . '/assets/uploads/lojista_' . $lojistaId;
$uploadUrl = '/assets/uploads/lojista_' . $lojistaId;

if (!is_dir($uploadPath)) {
    @mkdir($uploadPath, 0755, true);
}

$imagens = [];
if (is_dir($uploadPath)) {
    $files = @scandir($uploadPath);
    if ($files) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && !is_dir($uploadPath . '/' . $file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $imagens[] = [
                        'nome' => $file,
                        'url' => $uploadUrl . '/' . $file
                    ];
                }
            }
        }
    }
}

$qtdImagens = count($imagens);
$podeAdicionar = ($limiteImagens === -1) || ($qtdImagens < $limiteImagens);
$restantes = ($limiteImagens === -1) ? 'ilimitado' : ($limiteImagens - $qtdImagens);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria - Encartes Fáceis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        .upload-area:hover { border-color: #2563eb; background: #f0f4ff; }
        .galeria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            padding: 20px 0;
        }
        .galeria-item {
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .galeria-item img { width: 100%; height: 120px; object-fit: cover; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/lojista/">
                <i class="bi bi-images me-2"></i>Encartes Fáceis
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <h4><i class="bi bi-images me-2"></i>Galeria de Imagens</h4>
        
        <p class="text-muted">
            <?php if ($limiteImagens !== -1): ?>
                Limite: <?= $qtdImagens ?> / <?= $limiteImagens ?> (<?= $restantes ?> restantes)
            <?php else: ?>
                Limite: Ilimitado
            <?php endif; ?>
        </p>

        <div class="upload-area" id="uploadArea">
            <i class="bi bi-cloud-upload fs-1 text-muted"></i>
            <h5 class="mt-3">Clique para selecionar imagens</h5>
            <p class="text-muted">JPG, PNG, GIF, WebP (máx 5MB)</p>
            <input type="file" id="fileInput" accept="image/*" multiple style="display: none;">
            <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                Selecionar
            </button>
        </div>

        <div id="mensagem" class="mt-3"></div>

        <div class="galeria-grid mt-4">
            <?php if (empty($imagens)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-images fs-1"></i>
                    <p>Nenhuma imagem</p>
                </div>
            <?php else: ?>
                <?php foreach ($imagens as $img): ?>
                    <div class="galeria-item">
                        <img src="<?= $img['url'] ?>" alt="<?= $img['nome'] ?>">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (this.files.length === 0) return;
            
            var formData = new FormData();
            for (var i = 0; i < this.files.length; i++) {
                formData.append('imagens[]', this.files[i]);
            }
            
            document.getElementById('mensagem').innerHTML = '<div class="alert alert-info">Enviando...</div>';
            
            fetch('/api/upload-galeria.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    location.reload();
                } else {
                    document.getElementById('mensagem').innerHTML = '<div class="alert alert-danger">' + (data.erro || 'Erro') + '</div>';
                }
            })
            .catch(err => {
                document.getElementById('mensagem').innerHTML = '<div class="alert alert-danger">Erro ao enviar</div>';
            });
        });
    </script>
</body>
</html>