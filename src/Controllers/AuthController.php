<?php
class AuthController {
    public static function loginAdmin(string $email, string $senha): array {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findBy('email', $email);

        if (!$usuario || $usuario['tipo'] !== 'admin' || !$usuario['ativo']) {
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas'];
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            return ['sucesso' => false, 'erro' => 'Senha incorreta'];
        }

        $_SESSION['admin_id'] = $usuario['id'];
        $_SESSION['admin_nome'] = $usuario['nome'];
        $_SESSION['admin_email'] = $usuario['email'];
        session_regenerate_id(true);

        $usuarioModel->update($usuario['id'], ['ultimo_acesso' => date('Y-m-d H:i:s')]);

        return ['sucesso' => true];
    }

    public static function loginLojista(string $email, string $senha): array {
        $usuarioModel = new Usuario();
        $lojistaModel = new Lojista();

        $usuario = $usuarioModel->findBy('email', $email);

        if (!$usuario || $usuario['tipo'] !== 'lojista' || !$usuario['ativo']) {
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas'];
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            return ['sucesso' => false, 'erro' => 'Senha incorreta'];
        }

        $lojista = $lojistaModel->getByUsuario($usuario['id']);

        if (!$lojista) {
            return ['sucesso' => false, 'erro' => 'Lojista não encontrado'];
        }

        $_SESSION['lojista_id'] = $lojista['id'];
        $_SESSION['lojista_nome'] = $lojista['nome_loja'];
        $_SESSION['lojista_subdominio'] = $lojista['subdominio'];
        $_SESSION['lojista_plano_id'] = $lojista['plano_id'];
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        session_regenerate_id(true);
        $usuarioModel->update($usuario['id'], ['ultimo_acesso' => date('Y-m-d H:i:s')]);

        return ['sucesso' => true];
    }

    public static function registrarLojista(array $dados): array {
        $usuarioModel = new Usuario();
        $lojistaModel = new Lojista();

        if ($usuarioModel->verificarEmail($dados['email'])) {
            return ['sucesso' => false, 'erro' => 'E-mail já cadastrado'];
        }

        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $usuarioId = $usuarioModel->create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'senha_hash' => $senhaHash,
            'tipo' => 'lojista',
            'ativo' => 1
        ]);

        $subdominio = $lojistaModel->gerarSubdominio($dados['nome_loja']);
        $lojistaId = $lojistaModel->create([
            'usuario_id' => $usuarioId,
            'plano_id' => $dados['plano_id'],
            'nome_loja' => $dados['nome_loja'],
            'subdominio' => $subdominio,
            'status_assinatura' => 'trial',
            'data_inicio' => date('Y-m-d'),
            'data_validade' => date('Y-m-d', strtotime('+7 days')),
            'recursos_consumidos' => json_encode(['encartes_usados' => 0, 'push_enviados_mes' => 0])
        ]);

        $_SESSION['lojista_id'] = $lojistaId;
        $_SESSION['lojista_nome'] = $dados['nome_loja'];
        $_SESSION['lojista_subdominio'] = $subdominio;
        $_SESSION['lojista_plano_id'] = $dados['plano_id'];
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_nome'] = $dados['nome'];

        return ['sucesso' => true, 'lojista_id' => $lojistaId];
    }

    public static function logout(): void {
        session_destroy();
        header('Location: /');
        exit;
    }

    public static function verificarSessaoAdmin(): bool {
        return isset($_SESSION['admin_id']);
    }

    public static function verificarSessaoLojista(): bool {
        return isset($_SESSION['lojista_id']);
    }
}