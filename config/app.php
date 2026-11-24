<?php
// config/app.php - bootstrap: configuração, URL automática, sessão segura
define('BASE_PATH', realpath(__DIR__ . '/..'));

function env(string $chave, $padrao = null) {
    static $vars = null;
    if ($vars === null) {
        $arq = BASE_PATH . '/config/banco.ini';
        $vars = is_file($arq) ? parse_ini_file($arq) : [];
    }
    return $vars[$chave] ?? $padrao;
}

// Descobre sozinho a URL da pasta do projeto (funciona com qualquer nome de pasta)
$docroot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$raiz    = str_replace('\\', '/', BASE_PATH);
define('BASE_URL', rtrim(str_starts_with($raiz, $docroot) ? substr($raiz, strlen($docroot)) : '', '/'));

$debug = (int) env('DEBUG', 0) === 1;
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true,
        'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
