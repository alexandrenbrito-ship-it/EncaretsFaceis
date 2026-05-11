<?php
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            if (!defined('DB_HOST')) {
                throw new Exception('Configurações do banco não definidas. Execute o instalador.');
            }

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$instance;
    }

    public static function testConnection(string $host, string $dbname, string $user, string $pass): array {
        try {
            $dsn = "mysql:host=" . $host . ";dbname=" . $dbname . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return ['sucesso' => true, 'mensagem' => 'Conexão estabelecida com sucesso!'];
        } catch (PDOException $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
}