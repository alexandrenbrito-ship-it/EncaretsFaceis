<?php
class Auth {
    public static function iniciarSessao(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function gerarTokenCsrf(): string {
        self::iniciarSessao();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    public static function validarTokenCsrf(string $token): bool {
        self::iniciarSessao();
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function verificarAdmin(): bool {
        self::iniciarSessao();
        return isset($_SESSION['admin_id']);
    }

    public static function verificarLojista(): bool {
        self::iniciarSessao();
        return isset($_SESSION['lojista_id']);
    }

    public static function getAdminId(): ?int {
        self::iniciarSessao();
        return $_SESSION['admin_id'] ?? null;
    }

    public static function getLojistaId(): ?int {
        self::iniciarSessao();
        return $_SESSION['lojista_id'] ?? null;
    }

    public static function destruirSessao(): void {
        self::iniciarSessao();
        session_destroy();
    }

    public static function regenerarId(): void {
        self::iniciarSessao();
        session_regenerate_id(true);
    }

    public static function gerarHash(string $senha): string {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    public static function verificarSenha(string $senha, string $hash): bool {
        return password_verify($senha, $hash);
    }

    public static function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function sanitizar(string $texto): string {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}