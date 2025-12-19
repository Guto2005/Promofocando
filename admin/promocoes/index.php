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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    if ($_POST['acao'] === "salvar") {
        $stmt = $pdo->prepare("
            INSERT INTO promocoes 
            (idProduto, precoPromocional, dataInicio, dataFim, ativo, linkPromocao)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['idProduto'],
            $_POST['precoPromocional'],
            $_POST['dataInicio'],
            $_POST['dataFim'],
            $_POST['ativo'],
            $_POST['linkPromocao']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "editar") {
        $stmt = $pdo->prepare("
            UPDATE promocoes SET
                idProduto=?,
                precoPromocional=?,
                dataInicio=?,
                dataFim=?,
                ativo=?,
                linkPromocao=?
            WHERE idPromocao=?
        ");
        $stmt->execute([
            $_POST['idProduto'],
            $_POST['precoPromocional'],
            $_POST['dataInicio'],
            $_POST['dataFim'],
            $_POST['ativo'],
            $_POST['linkPromocao'],
            $_POST['idPromocao']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "deletarMultiplas" && !empty($_POST['selecionadas'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionadas']));
        $pdo->query("DELETE FROM promocoes WHERE idPromocao IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$filtroCategoria = $_GET['categoria'] ?? null;
$filtroStatus    = $_GET['status'] ?? null;
$buscaProduto    = $_GET['busca'] ?? null;

/* ================================
   BUSCA PROMOÇÕES
================================ */
$sql = "
    SELECT 
        p.*,
        pr.nomeProduto,
        c.nomeCategoria
    FROM promocoes p
    JOIN produtos pr ON pr.idProduto = p.idProduto
    JOIN categorias c ON c.idCategoria = pr.idCategoria
    WHERE 1=1
";
$params = [];

if (!empty($filtroCategoria)) {
    $sql .= " AND c.idCategoria = ?";
    $params[] = $filtroCategoria;
}

if ($filtroStatus !== null && $filtroStatus !== '') {
    $sql .= " AND p.ativo = ?";
    $params[] = $filtroStatus;
}

if (!empty($buscaProduto)) {
    $sql .= " AND pr.nomeProduto LIKE ?";
    $params[] = "%$buscaProduto%";
}

$sql .= " ORDER BY p.idPromocao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$promocoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   CATEGORIAS (SIDEBAR)
================================ */
$categorias = $pdo->query("
    SELECT idCategoria, nomeCategoria
    FROM categorias
    ORDER BY nomeCategoria
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   PRODUTOS (MODAL)
================================ */
$produtos = $pdo->query("
    SELECT idProduto, nomeProduto 
    FROM produtos 
    ORDER BY nomeProduto
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Promoções</title>
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

aside.sidebar input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
}

section.conteudo {
    flex: 1;
}

.btn-add {
    background: #2ecc71;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 5px;
    cursor: pointer;
    margin-bottom: 15px;
}

.btn-add:hover {
    background: #27ae60;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-content {
    background: #fff;
    padding: 20px;
    width: 420px;
    border-radius: 8px;
}

.modal-content label {
    display: block;
    margin-top: 10px;
}

.modal-content input,
.modal-content select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
}
</style>
</head>

<body>

<header>
    <h1>Gerenciar Promoções</h1>
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
    <h3>Status</h3>
    <a href="index.php">📋 Todas</a>
    <a href="?status=1">✅ Ativas</a>
    <a href="?status=0">❌ Inativas</a>

    <h3>Buscar Produto</h3>
    <form method="GET">
        <input type="text" name="busca" placeholder="Nome do produto..." value="<?= htmlspecialchars($buscaProduto ?? '') ?>">
        <button type="submit">🔍 Buscar</button>
    </form>

    <h3>Categorias</h3>
    <?php foreach ($categorias as $c): ?>
        <a href="?categoria=<?= $c['idCategoria'] ?>">
            📂 <?= htmlspecialchars($c['nomeCategoria']) ?>
        </a>
    <?php endforeach; ?>
</aside>

<section class="conteudo">

<button class="btn-add" onclick="abrirModalNova()">➕ Nova Promoção</button>

<form method="POST">
<input type="hidden" name="acao" value="deletarMultiplas">

<table>
<thead>
<tr>
<th><input type="checkbox" id="checkAll"></th>
<th>ID</th>
<th>Categoria</th>
<th>Produto</th>
<th>Preço</th>
<th>Status</th>
<th>Ações</th>
</tr>
</thead>
<tbody>
<?php foreach ($promocoes as $p): ?>
<tr>
<td><input type="checkbox" name="selecionadas[]" value="<?= $p['idPromocao'] ?>"></td>
<td><?= $p['idPromocao'] ?></td>
<td><?= htmlspecialchars($p['nomeCategoria']) ?></td>
<td><?= htmlspecialchars($p['nomeProduto']) ?></td>
<td>R$ <?= number_format($p['precoPromocional'], 2, ',', '.') ?></td>
<td><?= $p['ativo'] ? '✅ Ativa' : '❌ Inativa' ?></td>
<td>
<button type="button" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>✏️ Editar</button>
</td>
</tr>
<button class="btn-delete">🗑️ Deletar Selecionadas</button>
<?php endforeach; ?>
</tbody>
</table>

</form>

</section>
</main>

<!-- MODAL -->
<div id="modalPromocao" class="modal">
<div class="modal-content">
<h2 id="tituloModal"></h2>

<form method="POST">
<input type="hidden" name="acao" id="acaoModal">
<input type="hidden" name="idPromocao" id="idPromocao">

<label>Produto</label>
<select name="idProduto" id="idProduto" required>
<?php foreach ($produtos as $pr): ?>
<option value="<?= $pr['idProduto'] ?>"><?= htmlspecialchars($pr['nomeProduto']) ?></option>
<?php endforeach; ?>
</select>

<label>Preço Promocional</label>
<input type="number" step="0.01" name="precoPromocional" id="precoPromocional" required>

<label>Data Início</label>
<input type="date" name="dataInicio" id="dataInicio" required>

<label>Data Fim</label>
<input type="date" name="dataFim" id="dataFim" required>

<label>Status</label>
<select name="ativo" id="ativo">
<option value="1">Ativa</option>
<option value="0">Inativa</option>
</select>

<label>Link</label>
<input type="text" name="linkPromocao" id="linkPromocao">

<div class="modal-actions">
<button type="submit">💾 Salvar</button>
<button type="button" onclick="fecharModal()">❌ Cancelar</button>
</div>
</form>
</div>
</div>

<script>
const modal = document.getElementById('modalPromocao');

function abrirModalNova() {
    document.getElementById('tituloModal').innerText = 'Nova Promoção';
    document.getElementById('acaoModal').value = 'salvar';
    document.querySelector('form').reset();
    modal.style.display = 'flex';
}

function abrirModalEditar(p) {
    document.getElementById('tituloModal').innerText = 'Editar Promoção';
    document.getElementById('acaoModal').value = 'editar';

    idPromocao.value = p.idPromocao;
    idProduto.value = p.idProduto;
    precoPromocional.value = p.precoPromocional;
    dataInicio.value = p.dataInicio;
    dataFim.value = p.dataFim;
    ativo.value = p.ativo;
    linkPromocao.value = p.linkPromocao;

    modal.style.display = 'flex';
}

function fecharModal() {
    modal.style.display = 'none';
}

document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="selecionadas[]"]').forEach(c => {
        c.checked = this.checked;
    });
});
</script>

</body>
</html>

