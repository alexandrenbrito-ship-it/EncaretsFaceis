<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Encarte.php';

if (!isset($_SESSION['lojista_id'])) {
    header('Location: /lojista/login.php');
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$encarteId = (int)($_GET['id'] ?? 0);

if (!$encarteId) {
    header('Location: index.php');
    exit;
}

$encarteModel = new Encarte();
$encarte = $encarteModel->find($encarteId);

if (!$encarte || $encarte['lojista_id'] != $lojistaId) {
    header('Location: index.php');
    exit;
}

$dados = json_decode($encarte['dados_completos'] ?? '{}', true) ?? [];
if (!isset($dados['produtos'])) $dados['produtos'] = [];
if (!isset($dados['galeria'])) $dados['galeria'] = [];
if (!isset($dados['cabecalho'])) $dados['cabecalho'] = [];
if (!isset($dados['rodape'])) $dados['rodape'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    
    if ($_POST['acao'] === 'salvar') {
        $dadosJson = $_POST['dados'];
        
        $encarteModel->atualizar($encarteId, [
            'titulo' => $_POST['titulo'],
            'descricao' => $_POST['descricao'],
            'dados_completos' => $dadosJson
        ]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Encarte salvo com sucesso!']);
        exit;
    }
    
    if ($_POST['acao'] === 'publicar') {
        $encarteModel->publicar($encarteId);
        echo json_encode(['sucesso' => true, 'mensagem' => 'Encarte publicado!']);
        exit;
    }
    
    if ($_POST['acao'] === 'salvar_produto') {
        $produtos = $dados['produtos'] ?? [];
        $produtos[] = [
            'id' => uniqid(),
            'nome' => $_POST['nome'],
            'preco_original' => $_POST['preco_original'],
            'preco_oferta' => $_POST['preco_oferta'],
            'imagem' => $_POST['imagem'],
            'balao' => [
                'cor' => $_POST['balao_cor'] ?? '#e94560',
                'formato' => $_POST['balao_formato'] ?? 'retangular',
                'texto_promocao' => $_POST['balao_texto'] ?? 'OFERTA'
            ]
        ];
        $dados['produtos'] = $produtos;
        
        $encarteModel->atualizar($encarteId, [
            'dados_completos' => json_encode($dados)
        ]);
        
        echo json_encode(['sucesso' => true, 'produtos' => $produtos]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Encarte - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pickr/dist/themes/nano.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .editor-container { display: flex; height: calc(100vh - 60px); }
        .editor-sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            padding: 20px;
        }
        .editor-preview {
            flex: 1;
            background: #e9ecef;
            padding: 20px;
            overflow-y: auto;
        }
        .preview-frame {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            min-height: 600px;
            max-width: 800px;
            margin: 0 auto;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
        }
        .produto-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: move;
        }
        .produto-card:hover { background: #e9ecef; }
        .cor-field {
            width: 40px;
            height: 40px;
            border: none;
            cursor: pointer;
            border-radius: 8px;
        }
        .loading { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-white shadow-sm sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <input type="text" id="encarte_titulo" class="form-control form-control-lg border-0 fw-bold" 
                       value="<?= htmlspecialchars($encarte['titulo']) ?>" 
                       style="width: 300px;" onchange="autoSalvar()">
            </div>
            <div class="d-flex gap-2">
                <span id="status_salvar" class="text-muted align-self-center me-2">
                    <i class="bi bi-check-circle text-success"></i> Salvo
                </span>
                <button class="btn btn-outline-primary" onclick="salvarEncarte()">
                    <i class="bi bi-save"></i> Salvar
                </button>
                <?php if (!$encarte['publicado']): ?>
                    <button class="btn btn-success" onclick="publicarEncarte()">
                        <i class="bi bi-upload"></i> Publicar
                    </button>
                <?php else: ?>
                    <a href="/public/?s=<?= $_SESSION['lojista_subdominio'] ?>&e=<?= $encarte['slug'] ?>" 
                       target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-box-arrow-up-right"></i> Ver publicado
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="editor-container">
        <div class="editor-sidebar">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-cabecalho">
                        <i class="bi bi-card-heading"></i> Cabeçalho
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-produtos">
                        <i class="bi bi-box-seam"></i> Produtos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-galeria">
                        <i class="bi bi-images"></i> Galeria
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rodape">
                        <i class="bi bi-card-text"></i> Rodapé
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-cabecalho">
                    <h6 class="fw-bold mb-3">Cabeçalho do Encarte</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control" id="cabecalho_titulo" 
                               value="<?= htmlspecialchars($dados['cabecalho']['titulo'] ?? '') ?>" 
                               onchange="atualizarPreview()">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subtítulo</label>
                        <input type="text" class="form-control" id="cabecalho_subtitulo" 
                               value="<?= htmlspecialchars($dados['cabecalho']['subtitulo'] ?? '') ?>" 
                               onchange="atualizarPreview()">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cor de Fundo</label>
                        <div class="d-flex gap-2">
                            <input type="color" class="cor-field" id="cabecalho_cor_fundo" 
                                   value="<?= $dados['cabecalho']['cor_fundo'] ?? '#2563eb' ?>" 
                                   onchange="atualizarPreview()">
                            <input type="text" class="form-control" id="cabecalho_cor_fundo_txt" 
                                   value="<?= $dados['cabecalho']['cor_fundo'] ?? '#2563eb' ?>"
                                   onchange="document.getElementById('cabecalho_cor_fundo').value = this.value; atualizarPreview()">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cor do Texto</label>
                        <div class="d-flex gap-2">
                            <input type="color" class="cor-field" id="cabecalho_cor_texto" 
                                   value="<?= $dados['cabecalho']['cor_texto'] ?? '#ffffff' ?>" 
                                   onchange="atualizarPreview()">
                            <input type="text" class="form-control" id="cabecalho_cor_texto_txt" 
                                   value="<?= $dados['cabecalho']['cor_texto'] ?? '#ffffff' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagem de Fundo (URL)</label>
                        <input type="text" class="form-control" id="cabecalho_imagem" 
                               placeholder="https://..." 
                               value="<?= htmlspecialchars($dados['cabecalho']['imagem'] ?? '') ?>" 
                               onchange="atualizarPreview()">
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-produtos">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Produtos</h6>
                        <button class="btn btn-primary btn-sm" onclick="abrirModalProduto()">
                            <i class="bi bi-plus"></i> Adicionar
                        </button>
                    </div>
                    
                    <div id="lista_produtos">
                        <?php if (empty($dados['produtos'])): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-box-seam fs-1"></i>
                                <p class="mb-0">Nenhum produto adicionado</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($dados['produtos'] as $produto): ?>
                                <div class="produto-card" draggable="true">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                        <button class="btn btn-sm btn-outline-danger" onclick="removerProduto('<?= $produto['id'] ?>')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="small text-muted">
                                        R$ <?= number_format($produto['preco_oferta'], 2, ',', '.') ?>
                                        <?php if (!empty($produto['preco_original'])): ?>
                                            <span class="text-decoration-line-through">R$ <?= number_format($produto['preco_original'], 2, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($produto['imagem'])): ?>
                                        <img src="<?= htmlspecialchars($produto['imagem']) ?>" class="img-fluid mt-2 rounded" style="max-height: 60px;">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-galeria">
                    <h6 class="fw-bold mb-3">Galeria de Imagens</h6>
                    <p class="text-muted small">Adicione imagens extras para o rodapé do encarte</p>
                    
                    <div class="mb-3">
                        <label class="form-label">URL da Imagem</label>
                        <input type="text" class="form-control" id="galeria_url" placeholder="https://...">
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="adicionarGaleria()">
                        <i class="bi bi-plus"></i> Adicionar à Galeria
                    </button>
                    
                    <div id="lista_galeria" class="mt-3">
                        <?php if (!empty($dados['galeria'])): ?>
                            <?php foreach ($dados['galeria'] as $img): ?>
                                <div class="position-relative d-inline-block m-1">
                                    <img src="<?= htmlspecialchars($img) ?>" class="rounded" style="height: 80px;">
                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                            onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-rodape">
                    <h6 class="fw-bold mb-3">Rodapé do Encarte</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Texto do Rodapé</label>
                        <textarea class="form-control" id="rodape_texto" rows="2" 
                                  onchange="atualizarPreview()"><?= htmlspecialchars($dados['rodape']['texto'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="rodape_telefone" 
                               value="<?= htmlspecialchars($dados['rodape']['telefone'] ?? '') ?>" 
                               onchange="atualizarPreview()">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Endereço</label>
                        <textarea class="form-control" id="rodape_endereco" rows="2" 
                                  onchange="atualizarPreview()"><?= htmlspecialchars($dados['rodape']['endereco'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="editor-preview">
            <div class="preview-frame" id="preview_frame">
                <?php include __DIR__ . '/preview.php'; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_produto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Produto *</label>
                        <input type="text" class="form-control" id="produto_nome" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Preço Original</label>
                            <input type="text" class="form-control" id="produto_preco_original" placeholder="R$ 0,00">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Preço Oferta *</label>
                            <input type="text" class="form-control" id="produto_preco_oferta" placeholder="R$ 0,00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL da Imagem</label>
                        <input type="text" class="form-control" id="produto_imagem" placeholder="https://...">
                    </div>
                    <hr>
                    <h6>Configuração do Balão</h6>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Cor</label>
                            <input type="color" class="form-control form-control-color" id="produto_balao_cor" value="#e94560">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Formato</label>
                            <select class="form-select" id="produto_balao_formato">
                                <option value="retangular">Retangular</option>
                                <option value="circular">Circular</option>
                                <option value="badge">Badge</option>
                            </select>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Texto</label>
                            <input type="text" class="form-control" id="produto_balao_texto" value="OFERTA">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="adicionarProduto()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pickr/dist/pickr.min.js"></script>
    <script>
        let dadosEncarte = <?= json_encode($dados ?: ['produtos' => [], 'galeria' => [], 'cabecalho' => [], 'rodape' => []]) ?>;
        if (!dadosEncarte.produtos) dadosEncarte.produtos = [];
        if (!dadosEncarte.galeria) dadosEncarte.galeria = [];
        if (!dadosEncarte.cabecalho) dadosEncarte.cabecalho = {};
        if (!dadosEncarte.rodape) dadosEncarte.rodape = {};
        
        function atualizarPreview() {
            dadosEncarte.cabecalho = {
                titulo: document.getElementById('cabecalho_titulo').value,
                subtitulo: document.getElementById('cabecalho_subtitulo').value,
                cor_fundo: document.getElementById('cabecalho_cor_fundo').value,
                cor_texto: document.getElementById('cabecalho_cor_texto').value,
                imagem: document.getElementById('cabecalho_imagem').value
            };
            dadosEncarte.rodape = {
                texto: document.getElementById('rodape_texto').value,
                telefone: document.getElementById('rodape_telefone').value,
                endereco: document.getElementById('rodape_endereco').value
            };
            
            document.getElementById('encarte_titulo').value = dadosEncarte.cabecalho.titulo;
            
            fetch('preview.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dadosEncarte)
            }).then(r => r.text()).then(html => {
                document.getElementById('preview_frame').innerHTML = html;
            });
        }

        function autoSalvar() {
            document.getElementById('status_salvar').innerHTML = '<i class="bi bi-arrow-repeat"></i> Salvando...';
            salvarEncarte();
        }

        function salvarEncarte() {
            const titulo = document.getElementById('encarte_titulo').value;
            const descricao = '';
            
            const formData = new FormData();
            formData.append('acao', 'salvar');
            formData.append('titulo', titulo);
            formData.append('descricao', descricao);
            formData.append('dados', JSON.stringify(dadosEncarte));

            fetch('', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('status_salvar').innerHTML = 
                        '<i class="bi bi-check-circle text-success"></i> Salvo ' + new Date().toLocaleTimeString();
                });
        }

        function publicarEncarte() {
            salvarEncarte().then(() => {
                fetch('', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'acao=publicar'
                }).then(r => r.json()).then(data => {
                    location.reload();
                });
            });
        }

        function abrirModalProduto() {
            new bootstrap.Modal(document.getElementById('modal_produto')).show();
        }

        function adicionarProduto() {
            const produtoNome = document.getElementById('produto_nome').value.trim();
            const produtoPrecoOferta = document.getElementById('produto_preco_oferta').value.trim();

            if (!produtoNome || !produtoPrecoOferta) {
                alert('Preencha o nome e o preço de oferta (campos obrigatórios)');
                return;
            }

            const formData = new FormData();
            formData.append('acao', 'salvar_produto');
            formData.append('encarte_id', '<?= $encarteId ?>');
            formData.append('nome', produtoNome);
            formData.append('preco_original', document.getElementById('produto_preco_original').value.trim());
            formData.append('preco_oferta', produtoPrecoOferta);
            formData.append('imagem', document.getElementById('produto_imagem').value.trim());
            formData.append('balao_cor', document.getElementById('produto_balao_cor').value);
            formData.append('balao_formato', document.getElementById('produto_balao_formato').value);
            formData.append('balao_texto', document.getElementById('produto_balao_texto').value);

            fetch('/api/encarte-salvar.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    bootstrap.Modal.getInstance(document.getElementById('modal_produto')).hide();
                    location.reload();
                } else {
                    alert(data.erro || 'Erro ao salvar produto');
                }
            }).catch(err => {
                console.error('Erro:', err);
                alert('Erro ao salvar produto. Verifique o console para detalhes.');
            });
        }

        function removerProduto(id) {
            dadosEncarte.produtos = dadosEncarte.produtos.filter(p => p.id !== id);
            atualizarPreview();
        }

        Pickr.create({
            el: '.cor-field',
            theme: 'nano',
            default: '#2563eb',
            swatches: ['#2563eb', '#e94560', '#10b981', '#f59e0b', '#6366f1', '#ec4899']
        });
    </script>
</body>
</html>