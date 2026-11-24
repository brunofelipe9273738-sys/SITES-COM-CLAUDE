<?php
require_once __DIR__ . '/../includes/auth.php';

$erros = [];
$d = ['nome' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $d['nome']  = trim($_POST['nome'] ?? '');
    $d['email'] = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $conf  = $_POST['senha2'] ?? '';

    if (mb_strlen($d['nome']) < 3 || mb_strlen($d['nome']) > 120) $erros[] = 'Informe seu nome completo.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (strlen($senha) < 8) $erros[] = 'A senha precisa ter ao menos 8 caracteres.';
    if ($senha !== $conf) $erros[] = 'As senhas não conferem.';
    if (empty($_POST['lgpd'])) $erros[] = 'É necessário aceitar a Política de Privacidade.';

    if (!$erros) {
        $st = db()->prepare('SELECT id FROM usuarios WHERE email = ?');
        $st->execute([$d['email']]);
        if ($st->fetch()) {
            $erros[] = 'Não foi possível concluir o cadastro com este e-mail.';
        } else {
            db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, aceitou_lgpd_em) VALUES (?, ?, ?, NOW())')
                ->execute([$d['nome'], $d['email'], password_hash($senha, PASSWORD_DEFAULT)]);
            logar_usuario((int)db()->lastInsertId());
            flash('sucesso', 'Conta criada com sucesso. Bem-vindo(a)!');
            redirecionar('');
        }
    }
}

$titulo = 'Criar conta';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-card">
  <h1>Criar conta</h1>
  <?php foreach ($erros as $er): ?><div class="aviso aviso-erro"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <?= csrf_campo() ?>
    <label>Nome completo <input name="nome" value="<?= e($d['nome']) ?>" required></label>
    <label>E-mail <input type="email" name="email" value="<?= e($d['email']) ?>" required></label>
    <label>Senha <input type="password" name="senha" minlength="8" required></label>
    <label>Confirmar senha <input type="password" name="senha2" minlength="8" required></label>
    <label class="check"><input type="checkbox" name="lgpd" value="1"> Li e aceito a Política de Privacidade (LGPD).</label>
    <button class="btn" type="submit">Cadastrar</button>
  </form>
  <p>Já tem conta? <a href="<?= url('usuarios/login.php') ?>">Entrar</a></p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
