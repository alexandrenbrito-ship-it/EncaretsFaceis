<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /encartes/lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$db = Database::getConnection();

$stmt = $db->prepare("SELECT plano_id FROM enc_lojistas WHERE id = ?");
$stmt->execute([$lojistaId]);
$lojista = $stmt->fetch();

$stmt = $db->prepare("SELECT limite_notificacoes_mes FROM enc_planos WHERE id = ?");
$stmt->execute([$lojista['plano_id']]);
$limitePush = $stmt->fetch()['limite_notificacoes_mes'];

$stmt = $db->query("
    SELECT COUNT(*) as total FROM enc_notificacoes_push 
    WHERE lojista_id = $lojistaId 
    AND MONTH(data_envio) = MONTH(CURRENT_DATE())
");
$pushEnviados = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT COUNT(*) as total, cidade 
    FROM enc_clientes_pwa 
    WHERE lojista_id = ? AND ativo = 1 
    GROUP BY cidade
");
$stmt->execute([$lojistaId]);
$inscritosPorCidade = $stmt->fetchAll();

$stmt = $db->query("SELECT COUNT(*) as total FROM enc_clientes_pwa WHERE lojista_id = $lojistaId AND ativo = 1");
$totalInscritos = $stmt->fetch()['total'];

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $cidade = $_POST['cidade'] ?? null;

    if (empty($titulo) || empty($mensagem)) {
        $erro = 'Preencha título e mensagem';
    } elseif ($pushEnviados >= $limitePush) {
        $erro = 'Limite de notificações atingido. Faça upgrade do seu plano.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO enc_notificacoes_push (lojista_id, titulo, mensagem, segmento_cidade, status) 
            VALUES (?, ?, ?, ?, 'pendente')
        ");
        $stmt->execute([$lojistaId, $titulo, $mensagem, $cidade]);
        
        $sucesso = 'Notificação agendada com sucesso!';
        $pushEnviados++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Encartes Pro</title>
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
                    <a href="../index.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="../encartes/index.php" class="nav-link"><i class="bi bi-file-earmark-post me-2"></i> Encartes</a>
                    <a href="../mapa/index.php" class="nav-link"><i class="bi bi-geo-alt me-2"></i> Mapa</a>
                    <a href="../estatisticas/index.php" class="nav-link"><i class="bi bi-graph-up me-2"></i> Estatísticas</a>
                    <a href="index.php" class="nav-link active"><i class="bi bi-bell me-2"></i> Notificações</a>
                </nav>
            </div>
            <div class="col-md-10 p-4">
                <h4 class="fw-bold mb-4">Enviar Notificações Push</h4>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Limite do Plano</h6>
                                <h2 class="text-primary"><?= $limitePush ?></h2>
                                <small class="text-muted">notificações/mês</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Enviadas neste Mês</h6>
                                <h2 class="<?= $pushEnviados >= $limitePush ? 'text-danger' : 'text-success' ?>">
                                    <?= $pushEnviados ?>
                                </h2>
                                <small class="text-muted">de <?= $limitePush ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Nova Notificação</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Título</label>
                                        <input type="text" name="titulo" class="form-control" required 
                                               placeholder="Ex: Novas ofertas!">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mensagem</label>
                                        <textarea name="mensagem" class="form-control" rows="3" required 
                                                  placeholder="Ex: Venha conferir nossas promoções"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Segmentar por cidade (opcional)</label>
                                        <select name="cidade" class="form-select">
                                            <option value="">Todas as cidades</option>
                                            <?php foreach ($inscritosPorCidade as $ic): ?>
                                                <option value="<?= htmlspecialchars($ic['cidade']) ?>">
                                                    <?= htmlspecialchars($ic['cidade']) ?> (<?= $ic['total'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100" 
                                            <?= $pushEnviados >= $limitePush ? 'disabled' : '' ?>>
                                        <i class="bi bi-send"></i> Enviar Notificação
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 fw-bold">Inscritos: <?= $totalInscritos ?></h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cidade</th>
                                            <th>Inscritos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inscritosPorCidade as $ic): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($ic['cidade'] ?? 'Não informada') ?></td>
                                                <td><?= $ic['total'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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