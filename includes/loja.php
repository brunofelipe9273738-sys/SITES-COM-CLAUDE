<?php
// includes/loja.php - funções da vitrine
require_once __DIR__ . '/helpers.php';

function moeda($v): string { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function parcelado($v, int $n = 10): string { return $n . 'x de ' . moeda($v / $n) . ' sem juros'; }

function imagem_produto(string $slug, ?string $foto): ?string {
    if ($foto) return $foto;
    $rel = 'assets/imagens/produtos/' . $slug . '.svg';
    return is_file(BASE_PATH . '/' . $rel) ? $rel : null;
}

function categorias(): array {
    static $c = null;
    return $c ??= db()->query('SELECT id, nome, slug FROM categorias WHERE ativa = 1 ORDER BY id')->fetchAll();
}

function listar_produtos(array $f, int $limite = 12, int $pagina = 1): array {
    $w = ['p.ativo = 1']; $a = [];
    if (!empty($f['cat'])) { $w[] = 'c.slug = ?'; $a[] = $f['cat']; }
    if (!empty($f['q']))   { $w[] = '(p.nome LIKE ? OR p.descricao LIKE ?)'; $a[] = '%' . $f['q'] . '%'; $a[] = '%' . $f['q'] . '%'; }
    if (!empty($f['min'])) { $w[] = 'COALESCE(p.preco_promocional, p.preco) >= ?'; $a[] = (float)$f['min']; }
    if (!empty($f['max'])) { $w[] = 'COALESCE(p.preco_promocional, p.preco) <= ?'; $a[] = (float)$f['max']; }
    if (!empty($f['destaque']))   $w[] = 'p.destaque = 1';
    if (!empty($f['lancamento'])) $w[] = 'p.lancamento = 1';
    if (!empty($f['oferta']))     $w[] = 'p.preco_promocional IS NOT NULL';

    $ordens = ['menor' => 'preco_final ASC', 'maior' => 'preco_final DESC', 'nome' => 'p.nome ASC'];
    $ord = $ordens[$f['ordem'] ?? ''] ?? 'p.id DESC';   // lista branca: nada do usuário vai direto ao SQL

    $from = ' FROM produtos p JOIN categorias c ON c.id = p.categoria_id WHERE ' . implode(' AND ', $w);
    $st = db()->prepare('SELECT COUNT(*)' . $from); $st->execute($a);
    $total = (int)$st->fetchColumn();

    $sql = 'SELECT p.id, p.nome, p.slug, p.preco, p.preco_promocional, p.parcelas_max,
                   COALESCE(p.preco_promocional, p.preco) AS preco_final, c.nome AS categoria,
                   (SELECT caminho FROM produto_imagens WHERE produto_id = p.id ORDER BY ordem LIMIT 1) AS foto'
         . $from . " ORDER BY $ord LIMIT " . (int)$limite . ' OFFSET ' . (int)max(0, ($pagina - 1) * $limite);
    $st = db()->prepare($sql); $st->execute($a);
    $itens = array_map(function ($p) { $p['foto'] = imagem_produto($p['slug'], $p['foto']); return $p; }, $st->fetchAll());
    return ['itens' => $itens, 'total' => $total];
}

function card_produto(array $p): void { ?>
  <a class="card" href="<?= url('produtos/produto.php?slug=' . urlencode($p['slug'])) ?>">
    <div class="card-foto">
      <?php if ($p['foto']): ?><img src="<?= url($p['foto']) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
      <?php else: ?><div class="sem-foto">◆</div><?php endif; ?>
      <?php if ($p['preco_promocional']): ?><span class="selo">Oferta</span><?php endif; ?>
    </div>
    <h3><?= e($p['nome']) ?></h3>
    <p class="cat"><?= e($p['categoria']) ?></p>
    <?php if ($p['preco_promocional']): ?><p class="de"><?= moeda($p['preco']) ?></p><?php endif; ?>
    <p class="preco"><?= moeda($p['preco_final']) ?></p>
    <p class="parc">ou <?= e(parcelado($p['preco_final'], (int)$p['parcelas_max'])) ?></p>
  </a>
<?php }

function secao_produtos(string $titulo, array $filtro, string $link = ''): void {
    $r = listar_produtos($filtro, 4);
    if (!$r['itens']) return;
    echo '<section class="secao"><div class="secao-topo"><h2>' . e($titulo) . '</h2>';
    if ($link) echo '<a href="' . url($link) . '">Ver todos →</a>';
    echo '</div><div class="grade">';
    foreach ($r['itens'] as $p) card_produto($p);
    echo '</div></section>';
}
