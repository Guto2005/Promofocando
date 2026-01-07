<?php
$dir = "../../data/components/";
$files = glob($dir . "*.json");

echo "<h2>Componentes criados</h2>";

if (!$files) {
    echo "<p>Nenhum componente criado ainda.</p>";
    return;
}

echo "<ul>";
foreach ($files as $file) {
    $name = basename($file, ".json");
    echo "<li>$name</li>";
}
echo "</ul>";
