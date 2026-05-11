<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Helpers/PushNotifier.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lojista_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado']);
    exit;
}

$lojistaId = $_SESSION['lojista_id'];
$data = json_decode(file_get_contents('php://input'), true);

$titulo = $data['titulo'] ?? '';
$mensagem = $data['mensagem'] ?? '';
$urlDestino = $data['url'] ?? null;
$cidade = $data['cidade'] ?? null;

if (empty($titulo) || empty($mensagem)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Título e mensagem são obrigatórios']);
    exit;
}

try {
    $db = Database::getConnection();

    $stmt = $db->prepare("
        SELECT plano_id FROM enc_lojistas WHERE id = ?
    ");
    $stmt->execute([$lojistaId]);
    $lojista = $stmt->fetch();

    $stmt = $db->prepare("
        SELECT limite_notificacoes_mes FROM enc_planos WHERE id = ?
    ");
    $stmt->execute([$lojista['plano_id']]);
    $plano = $stmt->fetch();

    $limite = $plano['limite_notificacoes_mes'];

    $stmt = $db->query("
        SELECT COUNT(*) as total FROM enc_notificacoes_push 
        WHERE lojista_id = $lojistaId 
        AND MONTH(data_envio) = MONTH(CURRENT_DATE())
    ");
    $enviados = $stmt->fetch()['total'];

    if ($enviados >= $limite) {
        echo json_encode(['sucesso' => false, 'erro' => 'Limite de notificações atingido']);
        exit;
    }

    $sql = "SELECT endpoint_push, push_p256dh, push_auth FROM enc_clientes_pwa WHERE lojista_id = ? AND ativo = 1";
    $params = [$lojistaId];

    if ($cidade) {
        $sql .= " AND cidade = ?";
        $params[] = $cidade;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $inscritos = $stmt->fetchAll();

    if (empty($inscritos)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Nenhum inscrito encontrado']);
        exit;
    }

    $notifier = new PushNotifier();
    $sucesso = 0;
    $falhas = 0;

    foreach ($inscritos as $inscrito) {
        $subscription = [
            'endpoint' => $inscrito['endpoint_push'],
            'keys' => [
                'p256dh' => $inscrito['push_p256dh'],
                'auth' => $inscrito['push_auth']
            ]
        ];

        if ($notifier->enviar($subscription, $titulo, $mensagem, $urlDestino)) {
            $sucesso++;
        } else {
            $falhas++;
        }
    }

    $stmt = $db->prepare("
        INSERT INTO enc_notificacoes_push (lojista_id, titulo, mensagem, url_destino, segmento_cidade, total_enviado, total_falhas, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'concluido')
    ");
    $stmt->execute([$lojistaId, $titulo, $mensagem, $urlDestino, $cidade, $sucesso, $falhas]);

    $consumo = json_decode($lojista['recursos_consumidos'] ?? '{}', true);
    $consumo['push_enviados_mes'] = ($consumo['push_enviados_mes'] ?? 0) + $sucesso;

    $stmt = $db->prepare("UPDATE enc_lojistas SET recursos_consumidos = ? WHERE id = ?");
    $stmt->execute([json_encode($consumo), $lojistaId]);

    echo json_encode([
        'sucesso' => true,
        'enviados' => $sucesso,
        'falhas' => $falhas
    ]);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}