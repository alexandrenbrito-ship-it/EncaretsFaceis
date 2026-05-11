<?php
class Email {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private bool $smtpAuth;
    private bool $smtpSecure;
    private string $fromEmail;
    private string $fromName;

    public function __construct() {
        $this->host = getenv('SMTP_HOST') ?: '';
        $this->port = (int)(getenv('SMTP_PORT') ?: 587);
        $this->username = getenv('SMTP_USUARIO') ?: '';
        $this->password = getenv('SMTP_SENHA') ?: '';
        $this->smtpAuth = !empty($this->username);
        $this->smtpSecure = false;
        $this->fromEmail = 'noreply@encartes.com';
        $this->fromName = 'Encartes Pro';
    }

    public function setFrom(string $email, string $name): self {
        $this->fromEmail = $email;
        $this->fromName = $name;
        return $this;
    }

    public function send(string $to, string $subject, string $body): bool {
        if (!$this->smtpAuth) {
            return mail($to, $subject, $body);
        }

        $headers = [
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'from' => $this->fromName . ' <' . $this->fromEmail . '>',
            'to' => $to,
            'subject' => $subject,
            'html' => $body
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . getenv('RESEND_API_KEY'),
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public function sendTemplate(string $to, string $subject, string $template, array $data = []): bool {
        $body = $this->parseTemplate($template, $data);
        return $this->send($to, $subject, $body);
    }

    private function parseTemplate(string $template, array $data): string {
        $html = file_get_contents(__DIR__ . '/../templates/emails/' . $template . '.html');
        
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        return $html;
    }

    public static function enviarBoasVindas(string $email, string $nome): bool {
        $mail = new self();
        return $mail->send(
            $email,
            'Bem-vindo ao Encartes Pro!',
            '<h1>Olá, ' . $nome . '!</h1><p>Bem-vindo ao Encartes Pro. Comece a criar seus encartes digitais agora mesmo!</p><a href="' . APP_URL . '/lojista/">Acessar Painel</a>'
        );
    }

    public static function enviarNotificacaoRenovacao(string $email, string $nome, string $dataValidade): bool {
        $mail = new self();
        return $mail->send(
            $email,
            'Sua assinatura está prestes a vencer',
            '<h1>Olá, ' . $nome . '!</h1><p>Sua assinatura vence em ' . $dataValidade . '. Renove agora para não perder o acesso.</p>'
        );
    }
}