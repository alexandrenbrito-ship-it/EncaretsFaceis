<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Configuração</h1>";

echo "<h2>1. Verificando config.php</h2>";
if (file_exists(__DIR__ . '/config/config.php')) {
    echo "<p style='color:green'>✓ config.php existe</p>";
    require_once __DIR__ . '/config/config.php';
    echo "<pre>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "\n";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "\n";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDO') . "\n";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NÃO DEFINIDO') . "\n";
    echo "</pre>";
} else {
    echo "<p style='color:red'>✗ config.php NÃO existe - Execute o instalador</p>";
    exit;
}

echo "<h2>2. Testando conexão com banco</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getConnection();
    echo "<p style='color:green'>✓ Conexão OK</p>";
    
    echo "<h2>3. Verificando tabelas</h2>";
    $tables = $db->query("SHOW TABLES")->fetchAll();
    echo "Tabelas encontradas: " . count($tables) . "<br>";
    foreach ($tables as $table) {
        echo "- " . $table[0] . "<br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ ERRO: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Arquivos PHP</h2>";
$files = glob(__DIR__ . '/*.php');
echo "Arquivos na raiz: " . count($files) . "<br>";
foreach ($files as $f) {
    echo "- " . basename($f) . "<br>";
}