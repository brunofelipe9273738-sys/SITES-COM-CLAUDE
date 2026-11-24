<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/loja.php';
$usuario = usuario_atual();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Joalheria') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="topo">
  <a class="logo" href="<?= url() ?>">JOALHERIA</a>
  <form class="busca" action="<?= url('produtos/lista.php') ?>" method="get">
    <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Buscar joias..." aria-label="Buscar">
    <button type="submit">Buscar</button>
  </form>
  <nav>
    <?php if ($usuario): ?>
      <span>Olá, <?= e(explode(' ', $usuario['nome'])[0]) ?></span>
      <?php if ($usuario['papel'] === 'admin'): ?><a href="<?= url('admin/') ?>">Admin</a><?php endif; ?>
      <a href="<?= url('usuarios/logout.php') ?>">Sair</a>
    <?php else: ?>
      <a href="<?= url('usuarios/login.php') ?>">Entrar</a>
      <a href="<?= url('usuarios/cadastro.php') ?>">Criar conta</a>
    <?php endif; ?>
  </nav>
</header>
<nav class="menu-cat">
  <?php foreach (categorias() as $c): ?>
    <a href="<?= url('produtos/lista.php?cat=' . $c['slug']) ?>"><?= e($c['nome']) ?></a>
  <?php endforeach; ?>
</nav>
<main>
<?php foreach (pegar_flash() as $f): ?>
  <div class="aviso aviso-<?= e($f['tipo']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
