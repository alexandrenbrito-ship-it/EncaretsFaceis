<?php
class GeoIP {
    private static string $apiUrl = 'https://ip-api.com/json/';

    public static function getInfo(string $ip): array {
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
            return [
                'status' => 'fail',
                'country' => null,
                'countryCode' => null,
                'region' => null,
                'regionName' => null,
                'city' => null,
                'lat' => null,
                'lon' => null,
                'timezone' => null,
                'isp' => null,
                'org' => null
            ];
        }

        $url = self::$apiUrl . $ip;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => 'User-Agent: EncartesPro/1.0'
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            return json_decode($response, true) ?: ['status' => 'fail'];
        } catch (Exception $e) {
            return ['status' => 'fail', 'error' => $e->getMessage()];
        }
    }

    public static function getCidade(string $ip): ?string {
        $info = self::getInfo($ip);
        return $info['status'] === 'success' ? ($info['city'] ?? null) : null;
    }

    public static function getEstado(string $ip): ?string {
        $info = self::getInfo($ip);
        return $info['status'] === 'success' ? ($info['regionName'] ?? null) : null;
    }

    public static function getCoordenadas(string $ip): array {
        $info = self::getInfo($ip);
        
        if ($info['status'] === 'success' && isset($info['lat'], $info['lon'])) {
            return [
                'lat' => $info['lat'],
                'lon' => $info['lon']
            ];
        }
        
        return ['lat' => null, 'lon' => null];
    }

    public static function getPais(string $ip): ?string {
        $info = self::getInfo($ip);
        return $info['status'] === 'success' ? ($info['country'] ?? null) : null;
    }

    public static function getCodigoPais(string $ip): ?string {
        $info = self::getInfo($ip);
        return $info['status'] === 'success' ? ($info['countryCode'] ?? null) : null;
    }
}