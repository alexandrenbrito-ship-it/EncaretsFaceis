<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
} else {
    $dados = $dados ?? [];
}

$cabecalho = $dados['cabecalho'] ?? ['titulo' => '', 'subtitulo' => '', 'cor_fundo' => '#2563eb', 'cor_texto' => '#ffffff', 'imagem' => ''];
$produtos = $dados['produtos'] ?? [];
$galeria = $dados['galeria'] ?? [];
$rodape = $dados['rodape'] ?? ['texto' => '', 'telefone' => '', 'endereco' => ''];
$config = $dados['configuracao'] ?? ['colunas' => 3];
?>
<div class="encarte-preview" style="font-family: Arial, sans-serif; max-width: 100%;">
    <header style="background: <?= htmlspecialchars($cabecalho['cor_fundo']) ?>; color: <?= htmlspecialchars($cabecalho['cor_texto']) ?>; padding: 30px; text-align: center; <?= !empty($cabecalho['imagem']) ? 'background-image: url(' . htmlspecialchars($cabecalho['imagem']) . '); background-size: cover; background-position: center;' : '' ?>">
        <h1 style="margin: 0; font-size: 2rem;"><?= htmlspecialchars($cabecalho['titulo'] ?: 'Título do Encarte') ?></h1>
        <?php if (!empty($cabecalho['subtitulo'])): ?>
            <p style="margin: 10px 0 0;"><?= htmlspecialchars($cabecalho['subtitulo']) ?></p>
        <?php endif; ?>
    </header>

    <section style="padding: 20px;">
        <div style="display: grid; grid-template-columns: repeat(<?= (int)$config['colunas'] ?>, 1fr); gap: 15px;">
            <?php if (empty($produtos)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6c757d; background: #f8f9fa; border-radius: 8px;">
                    <i class="bi bi-box-seam" style="font-size: 3rem;"></i>
                    <p>Adicione produtos no editor</p>
                </div>
            <?php else: ?>
                <?php foreach ($produtos as $produto): 
                    $balaoEstilo = '';
                    switch ($produto['balao']['formato'] ?? 'retangular') {
                        case 'circular':
                            $balaoEstilo = 'border-radius: 50%; width: 60px; height: 60px;';
                            break;
                        case 'badge':
                            $balaoEstilo = 'border-radius: 20px; padding: 5px 15px;';
                            break;
                        default:
                            $balaoEstilo = 'border-radius: 4px;';
                    }
                ?>
                    <div style="background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative;">
                        <?php if (!empty($produto['balao']['texto'])): ?>
                            <span style="position: absolute; top: -10px; right: -10px; background: <?= htmlspecialchars($produto['balao']['cor'] ?? '#e94560') ?>; color: white; padding: 5px 10px; font-size: 0.7rem; font-weight: bold; <?= $balaoEstilo ?>">
                                <?= htmlspecialchars($produto['balao']['texto']) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="<?= htmlspecialchars($produto['imagem']) ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 10px;">
                        <?php endif; ?>
                        
                        <h6 style="margin: 0 0 8px; font-size: 0.9rem;"><?= htmlspecialchars($produto['nome']) ?></h6>
                        
                        <?php if (!empty($produto['preco_original'])): ?>
                            <span style="text-decoration: line-through; color: #6c757d; font-size: 0.8rem;">R$ <?= number_format((float)$produto['preco_original'], 2, ',', '.') ?></span>
                        <?php endif; ?>
                        
                        <div style="color: #e94560; font-weight: bold; font-size: 1.1rem;">
                            R$ <?= number_format((float)($produto['preco_oferta'] ?? 0), 2, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($galeria)): ?>
        <section style="padding: 20px; background: #f8f9fa;">
            <h6 style="margin-bottom: 15px;">Galeria</h6>
            <div style="display: flex; gap: 10px; overflow-x: auto;">
                <?php foreach ($galeria as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" style="height: 100px; border-radius: 8px;">
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <footer style="background: #333; color: white; padding: 20px; text-align: center;">
        <?php if (!empty($rodape['texto'])): ?>
            <p style="margin: 0;"><?= htmlspecialchars($rodape['texto']) ?></p>
        <?php endif; ?>
        <?php if (!empty($rodape['telefone'])): ?>
            <p style="margin: 5px 0;"><i class="bi bi-telephone"></i> <?= htmlspecialchars($rodape['telefone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($rodape['endereco'])): ?>
            <p style="margin: 5px 0;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($rodape['endereco']) ?></p>
        <?php endif; ?>
    </footer>
</div>