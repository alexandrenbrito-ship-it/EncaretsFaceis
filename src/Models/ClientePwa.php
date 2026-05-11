<?php
require_once __DIR__ . '/BaseModel.php';

class ClientePwa extends BaseModel {
    protected string $table = 'enc_clientes_pwa';
    protected string $primaryKey = 'id';

    public function getPorLojista(int $lojistaId, ?string $cidade = null): array {
        $sql = "SELECT * FROM {$this->table} WHERE lojista_id = ? AND ativo = 1";
        $params = [$lojistaId];

        if ($cidade) {
            $sql .= " AND cidade = ?";
            $params[] = $cidade;
        }

        $sql .= " ORDER BY data_cadastro DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countPorLojista(int $lojistaId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE lojista_id = ? AND ativo = 1");
        $stmt->execute([$lojistaId]);
        return (int) $stmt->fetch()['total'];
    }

    public function findByEndpoint(int $lojistaId, string $endpoint): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE lojista_id = ? AND endpoint_push = ?");
        $stmt->execute([$lojistaId, $endpoint]);
        return $stmt->fetch() ?: null;
    }

    public function getPorCidade(int $lojistaId): array {
        $stmt = $this->db->prepare("
            SELECT cidade, COUNT(*) as total 
            FROM {$this->table} 
            WHERE lojista_id = ? AND ativo = 1 AND cidade IS NOT NULL
            GROUP BY cidade
            ORDER BY total DESC
        ");
        $stmt->execute([$lojistaId]);
        return $stmt->fetchAll();
    }

    public function atualizarUltimoAcesso(int $id): bool {
        return $this->update($id, ['ultimo_acesso' => date('Y-m-d H:i:s')]);
    }
}