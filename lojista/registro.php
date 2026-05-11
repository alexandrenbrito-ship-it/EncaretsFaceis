<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Usuario.php';
require_once __DIR__ . '/../src/Models/Lojista.php';
require_once __DIR__ . '/../src/Models/Plano.php';

$erro = '';
$sucesso = '';

$planoModel = new Plano();
$planos = $planoModel->findAll(['ativo' => 1], 'ordem_exibicao ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $nomeLoja = trim($_POST['nome_loja'] ?? '');
    $planoId = (int)($_POST['plano_id'] ?? 0);

    if (empty($nome) || empty($email) || empty($senha) || empty($nomeLoja) || empty($planoId)) {
        $erro = 'Preencha todos os campos obrigatórios';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas não conferem';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres';
    } else {
        $usuarioModel = new Usuario();
        
        if ($usuarioModel->verificarEmail($email)) {
            $erro = 'Este e-mail já está cadastrado';
        } else {
            $lojistaModel = new Lojista();
            $subdominio = $lojistaModel->gerarSubdominio($nomeLoja);

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $usuarioId = $usuarioModel->create([
                'nome' => $nome,
                'email' => $email,
                'senha_hash' => $senhaHash,
                'tipo' => 'lojista',
                'ativo' => 1
            ]);

            $lojistaId = $lojistaModel->create([
                'usuario_id' => $usuarioId,
                'plano_id' => $planoId,
                'nome_loja' => $nomeLoja,
                'subdominio' => $subdominio,
                'status_assinatura' => 'trial',
                'data_inicio' => date('Y-m-d'),
                'data_validade' => date('Y-m-d', strtotime('+7 days')),
                'recursos_consumidos' => json_encode(['encartes_usados' => 0, 'push_enviados_mes' => 0])
            ]);

            $_SESSION['lojista_id'] = $lojistaId;
            $_SESSION['lojista_nome'] = $nomeLoja;
            $_SESSION['lojista_subdominio'] = $subdominio;
            $_SESSION['lojista_plano_id'] = $planoId;
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['usuario_nome'] = $nome;

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
    <title>Criar Conta - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .registro-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .plan-card {
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid #dee2e6;
        }
        .plan-card:hover {
            border-color: #2563eb;
        }
        .plan-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .plan-card.destaque {
            border-color: #e94560;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="registro-card p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shop fs-1 text-primary"></i>
                        <h3 class="mt-2">Criar Conta</h3>
                        <p class="text-muted">Comece seus encartes digitais agora mesmo</p>
                    </div>

                    <?php if ($erro): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Seu Nome *</label>
                                    <input type="text" name="nome" class="form-control" required 
                                           placeholder="João Silva">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome da Loja *</label>
                                    <input type="text" name="nome_loja" class="form-control" required 
                                           placeholder="Minha Loja">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="seu@email.com">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Senha *</label>
                                    <input type="password" name="senha" class="form-control" required 
                                           minlength="6" placeholder="Mínimo 6 caracteres">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Confirmar Senha *</label>
                                    <input type="password" name="confirmar_senha" class="form-control" required 
                                           placeholder="Repita a senha">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Escolha seu Plano</h5>

                        <div class="row">
                            <?php foreach ($planos as $plano): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card plan-card h-100 <?= $plano['destaque'] ? 'destaque' : '' ?>" 
                                         onclick="selectPlano(<?= $plano['id'] ?>)">
                                        <div class="card-body text-center">
                                            <?php if ($plano['destaque']): ?>
                                                <span class="badge bg-danger mb-2">Mais Popular</span>
                                            <?php endif; ?>
                                            <h5 class="card-title"><?= htmlspecialchars($plano['nome']) ?></h5>
                                            <p class="text-muted small"><?= htmlspecialchars($plano['descricao']) ?></p>
                                            <h3 class="text-primary">
                                                R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?>
                                                <small class="text-muted fs-6">/mês</small>
                                            </h3>
                                            <ul class="text-start small mt-3">
                                                <li><?= $plano['limite_encartes'] == -1 ? 'Encartes ilimitados' : $plano['limite_encartes'] . ' encartes' ?></li>
                                                <li><?= $plano['limite_notificacoes_mes'] ?> notificações/mês</li>
                                                <?php if ($plano['permite_mapa']): ?><li>Mapa de clientes</li><?php endif; ?>
                                                <?php if ($plano['permite_estatisticas_avancadas']): ?><li>Estatísticas avançadas</li><?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="plano_id" id="plano_id" value="">

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-rocket"></i> Criar Conta Grátis
                            </button>
                        </div>

                        <p class="text-center text-muted mt-3">
                            Já tem conta? <a href="login.php">Entrar</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPlano(id) {
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            document.getElementById('plano_id').value = id;
        }
        
        document.querySelector('.plan-card')?.classList.add('selected');
        document.getElementById('plano_id').value = '<?= $planos[0]['id'] ?? '' ?>';
    </script>
</body>
</html>