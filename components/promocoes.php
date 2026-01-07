<?php
$db = include __DIR__.'/../includes/conexao.php';
$limite = $compData['config']['limite'] ?? 6;
$link = $compData['config']['ver_mais_link'] ?? '/pages/promocoes.php';

$q = $db->prepare("SELECT * FROM produtos WHERE promocao = 1 LIMIT ?");
$q->bindValue(1, $limite, PDO::PARAM_INT);
$q->execute();
$items = $q->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="promocoes">
  <?php foreach ($items as $p): ?>
    <div class="card">
      <img src="<?= $p['imagem'] ?>">
      <h4><?= htmlspecialchars($p['nome']) ?></h4>
    </div>
  <?php endforeach; ?>
</div>

<a href="<?= $link ?>" class="ver-mais">Ver mais promoções</a>
