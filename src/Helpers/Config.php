<?php
class Config {
    private static ?array $cache = null;

    public static function get(string $chave, $default = null) {
        if (self::$cache === null) {
            self::load();
        }

        return self::$cache[$chave] ?? $default;
    }

    public static function set(string $chave, $valor): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO enc_configuracoes (chave, valor) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ");
            $stmt->execute([$chave, $valor]);
            
            self::$cache[$chave] = $valor;
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function all(): array {
        if (self::$cache === null) {
            self::load();
        }
        return self::$cache;
    }

    private static function load(): void {
        if (!defined('DB_PREFIX')) {
            self::$cache = [];
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT chave, valor FROM enc_configuracoes");
            $configs = $stmt->fetchAll();

            self::$cache = [];
            foreach ($configs as $config) {
                self::$cache[$config['chave']] = $config['valor'];
            }
        } catch (Exception $e) {
            self::$cache = [];
        }
    }

    public static function clearCache(): void {
        self::$cache = null;
    }
}