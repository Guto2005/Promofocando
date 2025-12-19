<?php
session_start();
require_once "../../assets/includes/conexao.php";

/* ===============================
   PROTEÇÃO DE LOGIN
================================ */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ===============================
   CONFIGURAÇÃO
================================ */
$novidadeDias = 10;
$filtroCategoria = $_GET['categoria'] ?? null;

/* ===============================
   BUSCA CATEGORIAS (SIDEBAR)
================================ */
$categorias = $pdo->query("
    SELECT * FROM categorias 
    ORDER BY nomeCategoria ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   BUSCA PRODUTOS NOVOS
================================ */
$sql = "
SELECT 
    p.*,
    c.nomeCategoria,
    s.nomeSubcategoria,
    DATEDIFF(NOW(), p.dataCadastro) AS diasPassados
FROM produtos p
LEFT JOIN categorias c ON p.idCategoria = c.idCategoria
LEFT JOIN subcategorias s ON p.idSubcategoria = s.idSubcategoria
WHERE p.dataCadastro >= DATE_SUB(NOW(), INTERVAL :dias DAY)
";

if ($filtroCategoria) {
    $sql .= " AND p.idCategoria = :categoria ";
}

$sql .= " ORDER BY p.dataCadastro DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':dias', $novidadeDias, PDO::PARAM_INT);

if ($filtroCategoria) {
    $stmt->bindValue(':categoria', $filtroCategoria, PDO::PARAM_INT);
}

$stmt->execute();
$produtosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Novidades</title>

<style>
body { margin:0; font-family:Arial; display:flex; }
header {
    position:fixed;
    top:0; left:0;
    width:100%;
    background:#111;
    color:#fff;
    padding:15px;
    z-index:10;
}
header nav a {
    color:#fff;
    margin-right:15px;
    text-decoration:none;
}
.sidebar {
    width:220px;
    margin-top:80px;
    background:#f2f2f2;
    padding:15px;
    border-right:1px solid #ccc;
}
.sidebar a {
    display:block;
    padding:6px;
    background:#fff;
    margin-bottom:5px;
    text-decoration:none;
    color:#000;
    border:1px solid #ccc;
}
.sidebar a:hover {
    background:#ddd;
}
main {
    flex:1;
    padding:20px;
    margin-top:80px;
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    border:1px solid #ccc;
    padding:6px;
}
img {
    max-width:60px;
}
.badge {
    background:#e60023;
    color:#fff;
    padding:3px 6px;
    font-size:11px;
    border-radius:4px;
    font-weight:bold;
}
</style>
</head>

<body>

<header>
    <strong>📰 Novidades (últimos <?= $novidadeDias ?> dias)</strong>
    <nav style="display:inline-block; margin-left:20px;">
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

<div class="sidebar">
    <h3>Categorias</h3>

    <?php foreach ($categorias as $c): ?>
        <a href="?categoria=<?= $c['idCategoria'] ?>">
            <?= htmlspecialchars($c['nomeCategoria']) ?>
        </a>
    <?php endforeach; ?>

    <a href="index.php">Mostrar tudo</a>
</div>

<main>
<h2>Produtos Recentes</h2>

<?php if (count($produtosRecentes) === 0): ?>
    <p>Nenhum produto novo encontrado.</p>
<?php else: ?>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Categoria</th>
    <th>Subcategoria</th>
    <th>Imagem</th>
    <th>Link</th>
    <th>Cadastrado</th>
    <th>Dias</th>
</tr>
</thead>
<tbody>

<?php foreach ($produtosRecentes as $p): ?>
<tr>
    <td><?= $p['idProduto'] ?></td>
    <td>
        <?= htmlspecialchars($p['nomeProduto']) ?>
        <span class="badge">NOVO</span>
    </td>
    <td><?= htmlspecialchars($p['nomeCategoria']) ?></td>
    <td><?= htmlspecialchars($p['nomeSubcategoria']) ?></td>
    <td>
        <?php if (!empty($p['imagemProduto'])): ?>
            <img src="<?= $p['imagemProduto'] ?>">
        <?php endif; ?>
    </td>
    <td>
        <a href="<?= $p['linkProduto'] ?>" target="_blank">Abrir</a>
    </td>
    <td><?= date("d/m/Y", strtotime($p['dataCadastro'])) ?></td>
    <td><?= $p['diasPassados'] ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<?php endif; ?>
</main>

</body>
</html>
