<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u264329520_encartes');
define('DB_USER', 'u264329520_encartes');
define('DB_PASS', 'Encartes2024@');
define('DB_PREFIX', 'enc_');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Encartes Fáceis');
define('APP_URL', 'https://encartesfaceis.online');
define('APP_ENV', 'production');

define('SESSION_NAME', 'enc_session');
define('SESSION_LIFETIME', 7200);

define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

define('GEOIP_API', 'https://ip-api.com/json/');
define('NOMINATIM_API', 'https://nominatim.openstreetmap.org/reverse');

define('MP_PUBLIC_KEY', '');
define('MP_ACCESS_TOKEN', '');
define('MP_MODO', 'sandbox');

define('APP_DOMAIN', parse_url(APP_URL, PHP_URL_HOST));
