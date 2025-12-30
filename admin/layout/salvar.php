<?php

$data = file_get_contents("php://input");

if (!$data) {
    http_response_code(400);
    echo "Nenhum dado recebido";
    exit;
}

file_put_contents(__DIR__ . "/layout.json", $data);

echo "ok";
