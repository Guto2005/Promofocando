<?php
require_once "../includes/conexao.php";
require_once "../includes/helpers.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar - Promofocando</title>
    <link rel="stylesheet" href="../assets/css/cadastrar.css">
</head>
<body>

    <div class="cadastro-container">
        <h1>Criar Conta</h1>
        <form action="cadastrar.php" method="POST">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Cadastrar</button>
        </form>
    </div>

</body>
</html>
