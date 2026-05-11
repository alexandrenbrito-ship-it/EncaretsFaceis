<?php
require_once __DIR__ . '/BaseModel.php';

class Plano extends BaseModel {
    protected string $table = 'enc_planos';
    protected string $primaryKey = 'id';

    public function getAtivos(): array {
        return $this->findAll(['ativo' => 1], 'ordem_exibicao ASC');
    }

    public function getDestaque(): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE ativo = 1 AND destaque = 1 
            ORDER BY ordem_exibicao ASC LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array {
        return $this->findAll([], 'ordem_exibicao ASC');
    }
}