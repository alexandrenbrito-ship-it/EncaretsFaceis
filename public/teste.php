<?php
echo "<h1>Teste de Configuração</h1>";

echo "<h2>1. Verificando config.php</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "✓ config.php carregado<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "APP_URL: " . APP_URL . "<br>";
} catch (Exception $e) {
    echo "✗ Erro no config: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Testando conexão</h2>";
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexão OK!<br>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas: " . count($tabelas) . "<br>";
    foreach ($tabelas as $t) {
        echo "- $t<br>";
    }
} catch (PDOException $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Verificando pasta uploads</h2>";
$uploadsPath = dirname(__DIR__) . '/assets/uploads/';
echo "Path: $uploadsPath<br>";
echo "Existe: " . (is_dir($uploadsPath) ? 'SIM' : 'NÃO') . "<br>";
if (!is_dir($uploadsPath)) {
    if (mkdir($uploadsPath, 0755, true)) {
        echo "✓ Criada com sucesso<br>";
    } else {
        echo "✗ Erro ao criar<br>";
    }
}

echo "<h2>Fim do teste</h2>";
echo "<a href='/lojista/login.php'>Testar login</a>";