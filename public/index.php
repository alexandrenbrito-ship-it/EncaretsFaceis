<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$lojista = null;
$encartes = [];
$erro = null;

$host = $_SERVER['HTTP_HOST'] ?? '';
$subdominio = null;

if (isset($_GET['s'])) {
    $subdominio = $_GET['s'];
} else {
    $parts = explode('.', $host);
    if (count($parts) > 2) {
        $subdominio = $parts[0];
    }
}

if ($subdominio) {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT l.*, p.nome as plano_nome FROM enc_lojistas l JOIN enc_planos p ON l.plano_id = p.id WHERE l.subdominio = ? AND l.status_assinatura IN ('ativa', 'trial')");
        $stmt->execute([$subdominio]);
        $lojista = $stmt->fetch();

        if ($lojista) {
            $stmt = $db->prepare("
                SELECT * FROM enc_encartes 
                WHERE lojista_id = ? AND publicado = 1 
                AND (data_expiracao IS NULL OR data_expiracao > NOW())
                ORDER BY data_publicacao DESC
            ");
            $stmt->execute([$lojista['id']]);
            $encartes = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        $erro = 'Erro ao carregar dados';
    }
}

$configPwa = json_decode($lojista['config_pwa'] ?? '{"cor_primaria":"#2563eb","cor_secundaria":"#1d4ed8"}', true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lojista ? htmlspecialchars($lojista['nome_loja']) : 'Loja' ?> - Encartes</title>
    <meta name="theme-color" content="<?= $configPwa['cor_primaria'] ?? '#2563eb' ?>">
    <link rel="manifest" href="/public/manifest.json.php?s=<?= $subdominio ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: <?= $configPwa['cor_primaria'] ?? '#2563eb' ?>;
            --secondary: <?= $configPwa['cor_secundaria'] ?? '#1d4ed8' ?>;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
        }
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 60px 0;
        }
        .encarte-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .encarte-card:hover {
            transform: translateY(-5px);
        }
        .encarte-img {
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }
    </style>
</head>
<body>
    <?php if (!$lojista): ?>
        <div class="container py-5 text-center">
            <i class="bi bi-shop fs-1 text-muted"></i>
            <h3 class="mt-3">Loja não encontrada</h3>
            <p class="text-muted">Esta loja não existe ou está inativa.</p>
            <a href="/" class="btn btn-primary">Voltar para início</a>
        </div>
    <?php else: ?>
        <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary)">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">
                    <?= htmlspecialchars($lojista['nome_loja']) ?>
                </a>
                <button class="btn btn-light btn-sm" onclick="subscribePush()">
                    <i class="bi bi-bell"></i> Ativar Notificações
                </button>
            </div>
        </nav>

        <section class="hero">
            <div class="container text-center">
                <?php if (!empty($lojista['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($lojista['logo_url']) ?>" class="mb-3" style="max-height: 80px;">
                <?php endif; ?>
                <h1 class="fw-bold"><?= htmlspecialchars($lojista['nome_loja']) ?></h1>
                <p>Veja nossas ofertas e promoções</p>
            </div>
        </section>

        <div class="container py-4">
            <?php if (empty($encartes)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-post text-muted fs-1"></i>
                    <h5 class="mt-3 text-muted">Nenhum encarte disponível</h5>
                </div>
            <?php else: ?>
                <h4 class="mb-4">
                    <i class="bi bi-collection"></i> Nossos Encartes
                </h4>
                <div class="row">
                    <?php foreach ($encartes as $encarte): 
                        $dados = json_decode($encarte['dados_completos'], true);
                        $imagem = $dados['cabecalho']['imagem'] ?? '';
                    ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card encarte-card h-100" onclick="window.location.href='encarte.php?id=<?= $encarte['id'] ?>&lojista=<?= $subdominio ?>'">
                                <?php if ($imagem): ?>
                                    <img src="<?= htmlspecialchars($imagem) ?>" class="encarte-img card-img-top">
                                <?php else: ?>
                                    <div class="encarte-img d-flex align-items-center justify-content-center">
                                        <i class="bi bi-image fs-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($encarte['titulo']) ?></h5>
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars($encarte['descricao'] ?? '') ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-eye"></i> <?= number_format($encarte['views']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($encarte['data_publicacao'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container text-center">
                <?php if (!empty($lojista['endereco'])): ?>
                    <p class="mb-1"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($lojista['endereco']) ?></p>
                <?php endif; ?>
                <?php if (!empty($lojista['telefone'])): ?>
                    <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($lojista['telefone']) ?></p>
                <?php endif; ?>
            </div>
        </footer>

        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/public/service-worker.js.php?s=<?= $subdominio ?>');
            }

            async function subscribePush() {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('<?= $configPwa['vapid_public'] ?? '' ?>')
                });

                await fetch('/api/push-subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        lojista_id: <?= $lojista['id'] ?>,
                        subscription: subscription
                    })
                });

                alert('Você receberá notificações desta loja!');
            }

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            navigator.geolocation?.getCurrentPosition(
                (pos) => {
                    fetch('/api/salvar-localizacao.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            lojista_id: <?= $lojista['id'] ?>,
                            lat: pos.coords.latitude,
                            lng: pos.coords.accuracy
                        })
                    });
                },
                () => {}
            );
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>