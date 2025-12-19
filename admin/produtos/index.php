<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ==========================
   Carregar dados base
========================== */
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$lojas = $pdo->query("SELECT * FROM lojas ORDER BY nomeLoja ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   ADICIONAR PRODUTO
========================== */
if (isset($_POST['add_produto'])) {

    $idCategoria = $_POST['idCategoria'];
    $idSubcategoria = $_POST['idSubcategoria'] !== "" ? $_POST['idSubcategoria'] : null;
    $idLoja = $_POST['idLoja'];
    $nomeProduto = $_POST['nomeProduto'];
    $descricaoProduto = $_POST['descricaoProduto'];
    $precoProduto = $_POST['precoProduto'] !== "" ? $_POST['precoProduto'] : null;
    $imagemProduto = $_POST['imagemProduto'];
    $linkProduto = $_POST['linkProduto'];

    $categoriaTexto = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=$idCategoria")->fetchColumn();
    $subcategoriaTexto = $idSubcategoria
        ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=$idSubcategoria")->fetchColumn()
        : null;
    $lojaTexto = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=$idLoja")->fetchColumn();

    $stmt = $pdo->prepare("
        INSERT INTO produtos
        (idCategoria, idSubcategoria, idLoja, nomeProduto, descricaoProduto, precoProduto,
         imagemProduto, categoriaProduto, subcategoriaProduto, lojaProduto, linkProduto, dataCadastro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $idCategoria, $idSubcategoria, $idLoja, $nomeProduto, $descricaoProduto,
        $precoProduto, $imagemProduto, $categoriaTexto, $subcategoriaTexto,
        $lojaTexto, $linkProduto
    ]);

    header("Location: index.php");
    exit;
}

/* ==========================
   EDITAR PRODUTO
========================== */
if (isset($_POST['edit_produto'])) {

    $idProduto = $_POST['idProduto'];
    $idCategoria = $_POST['idCategoria'];
    $idSubcategoria = $_POST['idSubcategoria'] !== "" ? $_POST['idSubcategoria'] : null;
    $idLoja = $_POST['idLoja'];
    $nomeProduto = $_POST['nomeProduto'];
    $descricaoProduto = $_POST['descricaoProduto'];
    $precoProduto = $_POST['precoProduto'] !== "" ? $_POST['precoProduto'] : null;
    $imagemProduto = $_POST['imagemProduto'];
    $linkProduto = $_POST['linkProduto'];

    $categoriaTexto = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=$idCategoria")->fetchColumn();
    $subcategoriaTexto = $idSubcategoria
        ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=$idSubcategoria")->fetchColumn()
        : null;
    $lojaTexto = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=$idLoja")->fetchColumn();

    $stmt = $pdo->prepare("
        UPDATE produtos SET
            idCategoria=?, idSubcategoria=?, idLoja=?, nomeProduto=?, descricaoProduto=?,
            precoProduto=?, imagemProduto=?, categoriaProduto=?, subcategoriaProduto=?,
            lojaProduto=?, linkProduto=?
        WHERE idProduto=?
    ");

    $stmt->execute([
        $idCategoria, $idSubcategoria, $idLoja, $nomeProduto, $descricaoProduto,
        $precoProduto, $imagemProduto, $categoriaTexto, $subcategoriaTexto,
        $lojaTexto, $linkProduto, $idProduto
    ]);

    header("Location: index.php");
    exit;
}

/* ==========================
   DELETAR MÚLTIPLOS
========================== */
if (isset($_POST['delete_selected']) && !empty($_POST['selecionados'])) {
    $ids = implode(",", array_map("intval", $_POST['selecionados']));
    $pdo->query("DELETE FROM produtos WHERE idProduto IN ($ids)");
    header("Location: index.php");
    exit;
}

/* ==========================
   FILTROS
========================== */
$busca = $_GET['busca'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';
$filtroSubcategoria = $_GET['subcategoria'] ?? '';

$sql = "SELECT * FROM produtos WHERE 1=1";
$params = [];

if ($busca) {
    $sql .= " AND nomeProduto LIKE ?";
    $params[] = "%$busca%";
}

if ($filtroCategoria) {
    $sql .= " AND idCategoria = ?";
    $params[] = $filtroCategoria;
}

if ($filtroSubcategoria) {
    $sql .= " AND idSubcategoria = ?";
    $params[] = $filtroSubcategoria;
}

$sql .= " ORDER BY idProduto DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Produtos</title>
<link rel="stylesheet" href="../../assets/css/admin.css">

<style>
main.layout-admin { display:flex; gap:20px; }
aside.sidebar {
    width:260px; background:#f5f5f5; padding:15px; border-radius:6px;
}
aside.sidebar h3 { margin:15px 0 8px; }
aside.sidebar a {
    display:block; padding:8px; margin-bottom:5px;
    background:#fff; border-radius:4px; text-decoration:none; color:#000;
}
aside.sidebar a:hover { background:#eaeaea; }
aside.sidebar input, aside.sidebar select {
    width:100%; padding:8px; margin-bottom:10px;
}
section.conteudo { flex:1; }

.thumb { max-width:100px; max-height:100px; border-radius:5px; object-fit:cover; }

.modal {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.5);
    justify-content:center; align-items:center; z-index:1000;
}
.modal-content {
    background:#fff; padding:20px;
    max-width:600px; width:90%; border-radius:10px;
}
</style>
</head>

<body>

<header>
    <h1>Gerenciar Produtos</h1>
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
    <h3>Buscar Produto</h3>
    <form method="GET">
        <input type="text" name="busca" placeholder="Nome do produto..." value="<?= htmlspecialchars($busca) ?>">

        <select name="categoria" onchange="this.form.submit()">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['idCategoria'] ?>" <?= $filtroCategoria == $c['idCategoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nomeCategoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="subcategoria">
            <option value="">Todas as subcategorias</option>
            <?php foreach ($subcategorias as $s): ?>
                <?php if (!$filtroCategoria || $s['idCategoria'] == $filtroCategoria): ?>
                    <option value="<?= $s['idSubcategoria'] ?>" <?= $filtroSubcategoria == $s['idSubcategoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nomeSubcategoria']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <button type="submit">🔍 Filtrar</button>
    </form>

    <h3>Categorias</h3>
    <a href="index.php">📦 Mostrar tudo</a>
    <?php foreach ($categorias as $c): ?>
        <a href="?categoria=<?= $c['idCategoria'] ?>"><?= htmlspecialchars($c['nomeCategoria']) ?></a>
    <?php endforeach; ?>
</aside>

<section class="conteudo">

<h2>Lista de Produtos</h2>

<form method="POST">

<button type="button" class="btn-add" onclick="abrirModalAdicionar()">➕ Adicionar Produto</button>
<button type="submit" name="delete_selected">🗑 Deletar Selecionados</button>

<table>
<thead>
<tr>
<th><input type="checkbox" onclick="toggleAll(this)"></th>
<th>ID</th>
<th>Nome</th>
<th>Imagem</th>
<th>Preço</th>
<th>Categoria</th>
<th>Subcategoria</th>
<th>Loja</th>
<th>Link</th>
<th>Ações</th>
</tr>
</thead>

<tbody>
<?php foreach ($produtos as $p): ?>
<tr>
<td><input type="checkbox" name="selecionados[]" value="<?= $p['idProduto'] ?>"></td>
<td><?= $p['idProduto'] ?></td>
<td><?= htmlspecialchars($p['nomeProduto']) ?></td>
<td><img src="<?= $p['imagemProduto'] ?>" class="thumb"></td>
<td><?= $p['precoProduto'] === null ? '—' : 'R$ '.number_format($p['precoProduto'],2,',','.') ?></td>
<td><?= htmlspecialchars($p['categoriaProduto']) ?></td>
<td><?= htmlspecialchars($p['subcategoriaProduto'] ?? '—') ?></td>
<td><?= htmlspecialchars($p['lojaProduto']) ?></td>
<td><a href="<?= $p['linkProduto'] ?>" target="_blank">Abrir</a></td>
<td><button type="button" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>✏ Editar</button></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</form>
</section>
</main>

<script>
function toggleAll(src){document.querySelectorAll("tbody input[type=checkbox]").forEach(c=>c.checked=src.checked);}
function openAdd(){document.getElementById("modalAdd").style.display="flex";}
function closeAdd(){document.getElementById("modalAdd").style.display="none";}
function openEdit(d){
    document.getElementById("modalEdit").style.display="flex";
    edit_idProduto.value=d.idProduto;
    edit_nomeProduto.value=d.nomeProduto;
    edit_descricaoProduto.value=d.descricaoProduto;
    edit_precoProduto.value=d.precoProduto;
    edit_imagemProduto.value=d.imagemProduto;
    edit_linkProduto.value=d.linkProduto;
    edit_idCategoria.value=d.idCategoria;
    filterSubEdit();
    edit_idSubcategoria.value=d.idSubcategoria;
    edit_idLoja.value=d.idLoja;
}
function closeEdit(){document.getElementById("modalEdit").style.display="none";}
function filterSubEdit(){
    let c=edit_idCategoria.value;
    document.querySelectorAll("#edit_idSubcategoria option").forEach(o=>{
        o.style.display=o.value==""||o.dataset.cat==c?"block":"none";
    });
}
</script>

</body>
</html>

