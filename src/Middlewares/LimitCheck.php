<?php
require_once __DIR__ . '/../Models/Lojista.php';
require_once __DIR__ . '/../Models/Plano.php';

class LimitCheck {
    public static function podeCriarEncarte(int $lojistaId): array {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) {
            return ['pode' => false, 'erro' => 'Lojista não encontrado'];
        }

        $limiteCustom = json_decode($lojista['limite_custom'] ?? 'null', true);
        
        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        if (!$plano) {
            return ['pode' => false, 'erro' => 'Plano não encontrado'];
        }

        $limite = $limiteCustom['encartes'] ?? $plano['limite_encartes'];
        $consumo = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
        $usados = $consumo['encartes_usados'] ?? 0;

        if ($limite !== -1 && $usados >= $limite) {
            return [
                'pode' => false,
                'erro' => 'Limite de encartes atingido',
                'usados' => $usados,
                'limite' => $limite
            ];
        }

        return [
            'pode' => true,
            'usados' => $usados,
            'limite' => $limite === -1 ? 'ilimitado' : $limite,
            'restantes' => $limite === -1 ? 'ilimitado' : ($limite - $usados)
        ];
    }

    public static function podeEnviarPush(int $lojistaId): array {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) {
            return ['pode' => false, 'erro' => 'Lojista não encontrado'];
        }

        $limiteCustom = json_decode($lojista['limite_custom'] ?? 'null', true);
        
        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        if (!$plano) {
            return ['pode' => false, 'erro' => 'Plano não encontrado'];
        }

        $limite = $limiteCustom['push'] ?? $plano['limite_notificacoes_mes'];
        $consumo = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
        $enviados = $consumo['push_enviados_mes'] ?? 0;

        if ($enviados >= $limite) {
            return [
                'pode' => false,
                'erro' => 'Limite de notificações atingido',
                'usados' => $enviados,
                'limite' => $limite
            ];
        }

        return [
            'pode' => true,
            'usados' => $enviados,
            'limite' => $limite,
            'restantes' => $limite - $enviados
        ];
    }

    public static function podeAdicionarImagem(int $lojistaId, int $qtdAtual): array {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) {
            return ['pode' => false, 'erro' => 'Lojista não encontrado'];
        }

        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        if (!$plano) {
            return ['pode' => false, 'erro' => 'Plano não encontrado'];
        }

        $limite = $plano['limite_imagens_por_galeria'];

        if ($limite !== -1 && $qtdAtual >= $limite) {
            return [
                'pode' => false,
                'erro' => 'Limite de imagens por galeria atingido',
                'usados' => $qtdAtual,
                'limite' => $limite
            ];
        }

        return [
            'pode' => true,
            'usados' => $qtdAtual,
            'limite' => $limite === -1 ? 'ilimitado' : $limite,
            'restantes' => $limite === -1 ? 'ilimitado' : ($limite - $qtdAtual)
        ];
    }

    public static function podeFazerMapa(int $lojistaId): bool {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) return false;

        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        return $plano && $plano['permite_mapa'];
    }

    public static function podeVerEstatisticasAvancadas(int $lojistaId): bool {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) return false;

        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        return $plano && $plano['permite_estatisticas_avancadas'];
    }

    public static function podeExportar(int $lojistaId): bool {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);

        if (!$lojista) return false;

        $planoModel = new Plano();
        $plano = $planoModel->find($lojista['plano_id']);

        return $plano && $plano['permite_exportacao'];
    }
}