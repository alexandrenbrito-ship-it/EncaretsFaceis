// Editor de Encartes - JavaScript
(function() {
    'use strict';

    let dadosEncarte = {
        cabecalho: {
            titulo: '',
            subtitulo: '',
            imagem: '',
            cor_fundo: '#2563eb',
            cor_texto: '#ffffff'
        },
        produtos: [],
        galeria: [],
        rodape: {
            texto: '',
            telefone: '',
            endereco: ''
        },
        configuracao: {
            colunas: 3,
            layout: 'grid'
        }
    };

    let debounceTimer = null;

    function init() {
        inicializarEventos();
        inicializarDragDrop();
        inicializarColorPicker();
    }

    function inicializarEventos() {
        document.querySelectorAll('#cabecalho_titulo, #cabecalho_subtitulo, #cabecalho_imagem').forEach(el => {
            el.addEventListener('input', debounceAtualizarPreview);
        });

        document.querySelectorAll('#cabecalho_cor_fundo, #cabecalho_cor_texto').forEach(el => {
            el.addEventListener('input', debounceAtualizarPreview);
        });

        document.querySelectorAll('#rodape_texto, #rodape_telefone, #rodape_endereco').forEach(el => {
            el.addEventListener('input', debounceAtualizarPreview);
        });
    }

    function debounceAtualizarPreview() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(atualizarPreview, 300);
    }

    function atualizarPreview() {
        dadosEncarte.cabecalho = {
            titulo: document.getElementById('cabecalho_titulo')?.value || '',
            subtitulo: document.getElementById('cabecalho_subtitulo')?.value || '',
            imagem: document.getElementById('cabecalho_imagem')?.value || '',
            cor_fundo: document.getElementById('cabecalho_cor_fundo')?.value || '#2563eb',
            cor_texto: document.getElementById('cabecalho_cor_texto')?.value || '#ffffff'
        };

        dadosEncarte.rodape = {
            texto: document.getElementById('rodape_texto')?.value || '',
            telefone: document.getElementById('rodape_telefone')?.value || '',
            endereco: document.getElementById('rodape_endereco')?.value || ''
        };

        dadosEncarte.configuracao = {
            colunas: parseInt(document.getElementById('config_colunas')?.value || 3),
            layout: document.getElementById('config_layout')?.value || 'grid'
        };

        fetch('preview.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dadosEncarte)
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('preview_frame').innerHTML = html;
        });
    }

    function inicializarDragDrop() {
        const listaProdutos = document.getElementById('lista_produtos');
        if (!listaProdutos) return;

        new Sortable(listaProdutos, {
            animation: 150,
            handle: '.produto-card',
            onEnd: function(evt) {
                const produto = dadosEncarte.produtos.splice(evt.oldIndex, 1)[0];
                dadosEncarte.produtos.splice(evt.newIndex, 0, produto);
            }
        });
    }

    function inicializarColorPicker() {
        if (typeof Pickr !== 'undefined') {
            document.querySelectorAll('.cor-field').forEach(el => {
                Pickr.create({
                    el: el,
                    theme: 'nano',
                    default: el.value,
                    swatches: ['#2563eb', '#e94560', '#10b981', '#f59e0b', '#6366f1', '#ec4899', '#ffffff', '#000000']
                }).on('change', (color, source, instance) => {
                    el.value = color.toHEXA().toString();
                    debounceAtualizarPreview();
                });
            });
        }
    }

    window.adicionarProduto = function(produto) {
        dadosEncarte.produtos.push({
            id: Date.now().toString(),
            nome: produto.nome,
            preco_original: produto.preco_original,
            preco_oferta: produto.preco_oferta,
            imagem: produto.imagem,
            balao: produto.balao
        });
        renderizarListaProdutos();
        atualizarPreview();
    };

    window.removerProduto = function(id) {
        dadosEncarte.produtos = dadosEncarte.produtos.filter(p => p.id !== id);
        renderizarListaProdutos();
        atualizarPreview();
    };

    function renderizarListaProdutos() {
        const lista = document.getElementById('lista_produtos');
        if (!lista) return;

        if (dadosEncarte.produtos.length === 0) {
            lista.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-box-seam fs-1"></i>
                    <p class="mb-0">Nenhum produto adicionado</p>
                </div>
            `;
            return;
        }

        lista.innerHTML = dadosEncarte.produtos.map(produto => `
            <div class="produto-card" draggable="true">
                <div class="d-flex justify-content-between align-items-start">
                    <strong>${escapeHtml(produto.nome)}</strong>
                    <button class="btn btn-sm btn-outline-danger" onclick="removerProduto('${produto.id}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="small text-muted">
                    R$ ${formatarPreco(produto.preco_oferta)}
                    ${produto.preco_original ? `<span class="text-decoration-line-through">R$ ${formatarPreco(produto.preco_original)}</span>` : ''}
                </div>
                ${produto.imagem ? `<img src="${escapeHtml(produto.imagem)}" class="img-fluid mt-2 rounded" style="max-height: 60px;">` : ''}
            </div>
        `).join('');
    }

    window.adicionarGaleria = function(url) {
        if (url) {
            dadosEncarte.galeria.push(url);
            renderizarGaleria();
            atualizarPreview();
        }
    };

    function renderizarGaleria() {
        const lista = document.getElementById('lista_galeria');
        if (!lista) return;

        lista.innerHTML = dadosEncarte.galeria.map((img, index) => `
            <div class="position-relative d-inline-block m-1">
                <img src="${escapeHtml(img)}" class="rounded" style="height: 80px;">
                <button class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="removerGaleria(${index})">×</button>
            </div>
        `).join('');
    }

    window.removerGaleria = function(index) {
        dadosEncarte.galeria.splice(index, 1);
        renderizarGaleria();
        atualizarPreview();
    };

    window.salvarEncarte = function() {
        const titulo = document.getElementById('encarte_titulo')?.value || '';
        const descricao = '';

        return fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=salvar&titulo=${encodeURIComponent(titulo)}&descricao=${encodeURIComponent(descricao)}&dados=${encodeURIComponent(JSON.stringify(dadosEncarte))}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                mostrarNotificacao('Encarte salvo com sucesso!', 'success');
            }
            return data;
        });
    };

    window.publicarEncarte = function() {
        salvarEncarte().then(() => {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'acao=publicar'
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    mostrarNotificacao('Encarte publicado!', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        });
    };

    function mostrarNotificacao(mensagem, tipo) {
        const alertArea = document.getElementById('alert-area') || document.createElement('div');
        alertArea.id = 'alert-area';
        alertArea.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show">
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.prepend(alertArea);
        setTimeout(() => alertArea.remove(), 3000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatarPreco(valor) {
        if (!valor) return '0,00';
        const num = parseFloat(valor.replace(',', '.'));
        return isNaN(num) ? '0,00' : num.toFixed(2).replace('.', ',');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();