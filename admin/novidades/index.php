<?php
session_start();
require_once "../../assets/includes/conexao.php";

/* ================================
   PROTEÇÃO
================================ */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ================================
   CONFIGURAÇÃO
================================ */
$novidadeDias   = 10;
$filtroCategoria = $_GET['categoria'] ?? null;

/* ================================
   CATEGORIAS (SIDEBAR)
================================ */
$categorias = $pdo->query("
    SELECT idCategoria, nomeCategoria
    FROM categorias
    ORDER BY nomeCategoria
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   BUSCA PRODUTOS NOVOS
================================ */
$sql = "
    SELECT 
        p.*,
        c.nomeCategoria,
        s.nomeSubcategoria,
        DATEDIFF(NOW(), p.dataCadastro) AS diasPassados
    FROM produtos p
    LEFT JOIN categorias c ON c.idCategoria = p.idCategoria
    LEFT JOIN subcategorias s ON s.idSubcategoria = p.idSubcategoria
    WHERE p.dataCadastro >= DATE_SUB(NOW(), INTERVAL ? DAY)
";

$params = [$novidadeDias];

if (!empty($filtroCategoria)) {
    $sql .= " AND p.idCategoria = ?";
    $params[] = $filtroCategoria;
}

$sql .= " ORDER BY p.dataCadastro DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Novidades</title>
<link rel="stylesheet" href="../../assets/css/admin.css">

<style>
main.layout-admin {
    display: flex;
    gap: 20px;
}

aside.sidebar {
    width: 260px;
    background: #f5f5f5;
    padding: 15px;
    border-radius: 6px;
}

aside.sidebar h3 {
    margin: 15px 0 8px;
}

aside.sidebar a {
    display: block;
    padding: 8px;
    margin-bottom: 5px;
    background: #fff;
    border-radius: 4px;
    text-decoration: none;
    color: #000;
    font-size: 14px;
}

aside.sidebar a:hover {
    background: #eaeaea;
}

section.conteudo {
    flex: 1;
}

.badge-novo {
    background: #e60023;
    color: #fff;
    padding: 3px 6px;
    font-size: 11px;
    border-radius: 4px;
    font-weight: bold;
}
</style>
</head>

<body>

<header>
    <h1>📰 Novidades (últimos <?= $novidadeDias ?> dias)</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../produtos/">📦 Produtos</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../lojas/">🏪 Lojas</a>
        <a href="../categorias/">📂 Categorias</a>
        <a href="../subcategorias/">📁 Subcategorias</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main class="layout-admin">

<aside class="sidebar">
    <h3>Categorias</h3>

    <a href="index.php">📋 Todas</a>

    <?php foreach ($categorias as $c): ?>
        <a href="?categoria=<?= $c['idCategoria'] ?>">
            📂 <?= htmlspecialchars($c['nomeCategoria']) ?>
        </a>
    <?php endforeach; ?>
</aside>

<section class="conteudo">

<h2>Produtos Recentes</h2>

<?php if (empty($produtos)): ?>
    <p>Nenhum produto novo encontrado.</p>
<?php else: ?>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Produto</th>
    <th>Categoria</th>
    <th>Subcategoria</th>
    <th>Imagem</th>
    <th>Link</th>
    <th>Cadastrado</th>
    <th>Dias</th>
</tr>
</thead>
<tbody>

<?php foreach ($produtos as $p): ?>
<tr>
    <td><?= $p['idProduto'] ?></td>
    <td>
        <?= htmlspecialchars($p['nomeProduto']) ?>
        <span class="badge-novo">NOVO</span>
    </td>
    <td><?= htmlspecialchars($p['nomeCategoria']) ?></td>
    <td><?= htmlspecialchars($p['nomeSubcategoria']) ?></td>
    <td>
        <?php if (!empty($p['imagemProduto'])): ?>
            <img src="<?= $p['imagemProduto'] ?>" style="max-width:60px;">
        <?php endif; ?>
    </td>
    <td>
        <?php if (!empty($p['linkProduto'])): ?>
            <a href="<?= $p['linkProduto'] ?>" target="_blank">Abrir</a>
        <?php endif; ?>
    </td>
    <td><?= date("d/m/Y", strtotime($p['dataCadastro'])) ?></td>
    <td><?= $p['diasPassados'] ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<?php endif; ?>

</section>
</main>

</body>
</html>

