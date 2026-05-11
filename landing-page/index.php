<?php
$planos = [];
$dbConnected = false;

if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
    
    if (defined('DB_HOST')) {
        require_once __DIR__ . '/../config/database.php';
        
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM enc_planos WHERE ativo = 1 ORDER BY ordem_exibicao ASC");
            $planos = $stmt->fetchAll();
            $dbConnected = true;
        } catch (Exception $e) {
            $dbConnected = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encartes Pro - Encartes Digitais para seu Negócio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1d4ed8;
            --accent: #e94560;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            background-size: 50px;
            animation: float 20s linear infinite;
        }
        @keyframes float {
            from { transform: translateY(0); }
            to { transform: translateY(-50px); }
        }
        .feature-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 16px;
            transition: all 0.3s;
            background: white;
        }
        .plan-card:hover {
            border-color: var(--primary);
            transform: scale(1.02);
        }
        .plan-card.destaque {
            border-color: var(--accent);
            position: relative;
        }
        .plan-card.destaque::before {
            content: 'Mais Popular';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .price {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
        }
        .price span {
            font-size: 1rem;
            color: #6c757d;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--secondary);
        }
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#">
                <i class="bi bi-collection text-primary"></i> Encartes Pro
            </a>
            <div class="d-flex gap-2">
                <a href="/encartes/lojista/login.php" class="btn btn-outline-primary">Login</a>
                <a href="/encartes/lojista/registro.php" class="btn btn-primary">Começar Grátis</a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Encartes Digitais para seu Negócio
                    </h1>
                    <p class="lead mb-4">
                        Crie encartes profissionais, Publique online e Alcance mais clientes.
                        Tudo em uma plataforma completa e fácil de usar.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="/encartes/lojista/registro.php" class="btn btn-light btn-lg">
                            <i class="bi bi-rocket"></i> Criar Conta Grátis
                        </a>
                        <a href="#como-funciona" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-play-circle"></i> Ver Demo
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="bg-white rounded-4 shadow-lg p-4" style="transform: rotate(-3deg);">
                        <img src="https://via.placeholder.com/400x300/e9ecef/6c757d?text=Preview+Encarte" 
                             alt="Preview Encarte" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Como Funciona</h2>
                <p class="text-muted">Em 3 passos você cria seu primeiro encarte digital</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h5>1. Escolha um Template</h5>
                        <p class="text-muted">Selecione entre modelos prontos ou crie do zero</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h5>2. Adicione Produtos</h5>
                        <p class="text-muted">Inclua fotos, preços e ofertas do seu catálogo</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-4">
                            <i class="bi bi-share"></i>
                        </div>
                        <h5>3. Compartilhe</h5>
                        <p class="text-muted">Publique e compartilhe nas redes sociais</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nossos Planos</h2>
                <p class="text-muted">Escolha o plano ideal para seu negócio</p>
            </div>
            <div class="row justify-content-center">
                <?php if ($dbConnected && !empty($planos)): ?>
                    <?php foreach ($planos as $plano): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card plan-card h-100 <?= $plano['destaque'] ? 'destaque' : '' ?> p-4">
                                <div class="text-center">
                                    <h4 class="fw-bold"><?= htmlspecialchars($plano['nome']) ?></h4>
                                    <p class="text-muted"><?= htmlspecialchars($plano['descricao']) ?></p>
                                    <div class="price mb-3">
                                        R$ <?= number_format($plano['preco_mensal'], 2, ',', '.') ?>
                                        <span>/mês</span>
                                    </div>
                                    <ul class="list-unstyled text-start mb-4">
                                        <li class="mb-2">
                                            <i class="bi bi-check text-success me-2"></i>
                                            <?= $plano['limite_encartes'] == -1 ? 'Encartes Ilimitados' : $plano['limite_encartes'] . ' encartes' ?>
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check text-success me-2"></i>
                                            <?= $plano['limite_notificacoes_mes'] ?> notificações/mês
                                        </li>
                                        <?php if ($plano['permite_mapa']): ?>
                                            <li class="mb-2">
                                                <i class="bi bi-check text-success me-2"></i> Mapa de clientes
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($plano['permite_estatisticas_avancadas']): ?>
                                            <li class="mb-2">
                                                <i class="bi bi-check text-success me-2"></i> Estatísticas avançadas
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                    <a href="/encartes/lojista/registro.php?plano=<?= $plano['id'] ?>" class="btn <?= $plano['destaque'] ? 'btn-primary' : 'btn-outline-primary' ?> w-100">
                                        Assinar Agora
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-4 mb-4">
                        <div class="card plan-card h-100 p-4">
                            <div class="text-center">
                                <h4 class="fw-bold">Básico</h4>
                                <p class="text-muted">Ideal para começar</p>
                                <div class="price mb-3">
                                    R$ 29,90<span>/mês</span>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> 5 encartes</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> 500 notificações/mês</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Mapa de clientes</li>
                                </ul>
                                <a href="/encartes/lojista/registro.php" class="btn btn-outline-primary w-100">Assinar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card plan-card destaque h-100 p-4">
                            <div class="text-center">
                                <h4 class="fw-bold">Profissional</h4>
                                <p class="text-muted">Para lojas em crescimento</p>
                                <div class="price mb-3">
                                    R$ 79,90<span>/mês</span>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> 20 encartes</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> 2.000 notificações/mês</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Mapa de clientes</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Estatísticas avançadas</li>
                                </ul>
                                <a href="/encartes/lojista/registro.php" class="btn btn-primary w-100">Assinar Agora</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card plan-card h-100 p-4">
                            <div class="text-center">
                                <h4 class="fw-bold">Enterprise</h4>
                                <p class="text-muted">Sem limites</p>
                                <div class="price mb-3">
                                    R$ 199,90<span>/mês</span>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Encartes ilimitados</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> 10.000 notificações/mês</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Tudo do Profissional</li>
                                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Exportação de dados</li>
                                </ul>
                                <a href="/encartes/lojista/registro.php" class="btn btn-outline-primary w-100">Assinar</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cta-section py-5">
        <div class="container py-5 text-center">
            <h2 class="fw-bold mb-3">Pronto para começar?</h2>
            <p class="mb-4">Crie sua conta grátis e tenha 7 dias de período trial</p>
            <a href="/encartes/lojista/registro.php" class="btn btn-light btn-lg">
                <i class="bi bi-rocket"></i> Criar Conta Grátis
            </a>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold">
                        <i class="bi bi-collection"></i> Encartes Pro
                    </h5>
                    <p class="text-white-50">Encartes digitais para seu negócio</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/encartes/admin/login.php" class="text-white-50 text-decoration-none">Painel Admin</a>
                </div>
            </div>
            <hr>
            <p class="text-center text-white-50 mb-0">
                &copy; <?= date('Y') ?> Encartes Pro. Todos os direitos reservados.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>