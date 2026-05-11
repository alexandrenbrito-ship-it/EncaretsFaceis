<?php
class Validation {
    public static function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarCpf(string $cpf): bool {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) {
                return false;
            }
        }

        return true;
    }

    public static function validarCnpj(string $cnpj): bool {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        
        if ($cnpj[12] != $digit1) {
            return false;
        }

        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights[$i];
        }
        
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        
        return $cnpj[13] == $digit2;
    }

    public static function validarTelefone(string $telefone): bool {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        return strlen($telefone) >= 10 && strlen($telefone) <= 11;
    }

    public static function validarUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function validarData(string $data, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $data);
        return $d && $d->format($format) === $data;
    }

    public static function validarMinimo(string $valor, int $min): bool {
        return strlen($valor) >= $min;
    }

    public static function validarMaximo(string $valor, int $max): bool {
        return strlen($valor) <= $max;
    }

    public static function validarObrigatorio(mixed $valor): bool {
        if (is_string($valor)) {
            return trim($valor) !== '';
        }
        return $valor !== null;
    }

    public static function validarNumerico(mixed $valor): bool {
        return is_numeric($valor);
    }

    public static function validarArray(mixed $valor): bool {
        return is_array($valor);
    }

    public static function validarJson(string $valor): bool {
        json_decode($valor);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function validarSubdominio(string $subdominio): bool {
        return preg_match('/^[a-z0-9]([a-z0-9-]{0,48}[a-z0-9])?$/', $subdominio) === 1;
    }

    public static function validarSenha(string $senha): array {
        $erros = [];
        
        if (strlen($senha) < 6) {
            $erros[] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if (strlen($senha) > 100) {
            $erros[] = 'Senha muito grande';
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    public static function validarCampo(string $campo, mixed $valor, array $regras): array {
        $erros = [];

        foreach ($regras as $regra => $parametro) {
            switch ($regra) {
                case 'obrigatorio':
                    if (!self::validarObrigatorio($valor)) {
                        $erros[] = $campo . ' é obrigatório';
                    }
                    break;
                case 'email':
                    if (!empty($valor) && !self::validarEmail($valor)) {
                        $erros[] = $campo . ' deve ser um e-mail válido';
                    }
                    break;
                case 'min':
                    if (is_string($valor) && !self::validarMinimo($valor, $parametro)) {
                        $erros[] = $campo . ' deve ter pelo menos ' . $parametro . ' caracteres';
                    }
                    break;
                case 'max':
                    if (is_string($valor) && !self::validarMaximo($valor, $parametro)) {
                        $erros[] = $campo . ' deve ter no máximo ' . $parametro . ' caracteres';
                    }
                    break;
                case 'url':
                    if (!empty($valor) && !self::validarUrl($valor)) {
                        $erros[] = $campo . ' deve ser uma URL válida';
                    }
                    break;
                case 'numerico':
                    if (!empty($valor) && !self::validarNumerico($valor)) {
                        $erros[] = $campo . ' deve ser um número';
                    }
                    break;
            }
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }
}