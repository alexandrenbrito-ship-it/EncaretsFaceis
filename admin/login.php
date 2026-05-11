<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Usuario.php';

$erro = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos';
    } else {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findBy('email', $email);

        if (!$usuario || $usuario['tipo'] !== 'admin' || !$usuario['ativo']) {
            $erro = 'Credenciais inválidas';
        } elseif (!password_verify($senha, $usuario['senha_hash'])) {
            $erro = 'Senha incorreta';
        } else {
            $_SESSION['admin_id'] = $usuario['id'];
            $_SESSION['admin_nome'] = $usuario['nome'];
            $_SESSION['admin_email'] = $usuario['email'];

            session_regenerate_id(true);
            $usuarioModel->update($usuario['id'], ['ultimo_acesso' => date('Y-m-d H:i:s')]);

            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            width: 400px;
        }
        .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.15);
            border-color: #e94560;
            color: white;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.5); }
        .input-group-text {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
    <div class="login-card p-4">
        <div class="text-center text-white mb-4">
            <i class="bi bi-shield-lock fs-1 text-danger"></i>
            <h3 class="mt-3 fw-bold">Admin</h3>
            <p class="opacity-75">Encartes Pro</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger bg-danger text-white border-0">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-white">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" required placeholder="admin@email.com">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-white">Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="senha" class="form-control" required placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="/landing-page/" class="text-white-50 text-decoration-none">
                <i class="bi bi-arrow-left"></i> Voltar para site
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>