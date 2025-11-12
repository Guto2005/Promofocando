<?php
session_start();
require_once __DIR__ . '/../../assets/includes/conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../pages/login.php');
    exit;
}

// Consulta os totais do painel
try {
    $stmtPromocoes = $pdo->query("SELECT COUNT(*) AS total FROM promocoes");
    $totalPromocoes = $stmtPromocoes->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtNovidades = $pdo->query("SELECT COUNT(*) AS total FROM novidades");
    $totalNovidades = $stmtNovidades->fetch(PDO::FETCH_ASSOC)['total'];

    $stmtUsuarios = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
    $totalUsuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {
    die("Erro ao buscar dados do painel: " . $e->getMessage());
}
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
        <h1>Painel Administrativo - Promofocando</h1>
        <a href="../../logout.php">Sair</a>
    </header>

    <main class="container">
        <div class="card">
            <h2><?= $totalPromocoes ?></h2>
            <p>Promoções ativas</p>
        </div>

        <div class="card">
            <h2><?= $totalNovidades ?></h2>
            <p>Novidades publicadas</p>
        </div>

        <div class="card">
            <h2><?= $totalUsuarios ?></h2>
            <p>Usuários cadastrados</p>
        </div>
    </main>

    <section class="actions">
        <a href="../promocoes/index.php">Gerenciar Promoções</a>
        <a href="../novidades/index.php">Gerenciar Novidades</a>
    </section>

</body>
</html>
