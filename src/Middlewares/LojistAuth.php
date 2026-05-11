<?php
class LojistAuth {
    public static function verificar(): void {
        session_start();
        
        if (!isset($_SESSION['lojista_id'])) {
            header('Location: /encartes/lojista/login.php');
            exit;
        }
    }

    public static function verificarJson(): array {
        session_start();
        
        if (!isset($_SESSION['lojista_id'])) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        return [
            'lojista_id' => $_SESSION['lojista_id'],
            'lojista_nome' => $_SESSION['lojista_nome'] ?? '',
            'lojista_subdominio' => $_SESSION['lojista_subdominio'] ?? '',
            'plano_id' => $_SESSION['lojista_plano_id'] ?? 0
        ];
    }

    public static function seNaoLogadoRedirecionar(): void {
        session_start();
        
        if (!isset($_SESSION['lojista_id'])) {
            header('Location: /encartes/lojista/login.php');
            exit;
        }
    }

    public static function getLojistaId(): int {
        return (int)($_SESSION['lojista_id'] ?? 0);
    }

    public static function getLojistaSubdominio(): string {
        return $_SESSION['lojista_subdominio'] ?? '';
    }
}