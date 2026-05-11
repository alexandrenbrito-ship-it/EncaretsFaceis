// Mapa de Clientes - JavaScript
(function() {
    'use strict';

    let map = null;
    let markers = [];
    let localizacoes = [];

    function init() {
        inicializarMapa();
        configurarFiltros();
    }

    function inicializarMapa() {
        const mapContainer = document.getElementById('map');
        if (!mapContainer) return;

        map = L.map('map').setView([-23.55, -46.63], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        carregarLocalizacoes();
    }

    function carregarLocalizacoes() {
        const lojistaId = window.LOJISTA_ID || 0;
        
        fetch(`/api/localizacoes.php?lojista_id=${lojistaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    localizacoes = data.localizacoes;
                    renderizarMarkers();
                    atualizarTabela();
                }
            })
            .catch(erro => console.error('Erro ao carregar localizações:', erro));
    }

    function renderizarMarkers(filtroCidade = null) {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        const localizacoesFiltradas = filtroCidade 
            ? localizacoes.filter(l => l.cidade === filtroCidade)
            : localizacoes;

        localizacoesFiltradas.forEach(loc => {
            if (loc.latitude && loc.longitude) {
                const marker = L.marker([loc.latitude, loc.longitude])
                    .addTo(map)
                    .bindPopup(criarPopupContent(loc));
                
                markers.push(marker);
            }
        });

        if (localizacoesFiltradas.length > 0) {
            const bounds = localizacoesFiltradas
                .filter(l => l.latitude && l.longitude)
                .map(l => [l.latitude, l.longitude]);
            
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    }

    function criarPopupContent(loc) {
        return `
            <div style="min-width: 150px;">
                <strong>${escapeHtml(loc.cidade || 'Cidade desconhecida')}</strong><br>
                <small>${escapeHtml(loc.estado || '')}</small><br>
                <hr style="margin: 5px 0;">
                <small class="text-muted">
                    Última atualização: ${loc.ultima_atualizacao ? formatarData(loc.ultima_atualizacao) : 'N/A'}
                </small>
            </div>
        `;
    }

    function atualizarTabela(filtroCidade = null) {
        const tbody = document.querySelector('#tabela-clientes tbody');
        if (!tbody) return;

        const localizacoesFiltradas = filtroCidade 
            ? localizacoes.filter(l => l.cidade === filtroCidade)
            : localizacoes;

        if (localizacoesFiltradas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Nenhum cliente encontrado
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = localizacoesFiltradas.map(loc => `
            <tr>
                <td>${escapeHtml(loc.cidade || '-')}</td>
                <td>${escapeHtml(loc.estado || '-')}</td>
                <td>${loc.latitude?.toFixed(4) || '-'}, ${loc.longitude?.toFixed(4) || '-'}</td>
                <td>${loc.precisao_metros ? loc.precisao_metros + 'm' : '-'}</td>
                <td>${loc.ultima_atualizacao ? formatarData(loc.ultima_atualizacao) : '-'}</td>
            </tr>
        `).join('');
    }

    function configurarFiltros() {
        const filtroCidade = document.getElementById('filtro-cidade');
        if (!filtroCidade) return;

        filtroCidade.addEventListener('change', function() {
            const cidade = this.value;
            renderizarMarkers(cidade);
            atualizarTabela(cidade);
        });
    }

    window.centralizarMapa = function(cidade) {
        const locs = localizacoes.filter(l => l.cidade === cidade);
        if (locs.length > 0) {
            const bounds = locs.map(l => [l.latitude, l.longitude]);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    };

    window.adicionarMarkerManual = function(lat, lng, cidade) {
        const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`<strong>${escapeHtml(cidade)}</strong>`);
        
        markers.push(marker);
        map.setView([lat, lng], 14);
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function formatarData(dataString) {
        if (!dataString) return '';
        const data = new Date(dataString);
        return data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();