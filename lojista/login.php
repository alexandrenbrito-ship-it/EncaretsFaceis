<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Usuario.php';
require_once __DIR__ . '/../src/Models/Lojista.php';

$erro = '';
$sucesso = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
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

        if (!$usuario || $usuario['tipo'] !== 'lojista' || !$usuario['ativo']) {
            $erro = 'Credenciais inválidas';
        } elseif (!password_verify($senha, $usuario['senha_hash'])) {
            $erro = 'Senha incorreta';
        } else {
            $lojistaModel = new Lojista();
            $lojista = $lojistaModel->getByUsuario($usuario['id']);

            if (!$lojista) {
                $erro = 'Lojista não encontrado';
            } else {
                $_SESSION['lojista_id'] = $lojista['id'];
                $_SESSION['lojista_nome'] = $lojista['nome_loja'];
                $_SESSION['lojista_subdominio'] = $lojista['subdominio'];
                $_SESSION['lojista_plano_id'] = $lojista['plano_id'];
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                $usuarioModel->update($usuario['id'], ['ultimo_acesso' => date('Y-m-d H:i:s')]);

                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Lojista - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            background: #2563eb;
            border: none;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-shop fs-1"></i>
                        <h3 class="mt-2 mb-0">Encartes Pro</h3>
                        <p class="mb-0 opacity-75">Painel do Lojista</p>
                    </div>
                    <div class="p-4">
                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($sucesso): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" required 
                                           placeholder="seu@email.com">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="senha" class="form-control" required 
                                           placeholder="••••••••">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="lembrar">
                                    <label class="form-check-label" for="lembrar">Lembrar</label>
                                </div>
                                <a href="#" class="text-decoration-none small">Esqueceu a senha?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">Não tem conta?</p>
                            <a href="registro.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Criar Conta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>