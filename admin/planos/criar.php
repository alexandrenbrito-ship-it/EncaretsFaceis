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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = (float)($_POST['preco_mensal'] ?? 0);
    $limiteEncartes = (int)($_POST['limite_encartes'] ?? 5);
    $limiteNotificacoes = (int)($_POST['limite_notificacoes_mes'] ?? 500);
    $limiteImagens = (int)($_POST['limite_imagens_por_galeria'] ?? 10);
    $permiteMapa = isset($_POST['permite_mapa']) ? 1 : 0;
    $permiteEstatisticas = isset($_POST['permite_estatisticas_avancadas']) ? 1 : 0;
    $permiteExportacao = isset($_POST['permite_exportacao']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ordem = (int)($_POST['ordem_exibicao'] ?? 0);

    if (empty($nome) || $preco <= 0) {
        $erro = 'Preencha o nome e o preço do plano';
    } else {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO enc_planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, 
                limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, 
                destaque, ordem_exibicao, ativo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$nome, $descricao, $preco, $limiteEncartes, $limiteNotificacoes, $limiteImagens, 
            $permiteMapa, $permiteEstatisticas, $permiteExportacao, $destaque, $ordem]);
        
        $sucesso = 'Plano criado com sucesso!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Plano - Admin</title>
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
                    <h4 class="text-white mb-4">Novo Plano</h4>
                    
                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Nome do Plano *</label>
                                <input type="text" name="nome" class="form-control" required placeholder="Ex: Premium">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Preço Mensal *</label>
                                <input type="number" name="preco_mensal" class="form-control" step="0.01" min="0" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="2" placeholder="Descrição breve do plano"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-white">Limite Encartes</label>
                                <input type="number" name="limite_encartes" class="form-control" value="5">
                                <small class="text-white-50">-1 = ilimitado</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-white">Limite Notificações/Mês</label>
                                <input type="number" name="limite_notificacoes_mes" class="form-control" value="500">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-white">Limite Imagens/Galeria</label>
                                <input type="number" name="limite_imagens_por_galeria" class="form-control" value="10">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-white">Ordem de Exibição</label>
                                <input type="number" name="ordem_exibicao" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="destaque" id="destaque">
                                    <label class="form-check-label text-white" for="destaque">Destacar na Landing Page</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permite_mapa" id="permite_mapa" checked>
                                    <label class="form-check-label text-white" for="permite_mapa">Mapa de Clientes</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permite_estatisticas_avancadas" id="permite_estatisticas">
                                    <label class="form-check-label text-white" for="permite_estatisticas">Estatísticas Avançadas</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permite_exportacao" id="permite_exportacao">
                                    <label class="form-check-label text-white" for="permite_exportacao">Exportação de Dados</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Criar Plano</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>