<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/encartes');
    define('BASE_URL', 'http://localhost' . BASE_PATH);
}

if (file_exists(__DIR__ . '/config/config.php')) {
    header('Location: ' . BASE_PATH . '/landing-page/');
} else {
    header('Location: ' . BASE_PATH . '/install/');
}
exit;