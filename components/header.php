<?php $menu = $compData['config']['menu'] ?? []; ?>

<header class="site-header">
  <div class="logo"><a href="/">PROMOFOCANDO</a></div>

  <form action="/pages/produtos.php" method="get" class="busca">
    <input type="text" name="q" placeholder="Buscar produtos...">
  </form>

  <nav>
    <?php foreach ($menu as $m): ?>
      <div class="menu-item">
        <a href="<?= $m['link'] ?>"><?= $m['texto'] ?></a>
        <?php if (!empty($m['sub'])): ?>
          <div class="submenu">
            <?php foreach ($m['sub'] as $s): ?>
              <a href="<?= $s['link'] ?>"><?= $s['texto'] ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </nav>
</header>
