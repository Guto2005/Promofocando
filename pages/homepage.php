<?php
$data = json_decode(file_get_contents('../data/layouts/homepage.json'), true);
?>
<link rel="stylesheet" href="/assets/css/layout.css">

<div class="layout">
<?php foreach ($data as $item): ?>
  <div class="block"
       style="grid-column: <?= $item['layout']['x']+1 ?> / span <?= $item['layout']['w'] ?>;
              grid-row: <?= $item['layout']['y']+1 ?> / span <?= $item['layout']['h'] ?>;">
    <?php if (!empty($item['content']['title'])): ?>
      <h3><?= $item['content']['title'] ?></h3>
    <?php endif; ?>

    <?php if (!empty($item['content']['text'])): ?>
      <p><?= $item['content']['text'] ?></p>
    <?php endif; ?>
  </div>
<?php endforeach ?>
</div>
