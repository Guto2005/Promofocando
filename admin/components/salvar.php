<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Requisição inválida.');
}

$nome = isset($_POST['nome']) 
    ? preg_replace('/[^a-z0-9_-]/i', '_', strtolower($_POST['nome'])) 
    : null;

$tipo = $_POST['tipo'] ?? null;

if (!$nome || !$tipo) {
    die('Nome ou tipo não informado.');
}

if (strpos($nome, '..') !== false) {
    die('Nome inválido.');
}

$config = [];

switch ($tipo) {

    case 'banner':
        $config = [
            'imagem' => $_POST['imagem'] ?? '',
            'link'   => $_POST['link'] ?? '',
            'texto'  => $_POST['texto'] ?? '',
            'cor'    => $_POST['cor'] ?? '#ffffff',
        ];
        break;

    case 'carrossel':
        $slides = json_decode($_POST['slides'] ?? '[]', true);
        if (!is_array($slides)) $slides = [];
        $config = ['slides' => $slides];
        break;

    case 'novidades':
    case 'promocoes':
        $config = [
            'limite' => max(1, intval($_POST['limite'] ?? 8)),
            'ver_mais_link' => $_POST['ver_mais_link'] ?? ''
        ];
        break;

    case 'categorias':
        $config = [
            'mostrar_subcategorias' => !empty($_POST['mostrar_subcategorias'])
        ];
        break;

    case 'texto':
        $config = [
            'titulo' => $_POST['titulo'] ?? '',
            'subtitulo' => $_POST['subtitulo'] ?? '',
            'conteudo' => $_POST['conteudo'] ?? '',
        ];
        break;

    case 'cinto_banner':
        $itens = json_decode($_POST['itens'] ?? '[]', true);
        if (!is_array($itens)) $itens = [];
        $config = ['itens' => $itens];
        break;

    default:
        die('Tipo de componente inválido.');
}

$dados = [
    'tipo' => $tipo,
    'config' => $config
];

$dir = __DIR__ . "/../../data/components";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$path = $dir . "/{$nome}.json";

file_put_contents($path, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: index.php");
exit;
