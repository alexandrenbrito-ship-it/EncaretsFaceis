<?php
require_once __DIR__ . '/BaseModel.php';

class Visualizacao extends BaseModel {
    protected string $table = 'enc_visualizacoes_encarte';
    protected string $primaryKey = 'id';

    public function getTotalViewsPorLojista(int $lojistaId, ?string $periodo = null): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE lojista_id = ?";
        $params = [$lojistaId];

        if ($periodo === 'mes') {
            $sql .= " AND MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
        } elseif ($periodo === 'mes_anterior') {
            $sql .= " AND MONTH(data_hora) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(data_hora) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
        } elseif ($periodo === 'semana') {
            $sql .= " AND data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    public function getViewsPorDispositivo(int $lojistaId, ?string $periodo = null): array {
        $sql = "SELECT dispositivo, COUNT(*) as total FROM {$this->table} WHERE lojista_id = ?";
        $params = [$lojistaId];

        if ($periodo === 'mes') {
            $sql .= " AND MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
        }

        $sql .= " GROUP BY dispositivo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getViewsPorCidade(int $lojistaId, ?string $periodo = null): array {
        $sql = "SELECT cidade, COUNT(*) as total FROM {$this->table} WHERE lojista_id = ? AND cidade IS NOT NULL";
        $params = [$lojistaId];

        if ($periodo === 'mes') {
            $sql .= " AND MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
        }

        $sql .= " GROUP BY cidade ORDER BY total DESC LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getViewsPorEncarte(int $lojistaId, ?string $periodo = null): array {
        $sql = "
            SELECT e.id, e.titulo, COUNT(v.id) as visualizacoes
            FROM enc_encartes e
            LEFT JOIN {$this->table} v ON e.id = v.encarte_id";

        if ($periodo === 'mes') {
            $sql .= " AND MONTH(v.data_hora) = MONTH(CURRENT_DATE()) AND YEAR(v.data_hora) = YEAR(CURRENT_DATE())";
        }

        $sql .= " WHERE e.lojista_id = ? GROUP BY e.id, e.titulo ORDER BY visualizacoes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lojistaId]);
        return $stmt->fetchAll();
    }

    public function getViewsPorDia(int $lojistaId, int $dias = 30): array {
        $stmt = $this->db->prepare("
            SELECT DATE(data_hora) as data, COUNT(*) as total
            FROM {$this->table}
            WHERE lojista_id = ? AND data_hora >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(data_hora)
            ORDER BY data ASC
        ");
        $stmt->execute([$lojistaId, $dias]);
        return $stmt->fetchAll();
    }

    public function jaRegistrado(int $encarteId, string $ipHash, int $minutos = 30): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM {$this->table}
            WHERE encarte_id = ? AND ip_hash = ?
            AND data_hora >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        $stmt->execute([$encarteId, $ipHash, $minutos]);
        return (int) $stmt->fetch()['COUNT(*)'] > 0;
    }
}