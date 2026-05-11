<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '');
    define('BASE_URL', 'https://seudominio.com');
}

if (file_exists(__DIR__ . '/config/config.php')) {
    header('Location: ' . BASE_PATH . '/landing-page/');
} else {
    header('Location: ' . BASE_PATH . '/install/');
}
exit;