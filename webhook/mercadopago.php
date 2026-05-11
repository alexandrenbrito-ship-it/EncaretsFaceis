<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Requisição inválida']);
    exit;
}

try {
    $db = Database::getConnection();

    if ($data['type'] === 'payment') {
        $paymentId = $data['data']['id'] ?? null;
        
        if (!$paymentId) {
            echo json_encode(['erro' => 'Payment ID não encontrado']);
            exit;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments/' . $paymentId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $payment = json_decode($response, true);

        if (!$payment || !isset($payment['external_reference'])) {
            echo json_encode(['erro' => 'Pagamento não encontrado']);
            exit;
        }

        $lojistaId = (int) $payment['external_reference'];

        $stmt = $db->prepare("
            INSERT INTO enc_pagamentos 
            (lojista_id, plano_id, mp_payment_id, valor, status, dados_webhook) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $lojistaId,
            1,
            $paymentId,
            $payment['transaction_amount'],
            $payment['status'],
            json_encode($payment)
        ]);

        if ($payment['status'] === 'approved') {
            $stmt = $db->prepare("
                UPDATE enc_lojistas 
                SET status_assinatura = 'ativa', data_validade = DATE_ADD(NOW(), INTERVAL 1 MONTH)
                WHERE id = ?
            ");
            $stmt->execute([$lojistaId]);
        }

        $stmt = $db->prepare("
            INSERT INTO enc_logs_atividade (lojista_id, tipo, acao, descricao, ip) 
            VALUES (?, 'webhook', 'pagamento_recebido', ?, ?)
        ");
        $stmt->execute([$lojistaId, 'Payment ID: ' . $paymentId, $_SERVER['REMOTE_ADDR']]);

        echo json_encode(['sucesso' => true]);
    }
    elseif ($data['type'] === 'subscription_prepaid' || $data['type'] === 'subscription_authorized_payment') {
        $topic = $data['topic'] ?? '';
        $subscriptionId = $data['data']['id'] ?? '';

        if (empty($subscriptionId)) {
            echo json_encode(['erro' => 'Subscription ID não encontrado']);
            exit;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/preapproval_plan/search?status=all&id=' . $subscriptionId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $subscription = json_decode($response, true);

        if (!$subscription || !isset($subscription['results'][0])) {
            echo json_encode(['erro' => 'Assinatura não encontrada']);
            exit;
        }

        $subscriptionData = $subscription['results'][0];
        
        $stmt = $db->prepare("
            UPDATE enc_lojistas 
            SET status_assinatura = 'ativa', 
                mp_subscription_id = ?,
                data_validade = ?
            WHERE mp_subscription_id = ?
        ");
        $stmt->execute([
            $subscriptionId,
            $subscriptionData['next_billing_date'] ?? date('Y-m-d', strtotime('+30 days')),
            $subscriptionId
        ]);

        echo json_encode(['sucesso' => true]);
    }
    elseif ($data['type'] === 'subscription') {
        $topic = $data['topic'] ?? '';
        $subscriptionId = $data['data']['id'] ?? '';

        if ($topic === 'payment') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments/' . $subscriptionId);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . MP_ACCESS_TOKEN
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $payment = json_decode($response, true);

            if ($payment && $payment['status'] === 'approved') {
                $stmt = $db->prepare("
                    UPDATE enc_lojistas 
                    SET status_assinatura = 'ativa', data_validade = DATE_ADD(NOW(), INTERVAL 1 MONTH)
                    WHERE mp_subscription_id = ?
                ");
                $stmt->execute([$payment['preapproval_id'] ?? '']);
            }
        }

        echo json_encode(['sucesso' => true]);
    }
    else {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Tipo de webhook não tratado']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}