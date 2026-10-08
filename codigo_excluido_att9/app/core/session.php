<?php

function iniciarSessao(): void {
    if (session_status() === PHP_SESSION_NONE){
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }
}

function login(array $usuario): void {
    iniciarSessao();

    // Regenera o ID de sessão a cada login — evita fixação de sessão
    // (um dos pontos de segurança que veremos com mais detalhe na Fase 2).
    session_regenerate_id(true);

    $_SESSION = ["usuario" => [
        "idUsuario" => $usuario["idUsuario"],
        "nome" => $usuario["nome"],
        "email" => $usuario["email"],
        "perfilAcesso" => $usuario["perfilAcesso"] // instrutor ou aluno
    ]];
}

function logout(): void {
    iniciarSessao();
    $_SESSION = [];
    session_destroy();
}

function dadosUsuarioLogado(): ?array {
    iniciarSessao();
    
    return $_SESSION['usuario'] ?? null;
}

function usuarioLogado(): ?array {
    return dadosUsuarioLogado() ?? null;
}

function solicitarLogin(): void {
    if(!usuarioLogado()){
        header('Location: /?rota=auth/login');
        exit();
    }
}

function solicitarPerfilDeAcesso(string $perfil): void {
    solicitarLogin();

    if ($_SESSION['perfilAcesso'] !== $perfil){
        http_response_code(403);
        echo "Você não tem acesso a essa página!";
        exit();
    }
}

?>