<?php
class AdminAuth {
    public static function verificar(): void {
        session_start();
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function verificarJson(): array {
        session_start();
        
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode(['erro' => 'Não autorizado']);
            exit;
        }
        
        return ['admin_id' => $_SESSION['admin_id'], 'admin_nome' => $_SESSION['admin_nome'] ?? ''];
    }

    public static function seNaoLogadoRedirecionar(): void {
        session_start();
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login.php');
            exit;
        }
    }
}