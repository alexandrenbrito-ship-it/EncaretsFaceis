<?php
class Functions {
    public static function slug(string $texto): string {
        $texto = strtolower(trim($texto));
        $texto = preg_replace('/[^a-z0-9-]/', '-', $texto);
        $texto = preg_replace('/-+/', '-', $texto);
        $texto = trim($texto, '-');
        return $texto;
    }

    public static function limitarTexto(string $texto, int $limite = 100, string $sufixo = '...'): string {
        if (strlen($texto) <= $limite) {
            return $texto;
        }
        return substr($texto, 0, $limite) . $sufixo;
    }

    public static function formatarTelefone(string $telefone): string {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        } elseif (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
        }
        
        return $telefone;
    }

    public static function formatarPreco(float $preco): string {
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }

    public static function formatarData(string $data, string $formato = 'd/m/Y'): string {
        $date = new DateTime($data);
        return $date->format($formato);
    }

    public static function tempoRelativo(string $data): string {
        $date = new DateTime($data);
        $now = new DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y . ' ano' . ($diff->y > 1 ? 's' : '') . ' atrás';
        }
        if ($diff->m > 0) {
            return $diff->m . ' mês' . ($diff->m > 1 ? 'es' : '') . ' atrás';
        }
        if ($diff->d > 0) {
            return $diff->d . ' dia' . ($diff->d > 1 ? 's' : '') . ' atrás';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
        }
        
        return 'agora';
    }

    public static function gerarToken(int $tamanho = 32): string {
        return bin2hex(random_bytes($tamanho / 2));
    }

    public static function validarUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function get_client_ip(): string {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        return $ip;
    }

    public static function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public static function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public static function randomPassword(int $length = 8): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
}