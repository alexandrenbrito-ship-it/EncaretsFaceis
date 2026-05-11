<?php
class PushNotifier {
    private string $vapidPublic;
    private string $vapidPrivate;
    private string $subject;

    public function __construct() {
        $this->vapidPublic = '';
        $this->vapidPrivate = '';
        $this->subject = 'mailto:admin@encartes.com';
    }

    public function setVapidKeys(string $public, string $private): void {
        $this->vapidPublic = $public;
        $this->vapidPrivate = $private;
    }

    public function enviar(array $subscription, string $titulo, string $mensagem, ?string $url = null): bool {
        $endpoint = $subscription['endpoint'] ?? '';
        
        if (empty($endpoint)) {
            return false;
        }

        $payload = json_encode([
            'title' => $titulo,
            'body' => $mensagem,
            'icon' => '/assets/img/icon-192.png',
            'badge' => '/assets/img/icon-192.png',
            'data' => [
                'url' => $url ?? '/',
                'timestamp' => time()
            ],
            'vibrate' => [100, 50, 100]
        ]);

        $headers = [
            'Content-Type: application/json',
            'Authorization: vapid t=' . $this->vapidPublic
        ];

        $parts = parse_url($endpoint);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
            'TTL: 86400',
            'Authorization: vapid t=' . $this->vapidPublic
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return in_array($httpCode, [200, 201, 202]);
    }

    public function enviarParaVarios(array $subscriptions, string $titulo, string $mensagem, ?string $url = null): array {
        $resultados = [
            'sucesso' => 0,
            'falhas' => 0
        ];

        foreach ($subscriptions as $subscription) {
            if ($this->enviar($subscription, $titulo, $mensagem, $url)) {
                $resultados['sucesso']++;
            } else {
                $resultados['falhas']++;
            }
        }

        return $resultados;
    }

    public static function urlBase64ToUint8Array(string $base64String): string {
        $padding = '=' . str_repeat('=', (4 - strlen($base64String) % 4) % 4);
        $base64 = str_replace('-', '+', str_replace('_', '/', $base64String . $padding));
        $rawData = base64_decode($base64);
        return $rawData;
    }

    public static function generateVapidKeys(): array {
        $keys = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEY_RSA,
        ]);

        openssl_pkey_export($keys, $privateKey);

        $details = openssl_pkey_get_details($keys);
        $publicKey = $details['key'];

        return [
            'public' => self::base64UrlEncode($publicKey),
            'private' => self::base64UrlEncode($privateKey)
        ];
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}