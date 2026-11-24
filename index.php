<?php
$titulo = 'Joalheria | Início';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <p class="eyebrow">Nova coleção</p>
  <h1>Joias com elegância atemporal</h1>
  <p>Peças exclusivas em ouro 18k e prata 950.</p>
  <a class="btn btn-inline" href="<?= url('produtos/lista.php?lancamento=1') ?>">Ver lançamentos</a>
</section>
<?php
secao_produtos('Destaques', ['destaque' => 1], 'produtos/lista.php?destaque=1');
secao_produtos('Lançamentos', ['lancamento' => 1], 'produtos/lista.php?lancamento=1');
secao_produtos('Ofertas especiais', ['oferta' => 1], 'produtos/lista.php?oferta=1');
?>
<section class="secao"><div class="secao-topo"><h2>Categorias</h2></div>
  <div class="cats">
    <?php foreach (categorias() as $c): ?>
      <a href="<?= url('produtos/lista.php?cat=' . $c['slug']) ?>"><?= e($c['nome']) ?></a>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
