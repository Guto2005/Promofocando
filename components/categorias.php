<?php
$db = include __DIR__.'/../includes/conexao.php';
$limite = $compData['config']['limite'] ?? 12;

$q = $db->prepare("SELECT * FROM categorias LIMIT ?");
$q->bindValue(1, $limite, PDO::PARAM_INT);
$q->execute();
$cats = $q->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="categorias-grid">
  <?php foreach ($cats as $c): ?>
    <a href="/pages/produtos.php?categoria=<?= $c['id'] ?>" class="cat-box">
      <?= htmlspecialchars($c['nome']) ?>
    </a>
  <?php endforeach; ?>
</div>
