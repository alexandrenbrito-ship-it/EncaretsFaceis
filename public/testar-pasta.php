<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "=== TESTE UPLOAD ===\n\n";

echo "1. Pasta uploads existe?\n";
$basePath = '/home/u264329520/domains/encartesfaceis.online/public_html';
$uploadsPath = $basePath . '/assets/uploads';
echo "Path: $uploadsPath\n";
echo "Existe: " . (is_dir($uploadsPath) ? "SIM\n" : "NAO\n");

if (is_dir($uploadsPath)) {
    echo "Permissao: " . substr(sprintf('%o', fileperms($uploadsPath)), -3) . "\n";
}

echo "\n2. Pasta lojista_1 existe?\n";
$lojistaPath = $uploadsPath . '/lojista_1';
echo "Path: $lojistaPath\n";
echo "Existe: " . (is_dir($lojistaPath) ? "SIM\n" : "NAO\n");

if (!is_dir($lojistaPath)) {
    echo "Criando pasta...\n";
    if (mkdir($lojistaPath, 0755, true)) {
        echo "Criada com sucesso!\n";
    } else {
        echo "ERRO ao criar!\n";
    }
}

echo "\n3. Testando writing...\n";
$testFile = $lojistaPath . '/test.txt';
if (file_put_contents($testFile, 'test')) {
    echo "Arquivo criado com sucesso!\n";
    unlink($testFile);
} else {
    echo "ERRO ao criar arquivo!\n";
}

echo "\n=== FIM ===";