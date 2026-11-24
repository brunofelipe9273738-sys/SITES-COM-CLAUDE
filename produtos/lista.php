<?php
require_once __DIR__ . '/../includes/loja.php';

$f = [
  'cat' => $_GET['cat'] ?? '', 'q' => trim($_GET['q'] ?? ''), 'ordem' => $_GET['ordem'] ?? '',
  'min' => $_GET['min'] ?? '', 'max' => $_GET['max'] ?? '',
  'destaque' => $_GET['destaque'] ?? '', 'lancamento' => $_GET['lancamento'] ?? '', 'oferta' => $_GET['oferta'] ?? '',
];
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$por = 12;
$r = listar_produtos($f, $por, $pagina);
$paginas = max(1, (int)ceil($r['total'] / $por));

$titulo = 'Produtos | Joalheria';
foreach (categorias() as $c) if ($c['slug'] === $f['cat']) $titulo = $c['nome'] . ' | Joalheria';

function link_pag(int $p): string { $q = $_GET; $q['pagina'] = $p; return '?' . http_build_query($q); }
require __DIR__ . '/../includes/header.php';
?>
<h1 class="titulo-pag"><?= $f['q'] ? 'Resultados para “' . e($f['q']) . '”' : 'Nossas joias' ?></h1>
<form class="filtros" method="get">
  <?php foreach (['cat','q','destaque','lancamento','oferta'] as $k) if ($f[$k] !== '') echo '<input type="hidden" name="' . $k . '" value="' . e($f[$k]) . '">'; ?>
  <label>Preço mín. <input type="number" name="min" min="0" step="1" value="<?= e((string)$f['min']) ?>"></label>
  <label>Preço máx. <input type="number" name="max" min="0" step="1" value="<?= e((string)$f['max']) ?>"></label>
  <label>Ordenar
    <select name="ordem">
      <option value="">Mais recentes</option>
      <option value="menor" <?= $f['ordem'] === 'menor' ? 'selected' : '' ?>>Menor preço</option>
      <option value="maior" <?= $f['ordem'] === 'maior' ? 'selected' : '' ?>>Maior preço</option>
      <option value="nome" <?= $f['ordem'] === 'nome' ? 'selected' : '' ?>>Nome (A-Z)</option>
    </select>
  </label>
  <button class="btn btn-inline" type="submit">Filtrar</button>
</form>
<p class="contagem"><?= $r['total'] ?> produto(s)</p>
<?php if (!$r['itens']): ?>
  <p>Nenhum produto encontrado.</p>
<?php else: ?>
  <div class="grade"><?php foreach ($r['itens'] as $p) card_produto($p); ?></div>
  <?php if ($paginas > 1): ?>
  <div class="paginacao">
    <?php for ($i = 1; $i <= $paginas; $i++): ?>
      <a href="<?= e(link_pag($i)) ?>" class="<?= $i === $pagina ? 'ativa' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
