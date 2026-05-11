<?php
require_once __DIR__ . '/BaseModel.php';

class Encarte extends BaseModel {
    protected string $table = 'enc_encartes';
    protected string $primaryKey = 'id';

    public function getByLojista(int $lojistaId): array {
        return $this->findAll(['lojista_id' => $lojistaId], 'data_criacao DESC');
    }

    public function getPublicados(int $lojistaId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE lojista_id = ? AND publicado = 1 
            AND (data_expiracao IS NULL OR data_expiracao > NOW())
            ORDER BY data_publicacao DESC
        ");
        $stmt->execute([$lojistaId]);
        return $stmt->fetchAll();
    }

    public function getBySlug(int $lojistaId, string $slug): ?array {
        return $this->findBy('lojista_id', $lojistaId);
    }

    public function getSlug(string $slug): ?array {
        return $this->findBy('slug', $slug);
    }

    public function criar(array $dados): int {
        return $this->create($dados);
    }

    public function atualizar(int $id, array $dados): bool {
        $dados['data_atualizacao'] = date('Y-m-d H:i:s');
        return $this->update($id, $dados);
    }

    public function publicar(int $id): bool {
        return $this->update($id, [
            'publicado' => 1,
            'data_publicacao' => date('Y-m-d H:i:s')
        ]);
    }

    public function despublicar(int $id): bool {
        return $this->update($id, ['publicado' => 0]);
    }

    public function incrementarViews(int $id): bool {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getEstatisticas(int $lojistaId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_encartes,
                SUM(CASE WHEN publicado = 1 THEN 1 ELSE 0 END) as publicados,
                SUM(views) as total_views
            FROM {$this->table}
            WHERE lojista_id = ?
        ");
        $stmt->execute([$lojistaId]);
        return $stmt->fetch();
    }

    public function slugExists(int $lojistaId, string $slug, ?int $ignoreId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE lojista_id = ? AND slug = ?";
        $params = [$lojistaId, $slug];

        if ($ignoreId) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['COUNT(*)'] > 0;
    }

    public function gerarSlug(string $titulo, int $lojistaId, ?int $ignoreId = null): string {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]/', '-', $titulo)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $contador = 1;
        $slugOriginal = $slug;

        while ($this->slugExists($lojistaId, $slug, $ignoreId)) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }

        return $slug;
    }
}