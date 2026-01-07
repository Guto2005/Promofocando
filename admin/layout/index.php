<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editor de Layout - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">

    <style>
        main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .layout-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .layout-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .layout-header h2 {
            margin: 0;
            font-size: 1.3em;
        }

        .layout-header button {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background: #d4af37;
            color: #000;
            font-weight: bold;
            cursor: pointer;
        }

        .layout-header button:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>

<header>
    <h1>Painel Administrativo</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../produtos/">📦 Produtos</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../lojas/">🏪 Lojas</a>
        <a href="../layout/">🧩 Layouts</a>
        <a href="../components/">🧱 Components</a>
        <a href="../categorias/">📂 Categorias</a>
        <a href="../subcategorias/">📁 Subcategorias</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main>
    <div class="layout-container">
        <div class="layout-header">
            <h2>Editor de Layout da Homepage</h2>
            <button onclick="salvar()">💾 Salvar layout</button>
        </div>

        <div style="margin-bottom: 15px;">
            <button onclick="addBloco('loja')">➕ Loja</button>
            <button onclick="addBloco('banner')">🖼 Banner</button>
            <button onclick="addBloco('texto')">📝 Texto</button>
        </div>

        <div id="layout" class="layout"></div>
    </div>
</main>

<script src="../../assets/scripts/layout.js"></script>
</body>
</html>
