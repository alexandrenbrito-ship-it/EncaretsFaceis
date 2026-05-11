<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página Não Encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { background: #16213e; border-radius: 16px; padding: 40px; text-align: center; color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
        <h2 class="mt-4">Página Não Encontrada</h2>
        <p class="text-white-50">A página que você procura não existe ou foi movida.</p>
        <a href="/admin/" class="btn btn-primary mt-3">Ir para Dashboard</a>
    </div>
</body>
</html>