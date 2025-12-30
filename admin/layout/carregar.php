<?php

$file = __DIR__ . "/layout.json";

if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

echo file_get_contents($file);
