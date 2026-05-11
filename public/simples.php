<?php
echo "Inicio<br>";

try {
    echo "1. Carregando config...<br>";
    require_once __DIR__ . '/../config/config.php';
    echo "2. OK<br>";
    
    echo "3. Carregando database...<br>";
    require_once __DIR__ . '/../config/database.php';
    echo "4. OK<br>";
    
    echo "5. Conectando...<br>";
    $db = Database::getConnection();
    echo "6. OK<br>";
    
    echo "7. Verificando pasta uploads...<br>";
    $uploadsPath = dirname(__DIR__, 2) . '/assets/uploads';
    if (!is_dir($uploadsPath)) {
        mkdir($uploadsPath, 0755, true);
    }
    echo "8. OK - $uploadsPath<br>";
    
    echo "FIM - Tudo OK!";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}