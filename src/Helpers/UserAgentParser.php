<?php
class UserAgentParser {
    public static function getDispositivo(string $userAgent): string {
        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/tablet/i', $userAgent) || preg_match('/ipad/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    public static function getNavegador(string $userAgent): string {
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/edg/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/opera|opr/i', $userAgent)) {
            return 'Opera';
        }
        if (preg_match('/msie|trident/i', $userAgent)) {
            return 'Internet Explorer';
        }
        return 'Outro';
    }

    public static function getSistemaOperacional(string $userAgent): string {
        if (preg_match('/windows nt 10/i', $userAgent)) {
            return 'Windows 10';
        }
        if (preg_match('/windows nt 6\.3/i', $userAgent)) {
            return 'Windows 8.1';
        }
        if (preg_match('/windows nt 6\.1/i', $userAgent)) {
            return 'Windows 7';
        }
        if (preg_match('/mac os x/i', $userAgent)) {
            return 'macOS';
        }
        if (preg_match('/android/i', $userAgent)) {
            return 'Android';
        }
        if (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            return 'iOS';
        }
        if (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }
        return 'Outro';
    }

    public static function getNavegadorVersao(string $userAgent): ?string {
        if (preg_match('/chrome\/(\d+)/i', $userAgent, $matches)) {
            return $matches[1];
        }
        if (preg_match('/firefox\/(\d+)/i', $userAgent, $matches)) {
            return $matches[1];
        }
        if (preg_match('/safari\/(\d+)/i', $userAgent, $matches)) {
            return $matches[1];
        }
        if (preg_match('/edg\/(\d+)/i', $userAgent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function isMobile(string $userAgent): bool {
        return preg_match('/mobile/i', $userAgent) === 1;
    }

    public static function isBot(string $userAgent): bool {
        $bots = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 
            'google', 'yandex', 'bing', 'yahoo', 'duckduck'
        ];
        
        $userAgentLower = strtolower($userAgent);
        
        foreach ($bots as $bot) {
            if (strpos($userAgentLower, $bot) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public static function parse(string $userAgent): array {
        return [
            'dispositivo' => self::getDispositivo($userAgent),
            'navegador' => self::getNavegador($userAgent),
            'navegador_versao' => self::getNavegadorVersao($userAgent),
            'sistema_operacional' => self::getSistemaOperacional($userAgent),
            'is_mobile' => self::isMobile($userAgent),
            'is_bot' => self::isBot($userAgent)
        ];
    }
}