<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "Upload Path: /home/u264329520/domains/encartesfaceis.online/public_html/assets/uploads/lojista_1/\n";

$targetDir = '/home/u264329520/domains/encartesfaceis.online/public_html/assets/uploads/lojista_1/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

echo "Diretorio existe: " . (is_dir($targetDir) ? "SIM" : "NAO") . "\n";
echo "É gravável: " . (is_writable($targetDir) ? "SIM" : "NAO") . "\n";

if (!empty($_FILES)) {
    foreach ($_FILES['imagens']['name'] as $i => $name) {
        if ($_FILES['imagens']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = 'img_' . time() . '_' . $i . '.' . $ext;
            $dest = $targetDir . $newName;
            
            if (move_uploaded_file($_FILES['imagens']['tmp_name'][$i], $dest)) {
                echo "Sucesso: $newName\n";
            } else {
                echo "Erro ao mover: $name\n";
            }
        }
    }
} else {
    echo "Nenhum arquivo enviado\n";
}