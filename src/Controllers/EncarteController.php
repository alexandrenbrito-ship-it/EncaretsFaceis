<?php
require_once __DIR__ . '/../Models/Encarte.php';

class EncarteController {
    private Encarte $model;

    public function __construct() {
        $this->model = new Encarte();
    }

    public function listarPorLojista(int $lojistaId): array {
        return $this->model->getByLojista($lojistaId);
    }

    public function listarPublicados(int $lojistaId): array {
        return $this->model->getPublicados($lojistaId);
    }

    public function buscar(int $id): ?array {
        return $this->model->find($id);
    }

    public function buscarPorSlug(string $slug): ?array {
        return $this->model->getSlug($slug);
    }

    public function criar(array $dados): int {
        $slug = $this->model->gerarSlug($dados['titulo'], $dados['lojista_id']);
        
        return $this->model->criar([
            'lojista_id' => $dados['lojista_id'],
            'template_id' => $dados['template_id'] ?? null,
            'titulo' => $dados['titulo'],
            'slug' => $slug,
            'descricao' => $dados['descricao'] ?? '',
            'dados_completos' => $dados['dados_completos'],
            'publicado' => 0,
            'destaque' => 0
        ]);
    }

    public function atualizar(int $id, array $dados): bool {
        $updateData = [];
        
        if (isset($dados['titulo'])) {
            $updateData['titulo'] = $dados['titulo'];
        }
        if (isset($dados['descricao'])) {
            $updateData['descricao'] = $dados['descricao'];
        }
        if (isset($dados['dados_completos'])) {
            $updateData['dados_completos'] = $dados['dados_completos'];
        }

        return $this->model->atualizar($id, $updateData);
    }

    public function publicar(int $id): bool {
        return $this->model->publicar($id);
    }

    public function despublicar(int $id): bool {
        return $this->model->despublicar($id);
    }

    public function excluir(int $id): bool {
        return $this->model->delete($id);
    }

    public function incrementarViews(int $id): bool {
        return $this->model->incrementarViews($id);
    }

    public function getEstatisticas(int $lojistaId): array {
        return $this->model->getEstatisticas($lojistaId);
    }

    public function podeCriar(int $lojistaId): bool {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);
        
        if (!$lojista) return false;

        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);
        
        if (!$plano) return false;

        $limite = $plano['limite_encartes'];
        $consumo = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
        $usados = $consumo['encartes_usados'] ?? 0;

        return $limite == -1 || $usados < $limite;
    }
}