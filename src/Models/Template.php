<?php
require_once __DIR__ . '/BaseModel.php';

class Template extends BaseModel {
    protected string $table = 'enc_templates_encarte';
    protected string $primaryKey = 'id';

    public function getAtivos(): array {
        return $this->findAll(['ativo' => 1], 'nome ASC');
    }

    public function getPorCategoria(string $categoria): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE ativo = 1 AND categoria = ?
            ORDER BY nome ASC
        ");
        $stmt->execute([$categoria]);
        return $stmt->fetchAll();
    }

    public function incrementarUso(int $id): bool {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET uso_count = uso_count + 1 WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function getPopulares(int $limite = 5): array {
        return $this->findAll(['ativo' => 1], 'uso_count DESC', $limite);
    }

    public function aplicarTemplate(int $templateId, array $dadosBase): array {
        $template = $this->find($templateId);
        
        if (!$template) {
            return $dadosBase;
        }

        $estrutura = json_decode($template['configuracao_blocos'], true);
        
        return array_merge($dadosBase, [
            'configuracao' => $estrutura,
            'template_html' => $template['estrutura_html'],
            'template_css' => $template['estrutura_css']
        ]);
    }
}