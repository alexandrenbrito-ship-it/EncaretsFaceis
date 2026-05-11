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

$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM enc_planos WHERE ativo = 1 ORDER BY ordem_exibicao ASC");
$planos = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nomeLoja = trim($_POST['nome_loja'] ?? '');
    $subdominio = trim($_POST['subdominio'] ?? '');
    $planoId = (int)($_POST['plano_id'] ?? 0);

    if (empty($nome) || empty($email) || empty($senha) || empty($nomeLoja) || empty($subdominio)) {
        $erro = 'Preencha todos os campos';
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM enc_lojistas WHERE subdominio = ?");
        $stmt->execute([$subdominio]);
        if ($stmt->fetch()['COUNT(*)'] > 0) {
            $erro = 'Subdomínio já está em uso';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                INSERT INTO enc_usuarios (nome, email, senha_hash, tipo) 
                VALUES (?, ?, ?, 'lojista')
            ");
            $stmt->execute([$nome, $email, $senhaHash]);
            $usuarioId = $db->lastInsertId();

            $stmt = $db->prepare("
                INSERT INTO enc_lojistas (usuario_id, plano_id, nome_loja, subdominio, status_assinatura, data_inicio, data_validade, recursos_consumidos) 
                VALUES (?, ?, ?, ?, 'trial', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), '{}')
            ");
            $stmt->execute([$usuarioId, $planoId, $nomeLoja, $subdominio]);

            $sucesso = 'Lojista criado com sucesso!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Lojista - Admin</title>
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
                    <h4 class="text-white mb-4">Novo Lojista</h4>
                    
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nome</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Plano</label>
                                <select name="plano_id" class="form-select" required>
                                    <?php foreach ($planos as $plano): ?>
                                        <option value="<?= $plano['id'] ?>"><?= htmlspecialchars($plano['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <hr class="border-secondary">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nome da Loja</label>
                                <input type="text" name="nome_loja" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Subdomínio</label>
                                <div class="input-group">
                                    <input type="text" name="subdominio" class="form-control" required>
                                    <span class="input-group-text">.seudominio.com</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary">Voltar</a>
                            <button type="submit" class="btn btn-primary">Criar Lojista</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>