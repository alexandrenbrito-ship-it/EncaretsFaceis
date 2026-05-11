<?php
class Logger {
    private static string $path = __DIR__ . '/../../logs/';

    public static function info(string $mensagem, array $contexto = []): void {
        self::log('INFO', $mensagem, $contexto);
    }

    public static function error(string $mensagem, array $contexto = []): void {
        self::log('ERROR', $mensagem, $contexto);
    }

    public static function warning(string $mensagem, array $contexto = []): void {
        self::log('WARNING', $mensagem, $contexto);
    }

    public static function debug(string $mensagem, array $contexto = []): void {
        if (APP_ENV === 'development') {
            self::log('DEBUG', $mensagem, $contexto);
        }
    }

    public static function log(string $nivel, string $mensagem, array $contexto = []): void {
        $data = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        $contextoStr = !empty($contexto) ? ' ' . json_encode($contexto) : '';

        $linha = "[$data] [$nivel] [$ip] $uri | $mensagem$contextoStr\n";

        $arquivo = self::$path . date('Y-m-d') . '.log';
        
        if (!is_dir(self::$path)) {
            mkdir(self::$path, 0755, true);
        }

        file_put_contents($arquivo, $linha, FILE_APPEND);
    }

    public static function registrarAtividade(int $usuarioId, int $lojistaId, string $tipo, string $acao, ?string $descricao = null): void {
        try {
            $db = Database::getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO enc_logs_atividade (usuario_id, lojista_id, tipo, acao, descricao, ip)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuarioId,
                $lojistaId,
                $tipo,
                $acao,
                $descricao,
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
        } catch (Exception $e) {
            self::error('Erro ao registrar atividade: ' . $e->getMessage());
        }
    }

    public static function getLogs(string $data = null): array {
        $arquivo = self::$path . ($data ?? date('Y-m-d')) . '.log';
        
        if (!file_exists($arquivo)) {
            return [];
        }

        $linhas = file($arquivo);
        return array_reverse($linhas);
    }
}