<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Lojista.php';
require_once __DIR__ . '/../../src/Middlewares/LimitCheck.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$lojistaModel = new Lojista();
$lojista = $lojistaModel->find($lojistaId);

$planoModel = new \Src\Models\Plano();
$plano = $planoModel->find($lojista['plano_id']);
$limiteImagens = $plano['limite_imagens_por_galeria'] ?? 10;

$uploadPath = __DIR__ . '/../../assets/uploads/lojista_' . $lojistaId;
$uploadUrl = UPLOAD_URL . 'lojista_' . $lojistaId;

if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0755, true);
}

$imagens = [];
if (is_dir($uploadPath)) {
    $files = scandir($uploadPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($uploadPath . '/' . $file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $imagens[] = [
                    'nome' => $file,
                    'url' => $uploadUrl . '/' . $file,
                    'caminho' => $uploadPath . '/' . $file,
                    'tamanho' => filesize($uploadPath . '/' . $file),
                    'data' => filemtime($uploadPath . '/' . $file)
                ];
            }
        }
    }
}

usort($imagens, function($a, $b) {
    return $b['data'] - $a['data'];
});

$qtdImagens = count($imagens);
$podeAdicionar = $limiteImagens === -1 || $qtdImagens < $limiteImagens;
$restantes = $limiteImagens === -1 ? 'ilimitado' : ($limiteImagens - $qtdImagens);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    
    if ($_POST['acao'] === 'excluir') {
        $arquivo = $_POST['arquivo'] ?? '';
        $caminhoCompleto = $uploadPath . '/' . $arquivo;
        
        if (file_exists($caminhoCompleto) && unlink($caminhoCompleto)) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Imagem excluída']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir']);
        }
        exit;
    }
}

function formatarBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de Imagens - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .galeria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .galeria-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .galeria-item:hover {
            transform: translateY(-3px);
        }
        .galeria-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .galeria-item .info {
            padding: 10px;
            font-size: 0.8rem;
            color: #6c757d;
        }
        .galeria-item .actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
        }
        .galeria-item:hover .actions {
            display: block;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        .upload-area:hover, .upload-area.dragover {
            border-color: #2563eb;
            background: #f0f4ff;
        }
        .limite-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .limite-ok { background: #d1fae5; color: #065f46; }
        .limite-alerta { background: #fef3c7; color: #92400e; }
        .limite-excedido { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/lojista/">
                <i class="bi bi-card-image me-2"></i>Encartes Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/lojista/">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/lojista/encartes/">Encartes</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/lojista/galeria/">Galeria</a></li>
                    <li class="nav-item"><a class="nav-link" href="/lojista/notificacoes/">Notificações</a></li>
                    <li class="nav-item"><a class="nav-link" href="/lojista/mapa/">Mapa</a></li>
                    <li class="nav-item"><a class="nav-link" href="/lojista/estatisticas/">Estatísticas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/lojista/configuracoes.php">Configurações</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="bi bi-images me-2"></i>Galeria de Imagens</h4>
            <?php if ($limiteImagens !== -1): ?>
                <span class="limite-badge <?= $podeAdicionar ? 'limite-ok' : 'limite-excedido' ?>">
                    <i class="bi bi-info-circle me-1"></i>
                    <?= $qtdImagens ?> / <?= $limiteImagens ?> imagens (<?= $restantes ?> restantes)
                </span>
            <?php else: ?>
                <span class="limite-badge limite-ok">
                    <i class="bi bi-infinity me-1"></i>Imagens ilimitadas
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$podeAdicionar): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Limite atingido!</strong> Seu plano permite até <?= $limiteImagens ?> imagens. 
                Entre em contato para fazer upgrade.
            </div>
        <?php endif; ?>

        <div class="upload-area mb-4" id="uploadArea">
            <i class="bi bi-cloud-upload fs-1 text-muted"></i>
            <h5 class="mt-3">Arraste imagens aqui ou clique para selecionar</h5>
            <p class="text-muted">Formatos: JPG, PNG, GIF, WebP (máx 5MB cada)</p>
            <input type="file" id="fileInput" accept="image/jpeg,image/png,image/gif,image/webp" multiple style="display: none;">
            <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-plus-lg me-1"></i>Selecionar Imagens
            </button>
        </div>

        <div id="uploadProgress" class="alert alert-info d-none">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2"></div>
                <span>Enviando imagens...</span>
            </div>
        </div>

        <div class="galeria-grid" id="galeriaGrid">
            <?php if (empty($imagens)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="bi bi-images fs-1"></i>
                    <p class="mt-2">Nenhuma imagem na galeria</p>
                </div>
            <?php else: ?>
                <?php foreach ($imagens as $img): ?>
                    <div class="galeria-item">
                        <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($img['nome']) ?>">
                        <div class="actions">
                            <button class="btn btn-sm btn-danger" onclick="copiarUrl('<?= htmlspecialchars($img['url']) ?>')" title="Copiar URL">
                                <i class="bi bi-clipboard"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="excluirImagem('<?= htmlspecialchars($img['nome']) ?>')" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="info">
                            <div class="text-truncate"><?= htmlspecialchars($img['nome']) ?></div>
                            <small><?= formatarBytes($img['tamanho']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;
            
            uploadProgress.classList.remove('d-none');
            
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('imagens[]', files[i]);
            }

            fetch('/api/upload-galeria.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                uploadProgress.classList.add('d-none');
                if (data.sucesso) {
                    location.reload();
                } else {
                    alert(data.erro || 'Erro ao fazer upload');
                }
            })
            .catch(err => {
                uploadProgress.classList.add('d-none');
                alert('Erro ao fazer upload');
            });
        }

        function copiarUrl(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copiada para a área de transferência!');
            });
        }

        function excluirImagem(nome) {
            if (confirm('Tem certeza que deseja excluir esta imagem?')) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'acao=excluir&arquivo=' + encodeURIComponent(nome)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.sucesso) {
                        location.reload();
                    } else {
                        alert(data.erro || 'Erro ao excluir');
                    }
                });
            }
        }
    </script>
</body>
</html>