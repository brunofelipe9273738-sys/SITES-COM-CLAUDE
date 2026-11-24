<?php
require_once __DIR__ . '/../includes/auth.php';
if (usuario_atual()) redirecionar('');

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if (login_bloqueado($email)) {
        $erro = 'Muitas tentativas. Aguarde 15 minutos e tente de novo.';
    } else {
        $st = db()->prepare('SELECT id, senha_hash FROM usuarios WHERE email = ? AND ativo = 1');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha_hash'])) {
            limpar_falhas($email);
            if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
                    ->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
            }
            logar_usuario((int)$u['id']);
            redirecionar('');
        }
        registrar_falha($email);
        $erro = 'E-mail ou senha incorretos.';   // mensagem genérica de propósito
    }
}

$titulo = 'Entrar';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-card">
  <h1>Entrar</h1>
  <?php if ($erro): ?><div class="aviso aviso-erro"><?= e($erro) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_campo() ?>
    <label>E-mail <input type="email" name="email" required></label>
    <label>Senha <input type="password" name="senha" required></label>
    <button class="btn" type="submit">Entrar</button>
  </form>
  <p><a href="<?= url('usuarios/cadastro.php') ?>">Criar conta</a></p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
