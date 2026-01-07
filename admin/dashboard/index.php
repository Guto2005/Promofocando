<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

// Contagens das tabelas
$totalProdutos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totalLojas = $pdo->query("SELECT COUNT(*) FROM lojas")->fetchColumn();
$totalCategorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
$totalSubcategorias = $pdo->query("SELECT COUNT(*) FROM subcategorias")->fetchColumn();
$totalPromocoes = $pdo->query("SELECT COUNT(*) FROM promocoes")->fetchColumn();

// Novidades agora vêm da tabela produtos
$totalNovidades = $pdo->query("
    SELECT COUNT(*) 
    FROM produtos
    WHERE dataCadastro >= DATE_SUB(NOW(), INTERVAL 10 DAY)
")->fetchColumn();


$dataHoje = date('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
        main {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            flex: 1 1 200px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2em;
            color: #333;
        }

        .card p {
            font-size: 1.6em;
            font-weight: bold;
            color: #000;
        }

        section#info-geral {
            margin-top: 20px;
            flex-basis: 100%;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
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

    <div class="card">
        <h3>Total de Produtos</h3>
        <p><?= $totalProdutos ?></p>
    </div>

    <div class="card">
        <h3>Total de Lojas</h3>
        <p><?= $totalLojas ?></p>
    </div>

    <div class="card">
        <h3>Total de Categorias</h3>
        <p><?= $totalCategorias ?></p>
    </div>

    <div class="card">
        <h3>Total de Subcategorias</h3>
        <p><?= $totalSubcategorias ?></p>
    </div>

    <div class="card">
        <h3>Total de Promoções</h3>
        <p><?= $totalPromocoes ?></p>
    </div>

    <div class="card">
        <h3>Total de Novidades</h3>
        <p><?= $totalNovidades ?></p>
    </div>

    <section id="info-geral">
        <h3>Último acesso</h3>
        <p><?= $dataHoje ?></p>
    </section>

</main>

</body>
</html>
