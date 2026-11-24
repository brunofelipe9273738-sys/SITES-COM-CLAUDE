<?php
require_once __DIR__ . '/../includes/loja.php';

$st = db()->prepare('SELECT p.*, c.nome AS categoria, c.slug AS cat_slug FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE p.slug = ? AND p.ativo = 1');
$st->execute([$_GET['slug'] ?? '']);
$p = $st->fetch();
if (!$p) { http_response_code(404); $titulo = 'Produto não encontrado'; require __DIR__ . '/../includes/header.php'; echo '<p>Produto não encontrado.</p>'; require __DIR__ . '/../includes/footer.php'; exit; }

$im = db()->prepare('SELECT caminho FROM produto_imagens WHERE produto_id = ? ORDER BY ordem'); $im->execute([$p['id']]); $imagens = $im->fetchAll(PDO::FETCH_COLUMN);
if (!$imagens && ($padrao = imagem_produto($p['slug'], null))) $imagens = [$padrao];
$va = db()->prepare('SELECT id, rotulo, estoque FROM produto_variacoes WHERE produto_id = ? ORDER BY id'); $va->execute([$p['id']]); $variacoes = $va->fetchAll();
$estoque = array_sum(array_column($variacoes, 'estoque'));
$av = db()->prepare('SELECT a.nota, a.comentario, u.nome FROM avaliacoes a JOIN usuarios u ON u.id = a.usuario_id WHERE a.produto_id = ? AND a.aprovada = 1 ORDER BY a.id DESC'); $av->execute([$p['id']]); $avaliacoes = $av->fetchAll();
$rel = listar_produtos(['cat' => $p['cat_slug']], 5)['itens'];
$rel = array_slice(array_filter($rel, fn($x) => $x['id'] != $p['id']), 0, 4);
$final = $p['preco_promocional'] ?? $p['preco'];

$titulo = $p['nome'] . ' | Joalheria';
require __DIR__ . '/../includes/header.php';
?>
<div class="produto">
  <div class="galeria">
    <?php if ($imagens): foreach ($imagens as $img): ?>
      <div class="zoom"><img src="<?= url($img) ?>" alt="<?= e($p['nome']) ?>"></div>
    <?php endforeach; else: ?><div class="sem-foto grande">◆</div><?php endif; ?>
  </div>
  <div class="info">
    <p class="cat"><a href="<?= url('produtos/lista.php?cat=' . $p['cat_slug']) ?>"><?= e($p['categoria']) ?></a></p>
    <h1><?= e($p['nome']) ?></h1>
    <?php if ($p['preco_promocional']): ?><p class="de"><?= moeda($p['preco']) ?></p><?php endif; ?>
    <p class="preco grande-preco"><?= moeda($final) ?></p>
    <p class="parc">ou <?= e(parcelado($final, (int)$p['parcelas_max'])) ?></p>
    <p><?= nl2br(e($p['descricao'])) ?></p>
    <ul class="ficha">
      <li><b>Material:</b> <?= e($p['material']) ?></li>
      <li><b>Peso:</b> <?= e(number_format((float)$p['peso_gramas'], 2, ',', '')) ?> g</li>
      <li><b>Disponibilidade:</b> <?= $estoque > 0 ? 'Em estoque' : 'Indisponível' ?></li>
    </ul>
    <label>Tamanho / variação
      <select><?php foreach ($variacoes as $v): ?><option <?= $v['estoque'] < 1 ? 'disabled' : '' ?>><?= e($v['rotulo']) ?><?= $v['estoque'] < 1 ? ' (esgotado)' : '' ?></option><?php endforeach; ?></select>
    </label>
    <div class="acoes">
      <button class="btn" disabled title="Disponível na próxima etapa">Comprar</button>
      <button class="btn btn-claro" disabled title="Disponível na próxima etapa">Adicionar ao carrinho</button>
      <button class="btn btn-claro" disabled title="Disponível na próxima etapa">♡ Favoritar</button>
    </div>
    <p class="nota">Compra, carrinho e favoritos entram na próxima etapa.</p>
  </div>
</div>
<section class="secao"><div class="secao-topo"><h2>Avaliações</h2></div>
  <?php if (!$avaliacoes): ?><p>Este produto ainda não possui avaliações.</p><?php endif; ?>
  <?php foreach ($avaliacoes as $a): ?>
    <div class="avaliacao"><b><?= e($a['nome']) ?></b> <?= str_repeat('★', (int)$a['nota']) ?><p><?= e($a['comentario']) ?></p></div>
  <?php endforeach; ?>
</section>
<?php if ($rel): ?><section class="secao"><div class="secao-topo"><h2>Produtos relacionados</h2></div><div class="grade"><?php foreach ($rel as $x) card_produto($x); ?></div></section><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
