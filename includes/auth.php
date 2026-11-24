<?php
// includes/auth.php - sessão, papéis e proteção de rotas
require_once __DIR__ . '/helpers.php';

function usuario_atual(): ?array {
    if (empty($_SESSION['uid'])) return null;
    static $u = false;
    if ($u === false) {
        $st = db()->prepare('SELECT id, nome, email, papel FROM usuarios WHERE id = ? AND ativo = 1');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch() ?: null;
    }
    return $u;
}

function logar_usuario(int $id): void {
    session_regenerate_id(true);          // evita session fixation
    $_SESSION['uid'] = $id;
}

function sair(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function exigir_login(): array {
    $u = usuario_atual();
    if (!$u) { flash('erro', 'Faça login para continuar.'); redirecionar('usuarios/login.php'); }
    return $u;
}

/** @param string[] $papeis ex.: ['admin'] ou ['admin','atendente'] */
function exigir_papel(array $papeis): array {
    $u = exigir_login();
    if (!in_array($u['papel'], $papeis, true)) { http_response_code(403); exit('Acesso negado.'); }
    return $u;
}

// Bloqueio por força bruta: 5 falhas em 15 min por e-mail+IP
function login_bloqueado(string $email): bool {
    $st = db()->prepare("SELECT COUNT(*) FROM tentativas_login WHERE email = ? AND ip = ? AND criado_em > (NOW() - INTERVAL 15 MINUTE)");
    $st->execute([$email, ip_cliente()]);
    return (int)$st->fetchColumn() >= 5;
}
function registrar_falha(string $email): void {
    db()->prepare('INSERT INTO tentativas_login (email, ip) VALUES (?, ?)')->execute([$email, ip_cliente()]);
}
function limpar_falhas(string $email): void {
    db()->prepare('DELETE FROM tentativas_login WHERE email = ? AND ip = ?')->execute([$email, ip_cliente()]);
}
