<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

// Contagens
$totalPromocoes = $pdo->query("SELECT COUNT(*) FROM promocoes")->fetchColumn();
$totalNovidades = $pdo->query("SELECT COUNT(*) FROM novidades")->fetchColumn();
$dataHoje = date('d/m/Y H:i');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<header>
    <h1>Painel Administrativo</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../lojas/">🏪 Lojas</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main>
    <section>
        <h2>Resumo Geral</h2>
        <p><strong>Promoções:</strong> <?= $totalPromocoes ?></p>
        <p><strong>Novidades:</strong> <?= $totalNovidades ?></p>
        <p><strong>Último acesso:</strong> <?= $dataHoje ?></p>
    </section>
</main>

</body>
</html>
