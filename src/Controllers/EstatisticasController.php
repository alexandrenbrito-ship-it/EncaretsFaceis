<?php
require_once __DIR__ . '/../Models/Visualizacao.php';

class EstatisticasController {
    private Visualizacao $model;

    public function __construct() {
        $this->model = new Visualizacao();
    }

    public function getTotalViews(int $lojistaId, ?string $periodo = null): int {
        return $this->model->getTotalViewsPorLojista($lojistaId, $periodo);
    }

    public function getViewsPorDispositivo(int $lojistaId, ?string $periodo = null): array {
        return $this->model->getViewsPorDispositivo($lojistaId, $periodo);
    }

    public function getViewsPorCidade(int $lojistaId, ?string $periodo = null): array {
        return $this->model->getViewsPorCidade($lojistaId, $periodo);
    }

    public function getViewsPorEncarte(int $lojistaId, ?string $periodo = null): array {
        return $this->model->getViewsPorEncarte($lojistaId, $periodo);
    }

    public function getViewsPorDia(int $lojistaId, int $dias = 30): array {
        return $this->model->getViewsPorDia($lojistaId, $dias);
    }

    public function getResumo(int $lojistaId): array {
        $mesAtual = $this->getTotalViews($lojistaId, 'mes');
        $mesAnterior = $this->getTotalViews($lojistaId, 'mes_anterior');
        
        $crescimento = $mesAnterior > 0 
            ? round((($mesAtual - $mesAnterior) / $mesAnterior) * 100, 1)
            : 0;

        return [
            'views_mes' => $mesAtual,
            'views_total' => $this->getTotalViews($lojistaId),
            'crescimento' => $crescimento,
            'por_dispositivo' => $this->getViewsPorDispositivo($lojistaId, 'mes'),
            'por_cidade' => $this->getViewsPorCidade($lojistaId, 'mes')
        ];
    }
}