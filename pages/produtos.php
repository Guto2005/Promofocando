<?php
require_once "../includes/conexao.php";
require_once "../includes/helpers.php";

$result = $conn->query("SELECT * FROM produtos");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produtos - Promofocando</title>
    <link rel="stylesheet" href="../assets/css/produtos.css">
</head>
<body>

    <h1>Lista de Produtos</h1>

    <div class="produtos">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="produto">
                <img src="../assets/img/<?= htmlspecialchars($row['imagem']) ?>" alt="<?= htmlspecialchars($row['nome']) ?>">
                <h2><?= htmlspecialchars($row['nome']) ?></h2>
                <p><?= formatarPreco($row['preco']) ?></p>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>
