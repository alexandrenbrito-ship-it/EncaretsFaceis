<?php
require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel {
    protected string $table = 'enc_usuarios';
    protected string $primaryKey = 'id';

    public function findByEmail(string $email): ?array {
        return $this->findBy('email', $email);
    }

    public function criarLojista(array $dados): int {
        return $this->create($dados);
    }

    public function verificarEmail(string $email): bool {
        return $this->findBy('email', $email) !== null;
    }

    public function getLojistas(): array {
        $stmt = $this->db->query("
            SELECT u.*, l.nome_loja, l.subdominio, l.status_assinatura, p.nome as plano_nome
            FROM {$this->table} u
            JOIN enc_lojistas l ON u.id = l.usuario_id
            JOIN enc_planos p ON l.plano_id = p.id
            WHERE u.tipo = 'lojista'
            ORDER BY u.data_criacao DESC
        ");
        return $stmt->fetchAll();
    }

    public function getAdmins(): array {
        return $this->findAll(['tipo' => 'admin'], 'nome ASC');
    }
}