<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

$novidades = $pdo->query("SELECT * FROM novidades ORDER BY idNovidade DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Novidades - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<header>
    <h1>Gerenciar Novidades</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main>
    <h2>Lista de Novidades</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Imagem</th>
                <th>Link</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($novidades as $nov): ?>
                <tr>
                    <td><?= $nov['idNovidade'] ?></td>
                    <td><?= htmlspecialchars($nov['titulo']) ?></td>
                    <td><img src="../../uploads/<?= htmlspecialchars($nov['imagemNovidade']) ?>" width="60"></td>
                    <td><a href="<?= htmlspecialchars($nov['linkNovidade']) ?>" target="_blank">Abrir</a></td>
                    <td><?= date('d/m/Y', strtotime($nov['dataPublicacao'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
