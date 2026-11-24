</main>
<footer class="rodape">
  <div class="rodape-grade">
    <div><h4>JOALHERIA</h4><p>Joias em ouro 18k e prata 950, com certificado e embalagem para presente.</p></div>
    <div><h4>Categorias</h4>
      <?php foreach (array_slice(categorias(), 0, 6) as $c): ?><a href="<?= url('produtos/lista.php?cat=' . $c['slug']) ?>"><?= e($c['nome']) ?></a><?php endforeach; ?>
    </div>
    <div><h4>Minha conta</h4>
      <a href="<?= url('usuarios/login.php') ?>">Entrar</a><a href="<?= url('usuarios/cadastro.php') ?>">Criar conta</a>
    </div>
    <div><h4>Compra segura</h4><p>Pagamento em até 10x sem juros.<br>Troca em até 7 dias.</p></div>
  </div>
  <p class="direitos">&copy; <?= date('Y') ?> Joalheria. Todos os direitos reservados.</p>
</footer>
</body>
</html>
