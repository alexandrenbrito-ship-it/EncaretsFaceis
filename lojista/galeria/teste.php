<?php
session_start();

$lojistaId = $_SESSION['lojista_id'] ?? 0;

if (!$lojistaId) {
    header('Location: /lojista/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Galeria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Galeria - ID: <?= $lojistaId ?></h3>
    
    <form action="/api/upload-galeria.php" method="post" enctype="multipart/form-data" class="mb-4">
        <input type="file" name="imagens[]" multiple accept="image/*" class="form-control">
        <button type="submit" class="btn btn-primary mt-2">Enviar</button>
    </form>
    
    <p>Uploads em: /home/u264329520/domains/encartesfaceis.online/public_html/assets/uploads/lojista_<?= $lojistaId ?>/</p>
</body>
</html>