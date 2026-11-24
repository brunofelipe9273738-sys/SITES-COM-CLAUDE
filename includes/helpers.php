<?php
// includes/helpers.php - escape, redirect, flash, CSRF
require_once __DIR__ . '/../config/database.php';

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function url(string $caminho = ''): string { return BASE_URL . '/' . ltrim($caminho, '/'); }

function redirecionar(string $caminho): never { header('Location: ' . url($caminho)); exit; }

function flash(string $tipo, string $msg): void { $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $msg]; }

function pegar_flash(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_campo(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }

function csrf_validar(): void {
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('Sessão expirada. Volte e tente novamente.'); }
}

function ip_cliente(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
