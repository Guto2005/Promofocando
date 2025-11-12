<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

$promocoes = $pdo->query("SELECT * FROM promocoes ORDER BY idPromocao DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Promoções - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<header>
    <h1>Gerenciar Promoções</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main>
    <h2>Lista de Promoções</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Preço Promocional</th>
                <th>Link</th>
                <th>Ativo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promocoes as $promo): ?>
                <tr>
                    <td><?= $promo['idPromocao'] ?></td>
                    <td><?= $promo['idProduto'] ?></td>
                    <td>R$ <?= number_format($promo['precoPromocional'], 2, ',', '.') ?></td>
                    <td><a href="<?= htmlspecialchars($promo['linkPromocao']) ?>" target="_blank">Abrir</a></td>
                    <td><?= $promo['ativo'] ? '✅' : '❌' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
