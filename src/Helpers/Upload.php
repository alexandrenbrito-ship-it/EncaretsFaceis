<?php
class UploadHelper {
    private static string $uploadPath = UPLOAD_PATH;
    private static int $maxSize = 5242880; // 5MB
    private static array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function imagem(string $inputName, int $lojistaId): array {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return ['sucesso' => false, 'erro' => 'Nenhum arquivo enviado'];
        }

        $file = $_FILES[$inputName];

        if ($file['size'] > self::$maxSize) {
            return ['sucesso' => false, 'erro' => 'Arquivo muito grande (máx 5MB)'];
        }

        $mimeType = mime_content_type($file['tmp_name']);
        
        if (!in_array($mimeType, self::$allowedTypes)) {
            return ['sucesso' => false, 'erro' => 'Tipo de arquivo não permitido'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, self::$allowedExtensions)) {
            return ['sucesso' => false, 'erro' => 'Extensão não permitida'];
        }

        $lojistaPath = self::$uploadPath . 'lojista_' . $lojistaId;
        
        if (!is_dir($lojistaPath)) {
            mkdir($lojistaPath, 0755, true);
        }

        $newFilename = uniqid('img_') . '.' . $extension;
        $destination = $lojistaPath . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $url = UPLOAD_URL . 'lojista_' . $lojistaId . '/' . $newFilename;
            return ['sucesso' => true, 'url' => $url, 'path' => $destination];
        }

        return ['sucesso' => false, 'erro' => 'Erro ao mover arquivo'];
    }

    public static function excluir(string $filepath): bool {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    public static function getTamanhoPasta(int $lojistaId): int {
        $lojistaPath = self::$uploadPath . 'lojista_' . $lojistaId;
        
        if (!is_dir($lojistaPath)) {
            return 0;
        }

        $total = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($lojistaPath)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $total += $file->getSize();
            }
        }

        return $total;
    }

    public static function formatarBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}