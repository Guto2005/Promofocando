<?php
$layoutData = json_decode(file_get_contents($layoutFile), true);

foreach ($layoutData['grid'] as $area) {
    $compName = $area['component'];
    $compFile = __DIR__ . "/../data/components/$compName.json";

    if (!file_exists($compFile)) continue;

    $compData = json_decode(file_get_contents($compFile), true);
    include __DIR__ . "/../components/{$compData['tipo']}.php";
}
