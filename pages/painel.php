<?php
require_once "../includes/autenticacao.php";
verificarLogin();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Promofocando</title>
    <link rel="stylesheet" href="../assets/css/painel.css">
</head>
<body>

    <header>
        <h1>Bem-vindo ao Painel</h1>
        <a href="logout.php">Sair</a>
    </header>

    <main>
        <p>Aqui você poderá gerenciar os produtos e promoções.</p>
    </main>

</body>
</html>
