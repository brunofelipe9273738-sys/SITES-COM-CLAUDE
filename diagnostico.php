<?php
// Diagnóstico temporário. APAGUE depois dos testes.
ini_set('display_errors', '1'); error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/app.php';
echo '<h2>Diagnóstico da Joalheria</h2>';
echo '<p>PHP: ' . PHP_VERSION . '</p>';
echo '<p>Pasta do projeto: ' . htmlspecialchars(BASE_PATH) . '</p>';
echo '<p>Endereço detectado: <b>' . htmlspecialchars(BASE_URL) . '/</b></p>';
echo '<p><a href="' . BASE_URL . '/">Início</a> | <a href="' . BASE_URL . '/usuarios/cadastro.php">Cadastro</a> | <a href="' . BASE_URL . '/usuarios/login.php">Login</a></p>';
foreach (['config/app.php','config/database.php','config/banco.ini','includes/auth.php','includes/helpers.php','includes/header.php','includes/footer.php','usuarios/cadastro.php','usuarios/login.php','usuarios/logout.php','assets/css/style.css'] as $a) {
    echo (is_file(BASE_PATH . "/$a") ? '✅ ' : '❌ FALTANDO: ') . $a . '<br>';
}
echo '<h3>Banco de dados</h3>';
try {
    require_once BASE_PATH . '/config/database.php';
    $n = db()->query('SHOW TABLES')->rowCount();
    echo "✅ Conectou. Tabelas: $n (esperado: 22)";
    if ($n < 22) echo '<br>❌ Importe database/database.sql no phpMyAdmin.';
} catch (Throwable $e) {
    echo '❌ ' . htmlspecialchars($e->getMessage());
}
