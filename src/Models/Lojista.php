<?php
require_once __DIR__ . '/BaseModel.php';

class Lojista extends BaseModel {
    protected string $table = 'enc_lojistas';
    protected string $primaryKey = 'id';

    public function getByUsuario(int $usuarioId): ?array {
        $stmt = $this->db->prepare("
            SELECT l.*, p.nome as plano_nome, p.limite_encartes, p.limite_notificacoes_mes
            FROM {$this->table} l
            JOIN enc_planos p ON l.plano_id = p.id
            WHERE l.usuario_id = ?
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch() ?: null;
    }

    public function getBySubdominio(string $subdominio): ?array {
        return $this->findBy('subdominio', $subdominio);
    }

    public function verificarSubdominio(string $subdominio): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE subdominio = ?");
        $stmt->execute([$subdominio]);
        return (int) $stmt->fetch()['COUNT(*)'] > 0;
    }

    public function gerarSubdominio(string $nome): string {
        $subdominio = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $nome)));
        $subdominio = substr($subdominio, 0, 50);
        
        if (empty($subdominio)) {
            $subdominio = 'loja' . time();
        }

        $original = $subdominio;
        $contador = 1;

        while ($this->verificarSubdominio($subdominio)) {
            $subdominio = $original . $contador;
            $contador++;
        }

        return $subdominio;
    }

    public function atualizarRecursos(int $lojistaId, array $recursos): bool {
        $lojista = $this->find($lojistaId);
        $atual = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
        $atual = array_merge($atual, $recursos);
        
        return $this->update($lojistaId, [
            'recursos_consumidos' => json_encode($atual)
        ]);
    }

    public function getConsumo(int $lojistaId): array {
        $lojista = $this->find($lojistaId);
        return json_decode($lojista['recursos_consumidos'] ?? '{}', true);
    }

    public function getEstatisticas(): array {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_assinatura = 'ativa' THEN 1 ELSE 0 END) as ativos,
                SUM(CASE WHEN status_assinatura = 'trial' THEN 1 ELSE 0 END) as trials,
                SUM(CASE WHEN status_assinatura = 'vencida' THEN 1 ELSE 0 END) as vencidas
            FROM {$this->table}
        ");
        return $stmt->fetch();
    }
}