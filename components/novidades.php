<?php
$db = include __DIR__.'/../includes/conexao.php';
$q = $db->query("SELECT * FROM produtos WHERE novidade = 1 LIMIT 6");
$items = $q->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="novidades">
  <?php foreach ($items as $p): ?>
    <div class="card">
      <img src="<?= $p['imagem'] ?>">
      <h4><?= $p['nome'] ?></h4>
    </div>
  <?php endforeach; ?>
</div>
