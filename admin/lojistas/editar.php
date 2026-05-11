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

$stmt = $db->prepare("
    SELECT l.*, u.nome as usuario_nome, u.email, p.nome as plano_nome
    FROM enc_lojistas l
    JOIN enc_usuarios u ON l.usuario_id = u.id
    JOIN enc_planos p ON l.plano_id = p.id
    WHERE l.id = ?
");
$stmt->execute([$id]);
$lojista = $stmt->fetch();

if (!$lojista) {
    header('Location: index.php');
    exit;
}

$stmt = $db->query("SELECT * FROM enc_planos WHERE ativo = 1 ORDER BY ordem_exibicao ASC");
$planos = $stmt->fetchAll();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeLoja = trim($_POST['nome_loja'] ?? '');
    $subdominio = trim($_POST['subdominio'] ?? '');
    $planoId = (int)($_POST['plano_id'] ?? 0);
    $status = $_POST['status_assinatura'] ?? 'trial';
    $limiteCustom = $_POST['limite_custom'] ?? '';

    if (empty($nomeLoja) || empty($subdominio)) {
        $erro = 'Preencha os campos obrigatórios';
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM enc_lojistas WHERE subdominio = ? AND id != ?");
        $stmt->execute([$subdominio, $id]);
        if ($stmt->fetch()['total'] > 0) {
            $erro = 'Subdomínio já está em uso';
        } else {
            $limiteCustomJson = !empty($limiteCustom) ? json_encode(['encartes' => (int)$limiteCustom, 'manual' => true]) : null;

            $stmt = $db->prepare("
                UPDATE enc_lojistas 
                SET nome_loja = ?, subdominio = ?, plano_id = ?, status_assinatura = ?, limite_custom = ?
                WHERE id = ?
            ");
            $stmt->execute([$nomeLoja, $subdominio, $planoId, $status, $limiteCustomJson, $id]);

            $sucesso = 'Lojista atualizado com sucesso!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lojista - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
                        <h4 class="text-white mb-0">Editar Lojista</h4>
                        <a href="index.php" class="btn btn-secondary btn-sm">Voltar</a>
                    </div>
                    
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-white">Nome da Loja</label>
                            <input type="text" name="nome_loja" class="form-control" value="<?= htmlspecialchars($lojista['nome_loja']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-white">Subdomínio</label>
                            <div class="input-group">
                                <input type="text" name="subdominio" class="form-control" value="<?= htmlspecialchars($lojista['subdominio']) ?>" required>
                                <span class="input-group-text">.seudominio.com</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Plano</label>
                                <select name="plano_id" class="form-select" required>
                                    <?php foreach ($planos as $plano): ?>
                                        <option value="<?= $plano['id'] ?>" <?= $plano['id'] == $lojista['plano_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($plano['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Status da Assinatura</label>
                                <select name="status_assinatura" class="form-select">
                                    <option value="trial" <?= $lojista['status_assinatura'] == 'trial' ? 'selected' : '' ?>>Trial</option>
                                    <option value="ativa" <?= $lojista['status_assinatura'] == 'ativa' ? 'selected' : '' ?>>Ativa</option>
                                    <option value="vencida" <?= $lojista['status_assinatura'] == 'vencida' ? 'selected' : '' ?>>Vencida</option>
                                    <option value="cancelada" <?= $lojista['status_assinatura'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Limite Customizado de Encartes (opcional)</label>
                            <input type="number" name="limite_custom" class="form-control" placeholder="Deixe vazio para usar limite do plano">
                            <small class="text-white-50">Sobrescreve o limite do plano se preenchido</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">E-mail</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($lojista['email']) ?>" disabled>
                            <small class="text-white-50">O e-mail não pode ser alterado aqui</small>
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
</body>
</html>