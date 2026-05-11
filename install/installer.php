<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
    exit;
}

$acao = $_POST['acao'] ?? '';

if ($acao === 'testar_conexao') {
    $host = $_POST['db_host'] ?? '';
    $dbname = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';

    try {
        $dsn = "mysql:host=" . $host . ";dbname=" . $dbname . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo json_encode(['sucesso' => true, 'mensagem' => 'Conexão estabelecida com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

if ($acao === 'instalar') {
    $dbHost = $_POST['db_host'] ?? '';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $dbPrefix = $_POST['db_prefix'] ?? 'enc_';

    $appUrl = $_POST['app_url'] ?? '';
    $appName = $_POST['app_name'] ?? 'Encartes Pro';
    $adminName = $_POST['admin_name'] ?? 'Administrador';
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminSenha = $_POST['admin_senha'] ?? '';

    $mpPublicKey = $_POST['mp_public_key'] ?? '';
    $mpAccessToken = $_POST['mp_access_token'] ?? '';
    $mpModo = $_POST['mp_modo'] ?? 'sandbox';

    if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($appUrl) || empty($adminEmail) || empty($adminSenha)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios']);
        exit;
    }

    try {
        $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $schema = file_get_contents(__DIR__ . '/schema.sql');
        $schema = str_replace('enc_', $dbPrefix, $schema);
        $pdo->exec($schema);

        $senhaHash = password_hash($adminSenha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$adminName, $adminEmail, $senhaHash]);

        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Básico', 'Ideal para começar', 29.90, 5, 500, 10, 1, 0, 0, 0, 1]);
        
        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Profissional', 'Para lojas em crescimento', 79.90, 20, 2000, 50, 1, 1, 0, 1, 2]);
        
        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "planos (nome, descricao, preco_mensal, limite_encartes, limite_notificacoes_mes, limite_imagens_por_galeria, permite_mapa, permite_estatisticas_avancadas, permite_exportacao, destaque, ordem_exibicao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Enterprise', 'Sem limites, controle total', 199.90, -1, 10000, -1, 1, 1, 1, 0, 3]);

        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "configuracoes (chave, valor, descricao) VALUES (?, ?, ?)");
        $stmt->execute(['sistema_nome', $appName, 'Nome do sistema']);
        $stmt->execute(['url_base', $appUrl, 'URL raiz']);
        $stmt->execute(['mp_public_key', $mpPublicKey, 'Chave pública MP']);
        $stmt->execute(['mp_access_token', $mpAccessToken, 'Token MP']);
        $stmt->execute(['mp_modo', $mpModo, 'Modo MP']);
        $stmt->execute(['geoip_api', 'https://ip-api.com/json/', 'API de geolocalização']);

        $templateHtml = '<div class="encarte"><header class="enc-header">{{cabecalho_titulo}}</header><section class="enc-produtos">{{produtos}}</section><footer class="enc-rodape">{{rodape_texto}}</footer></div>';
        $templateCss = '.encarte{font-family:Arial,sans-serif;max-width:800px;margin:0 auto}.enc-header{background:{{cor_primaria}};color:#fff;padding:20px;text-align:center}.enc-produtos{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:20px}.enc-rodape{background:#333;color:#fff;padding:10px;text-align:center}';
        $templateConfig = '{"blocos":["cabecalho","produtos","galeria","rodape"],"permite_adicionar_produtos":true,"max_produtos_por_linha":4}';

        $stmt = $pdo->prepare("INSERT INTO " . $dbPrefix . "templates_encarte (nome, descricao, categoria, estrutura_html, estrutura_css, configuracao_blocos) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Supermercado Clássico', 'Layout padrão para supermercados', 'supermercado', $templateHtml, $templateCss, $templateConfig]);

        $configPhp = "<?php
define('DB_HOST', '" . addslashes($dbHost) . "');
define('DB_NAME', '" . addslashes($dbName) . "');
define('DB_USER', '" . addslashes($dbUser) . "');
define('DB_PASS', '" . addslashes($dbPass) . "');
define('DB_PREFIX', '" . addslashes($dbPrefix) . "');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', '" . addslashes($appName) . "');
define('APP_URL', '" . addslashes($appUrl) . "');
define('APP_ENV', 'production');

define('SESSION_NAME', 'enc_session');
define('SESSION_LIFETIME', 7200);

define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

define('GEOIP_API', 'https://ip-api.com/json/');
define('NOMINATIM_API', 'https://nominatim.openstreetmap.org/reverse');

define('MP_PUBLIC_KEY', '" . addslashes($mpPublicKey) . "');
define('MP_ACCESS_TOKEN', '" . addslashes($mpAccessToken) . "');
define('MP_MODO', '" . addslashes($mpModo) . "');

define('APP_DOMAIN', parse_url(APP_URL, PHP_URL_HOST));
";

        file_put_contents(__DIR__ . '/../config/config.php', $configPhp);

        file_put_contents(__DIR__ . '/installed.lock', date('Y-m-d H:i:s'));

        echo json_encode([
            'sucesso' => true,
            'admin_url' => $appUrl . '/admin/',
            'admin_email' => $adminEmail
        ]);

    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao instalar: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida']);