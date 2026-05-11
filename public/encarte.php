<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$encarte = null;
$lojista = null;
$erro = null;

$encarteId = (int)($_GET['id'] ?? 0);
$subdominio = $_GET['lojista'] ?? '';

if ($encarteId && $subdominio) {
    try {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT l.* FROM enc_lojistas l WHERE l.subdominio = ?");
        $stmt->execute([$subdominio]);
        $lojista = $stmt->fetch();

        if ($lojista) {
            $stmt = $db->prepare("SELECT * FROM enc_encartes WHERE id = ? AND lojista_id = ? AND publicado = 1");
            $stmt->execute([$encarteId, $lojista['id']]);
            $encarte = $stmt->fetch();
        }
    } catch (Exception $e) {
        $erro = 'Erro ao carregar encarte';
    }
}

if (!$encarte) {
    header('Location: /encartes/public/');
    exit;
}

$dados = json_decode($encarte['dados_completos'], true);
$configPwa = json_decode($lojista['config_pwa'] ?? '{"cor_primaria":"#2563eb"}', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($encarte['titulo']) ?> - <?= htmlspecialchars($lojista['nome_loja']) ?></title>
    <meta name="theme-color" content="<?= $configPwa['cor_primaria'] ?? '#2563eb' ?>">
    <link rel="manifest" href="/encartes/public/manifest.json.php?s=<?= $subdominio ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: <?= $configPwa['cor_primaria'] ?? '#2563eb' ?>;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            padding-bottom: 80px;
        }
        .encarte-header {
            background: linear-gradient(135deg, var(--primary) 0%, <?= $configPwa['cor_secundaria'] ?? '#1d4ed8' ?> 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .produto-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .produto-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            background: #e9ecef;
        }
        .balao-oferta {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .balao-circular {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .balao-badge {
            border-radius: 20px;
        }
        .preco {
            color: #e94560;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .preco-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .rodape {
            background: #333;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .floating-share {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark" style="background: var(--primary)">
        <div class="container">
            <a class="navbar-brand" href="/encartes/public/?s=<?= $subdominio ?>">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <span class="text-white"><?= htmlspecialchars($lojista['nome_loja']) ?></span>
        </div>
    </nav>

    <header class="encarte-header">
        <h1 class="fw-bold"><?= htmlspecialchars($dados['cabecalho']['titulo'] ?? $encarte['titulo']) ?></h1>
        <?php if (!empty($dados['cabecalho']['subtitulo'])): ?>
            <p class="mb-0"><?= htmlspecialchars($dados['cabecalho']['subtitulo']) ?></p>
        <?php endif; ?>
    </header>

    <div class="container py-4">
        <?php if (!empty($dados['produtos'])): ?>
            <div class="row">
                <?php foreach ($dados['produtos'] as $produto): 
                    $balaoCor = $produto['balao']['cor'] ?? '#e94560';
                    $balaoFormato = $produto['balao']['formato'] ?? 'retangular';
                    $balaoTexto = $produto['balao']['texto'] ?? '';
                    
                    $formatoClass = '';
                    switch ($balaoFormato) {
                        case 'circular': $formatoClass = 'balao-circular'; break;
                        case 'badge': $formatoClass = 'balao-badge'; break;
                    }
                ?>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="produto-card">
                            <?php if ($balaoTexto): ?>
                                <span class="balao-oferta <?= $formatoClass ?>" style="background: <?= $balaoCor ?>">
                                    <?= htmlspecialchars($balaoTexto) ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produto['imagem'])): ?>
                                <img src="<?= htmlspecialchars($produto['imagem']) ?>" class="produto-img">
                            <?php else: ?>
                                <div class="produto-img d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted fs-1"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h6 class="mt-2 mb-1"><?= htmlspecialchars($produto['nome']) ?></h6>
                            
                            <?php if (!empty($produto['preco_original'])): ?>
                                <span class="preco-original">R$ <?= number_format((float)$produto['preco_original'], 2, ',', '.') ?></span>
                            <?php endif; ?>
                            
                            <div class="preco">R$ <?= number_format((float)($produto['preco_oferta'] ?? 0), 2, ',', '.') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-box-seam fs-1"></i>
                <p>Nenhum produto neste encarte</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($dados['galeria'])): ?>
        <div class="container py-3">
            <h5 class="mb-3">Galeria</h5>
            <div class="d-flex gap-2 overflow-auto pb-2">
                <?php foreach ($dados['galeria'] as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" style="height: 120px; border-radius: 8px;">
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <footer class="rodape">
        <?php if (!empty($dados['rodape']['texto'])): ?>
            <p class="mb-2"><?= htmlspecialchars($dados['rodape']['texto']) ?></p>
        <?php endif; ?>
        <?php if (!empty($dados['rodape']['telefone'])): ?>
            <p class="mb-1"><i class="bi bi-telephone"></i> <?= htmlspecialchars($dados['rodape']['telefone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($dados['rodape']['endereco'])): ?>
            <p class="mb-0"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($dados['rodape']['endereco']) ?></p>
        <?php endif; ?>
    </footer>

    <div class="floating-share">
        <button class="btn btn-primary rounded-circle p-3" onclick="shareEncarte()">
            <i class="bi bi-share"></i>
        </button>
    </div>

    <script>
        fetch('/api/registrar-visualizacao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'encarte_id=<?= $encarte['id'] ?>&lojista_id=<?= $lojista['id'] ?>'
        });

        function shareEncarte() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($encarte['titulo']) ?>',
                    text: 'Veja as ofertas da loja <?= htmlspecialchars($lojista['nome_loja']) ?>',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copiado!');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>