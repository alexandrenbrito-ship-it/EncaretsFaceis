<?php
require_once __DIR__ . '/../Models/ClientePwa.php';

class PushController {
    private ClientePwa $model;

    public function __construct() {
        $this->model = new ClientePwa();
    }

    public function inscriar(int $lojistaId, array $subscription): array {
        $endpoint = $subscription['endpoint'] ?? '';
        $keys = $subscription['keys'] ?? [];
        
        if (empty($endpoint)) {
            return ['sucesso' => false, 'erro' => 'Endpoint inválido'];
        }

        $existente = $this->model->findByEndpoint($lojistaId, $endpoint);
        
        if ($existente) {
            return ['sucesso' => true, 'mensagem' => 'Já inscrito'];
        }

        $geo = $this->obterGeolocalizacao();
        
        $id = $this->model->create([
            'lojista_id' => $lojistaId,
            'endpoint_push' => $endpoint,
            'push_p256dh' => $keys['p256dh'] ?? '',
            'push_auth' => $keys['auth'] ?? '',
            'cidade' => $geo['cidade'] ?? null,
            'estado' => $geo['estado'] ?? null,
            'dispositivo' => $this->detectarDispositivo(),
            'ativo' => 1
        ]);

        return ['sucesso' => true, 'id' => $id];
    }

    public function listarInscritos(int $lojistaId, ?string $cidade = null): array {
        return $this->model->getPorLojista($lojistaId, $cidade);
    }

    public function contarInscritos(int $lojistaId): int {
        return $this->model->countPorLojista($lojistaId);
    }

    public function desinscrever(int $id): bool {
        return $this->model->update($id, ['ativo' => 0]);
    }

    private function obterGeolocalizacao(): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['cidade' => null, 'estado' => null];
        }

        try {
            $response = @file_get_contents('https://ip-api.com/json/' . $ip);
            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'success') {
                return [
                    'cidade' => $data['city'] ?? null,
                    'estado' => $data['region'] ?? null
                ];
            }
        } catch (Exception $e) {
        }

        return ['cidade' => null, 'estado' => null];
    }

    private function detectarDispositivo(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/mobile/i', $ua)) return 'mobile';
        if (preg_match('/tablet/i', $ua)) return 'tablet';
        return 'desktop';
    }

    public function podeEnviar(int $lojistaId): array {
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

        $limite = $plano['limite_notificacoes_mes'];
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
            'restantes' => $limite - $enviados
        ];
    }

    public function registrarEnvio(int $lojistaId): bool {
        $lojistaModel = new Lojista();
        $lojista = $lojistaModel->find($lojistaId);
        
        $consumo = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
        $consumo['push_enviados_mes'] = ($consumo['push_enviados_mes'] ?? 0) + 1;
        
        return $lojistaModel->update($lojistaId, [
            'recursos_consumidos' => json_encode($consumo)
        ]);
    }
}