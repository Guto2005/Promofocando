<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ================================
   AÇÕES
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ADICIONAR */
    if (isset($_POST['acao']) && $_POST['acao'] === 'salvar') {

        $stmt = $pdo->prepare("
            INSERT INTO produtos
            (idCategoria, idSubcategoria, idLoja, nomeProduto, descricaoProduto,
             precoProduto, imagemProduto, categoriaProduto, subcategoriaProduto,
             lojaProduto, linkProduto, dataCadastro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $categoriaTxt = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=".$_POST['idCategoria'])->fetchColumn();
        $subTxt = $_POST['idSubcategoria']
            ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=".$_POST['idSubcategoria'])->fetchColumn()
            : null;
        $lojaTxt = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=".$_POST['idLoja'])->fetchColumn();

        $stmt->execute([
            $_POST['idCategoria'],
            $_POST['idSubcategoria'] ?: null,
            $_POST['idLoja'],
            $_POST['nomeProduto'],
            $_POST['descricaoProduto'],
            $_POST['precoProduto'] ?: null,
            $_POST['imagemProduto'],
            $categoriaTxt,
            $subTxt,
            $lojaTxt,
            $_POST['linkProduto']
        ]);

        header("Location: index.php");
        exit;
    }

    /* EDITAR */
    if (isset($_POST['acao']) && $_POST['acao'] === 'editar') {

        $stmt = $pdo->prepare("
            UPDATE produtos SET
                idCategoria=?, idSubcategoria=?, idLoja=?, nomeProduto=?,
                descricaoProduto=?, precoProduto=?, imagemProduto=?,
                categoriaProduto=?, subcategoriaProduto=?, lojaProduto=?,
                linkProduto=?
            WHERE idProduto=?
        ");

        $categoriaTxt = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=".$_POST['idCategoria'])->fetchColumn();
        $subTxt = $_POST['idSubcategoria']
            ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=".$_POST['idSubcategoria'])->fetchColumn()
            : null;
        $lojaTxt = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=".$_POST['idLoja'])->fetchColumn();

        $stmt->execute([
            $_POST['idCategoria'],
            $_POST['idSubcategoria'] ?: null,
            $_POST['idLoja'],
            $_POST['nomeProduto'],
            $_POST['descricaoProduto'],
            $_POST['precoProduto'] ?: null,
            $_POST['imagemProduto'],
            $categoriaTxt,
            $subTxt,
            $lojaTxt,
            $_POST['linkProduto'],
            $_POST['idProduto']
        ]);

        header("Location: index.php");
        exit;
    }

    /* DELETAR MÚLTIPLOS */
    if (isset($_POST['acao']) && $_POST['acao'] === 'deletarMultiplos' && !empty($_POST['selecionados'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionados']));
        $pdo->query("DELETE FROM produtos WHERE idProduto IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$busca       = $_GET['busca'] ?? null;
$categoria   = $_GET['categoria'] ?? null;
$subcategoria= $_GET['subcategoria'] ?? null;
$loja        = $_GET['loja'] ?? null;

$sql = "SELECT * FROM produtos WHERE 1=1";
$params = [];

if ($busca) {
    $sql .= " AND nomeProduto LIKE ?";
    $params[] = "%$busca%";
}
if ($categoria) {
    $sql .= " AND idCategoria = ?";
    $params[] = $categoria;
}
if ($subcategoria) {
    $sql .= " AND idSubcategoria = ?";
    $params[] = $subcategoria;
}
if ($loja) {
    $sql .= " AND idLoja = ?";
    $params[] = $loja;
}

$sql .= " ORDER BY idProduto DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   BASE
================================ */
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria")->fetchAll(PDO::FETCH_ASSOC);
$lojas = $pdo->query("SELECT * FROM lojas ORDER BY nomeLoja")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Produtos</title>
<link rel="stylesheet" href="../../assets/css/admin.css">

<style>
main.layout-admin{display:flex;gap:20px}
aside.sidebar{width:260px;background:#f5f5f5;padding:15px;border-radius:6px}
aside.sidebar h3{margin:15px 0 8px}
aside.sidebar a{display:block;padding:8px;margin-bottom:5px;background:#fff;border-radius:4px;text-decoration:none;color:#000}
aside.sidebar a:hover{background:#eaeaea}
aside.sidebar input{width:100%;padding:8px;margin-bottom:10px}
section.conteudo{flex:1}

.thumb{max-width:80px;border-radius:5px}

.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;z-index:999}
.modal-content{background:#fff;padding:20px;width:520px;border-radius:8px}
.modal-content label{display:block;margin-top:10px}
.modal-content input,.modal-content textarea,.modal-content select{width:100%;padding:8px;margin-top:5px}
.modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:15px}
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
<h3>Buscar</h3>
<form method="GET">
<input type="text" name="busca" placeholder="Nome do produto..." value="<?= htmlspecialchars($busca ?? '') ?>">
<button type="submit">🔍 Buscar</button>
</form>

<h3>Categorias</h3>
<a href="index.php">📦 Todas</a>
<?php foreach ($categorias as $c): ?>
<a href="?categoria=<?= $c['idCategoria'] ?>">📂 <?= htmlspecialchars($c['nomeCategoria']) ?></a>
<?php endforeach; ?>

<h3>Lojas</h3>
<?php foreach ($lojas as $l): ?>
<a href="?loja=<?= $l['idLoja'] ?>">🏪 <?= htmlspecialchars($l['nomeLoja']) ?></a>
<?php endforeach; ?>
</aside>

<section class="conteudo">

<h2>Lista de Produtos</h2>

<div class="botoes">
<button class="btn-add" onclick="abrirModalNovo()">➕ Novo Produto</button>
<button class="btn-delete">🗑️ Deletar Selecionados</button>
</div>

<form method="POST">
<input type="hidden" name="acao" value="deletarMultiplos">

<table>
<thead>
<tr>
<th><input type="checkbox" id="checkAll"></th>
<th>ID</th>
<th>Produto</th>
<th>Imagem</th>
<th>Preço</th>
<th>Categoria</th>
<th>Loja</th>
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
<td><?= $p['precoProduto'] ? 'R$ '.number_format($p['precoProduto'],2,',','.') : '—' ?></td>
<td><?= htmlspecialchars($p['categoriaProduto']) ?></td>
<td><?= htmlspecialchars($p['lojaProduto']) ?></td>
<td><button type="button" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>✏️ Editar</button></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</form>

</section>
</main>

<!-- MODAL -->
<div id="modalProduto" class="modal">
<div class="modal-content">
<h2 id="tituloModal"></h2>

<form method="POST">
<input type="hidden" name="acao" id="acao">
<input type="hidden" name="idProduto" id="idProduto">

<label>Nome</label>
<input type="text" name="nomeProduto" id="nomeProduto" required>

<label>Descrição</label>
<textarea name="descricaoProduto" id="descricaoProduto"></textarea>

<label>Preço</label>
<input type="number" step="0.01" name="precoProduto" id="precoProduto">

<label>Imagem (URL)</label>
<input type="text" name="imagemProduto" id="imagemProduto">

<label>Categoria</label>
<select name="idCategoria" id="idCategoria" required>
<?php foreach ($categorias as $c): ?>
<option value="<?= $c['idCategoria'] ?>"><?= htmlspecialchars($c['nomeCategoria']) ?></option>
<?php endforeach; ?>
</select>

<label>Loja</label>
<select name="idLoja" id="idLoja" required>
<?php foreach ($lojas as $l): ?>
<option value="<?= $l['idLoja'] ?>"><?= htmlspecialchars($l['nomeLoja']) ?></option>
<?php endforeach; ?>
</select>

<label>Link</label>
<input type="text" name="linkProduto" id="linkProduto">

<div class="modal-actions">
<button type="submit">💾 Salvar</button>
<button type="button" onclick="fecharModal()">❌ Cancelar</button>
</div>
</form>
</div>
</div>

<script>
const modal=document.getElementById('modalProduto');

function abrirModalNovo(){
tituloModal.innerText='Novo Produto';
acao.value='salvar';
modal.style.display='flex';
document.querySelector('#modalProduto form').reset();
}
function abrirModalEditar(p){
tituloModal.innerText='Editar Produto';
acao.value='editar';
idProduto.value=p.idProduto;
nomeProduto.value=p.nomeProduto;
descricaoProduto.value=p.descricaoProduto;
precoProduto.value=p.precoProduto;
imagemProduto.value=p.imagemProduto;
idCategoria.value=p.idCategoria;
idLoja.value=p.idLoja;
linkProduto.value=p.linkProduto;
modal.style.display='flex';
}
function fecharModal(){modal.style.display='none';}
document.getElementById('checkAll').addEventListener('change',e=>{
document.querySelectorAll("input[name='selecionados[]']").forEach(c=>c.checked=e.target.checked);
});
</script>

</body>
</html>
