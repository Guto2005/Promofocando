<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ================================
   CARREGAR CATEGORIAS
================================ */
$categorias = $pdo->query("
    SELECT idCategoria, nomeCategoria
    FROM categorias
    ORDER BY nomeCategoria ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   AÇÕES
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    if ($_POST['acao'] === "salvar") {
        $stmt = $pdo->prepare("
            INSERT INTO subcategorias (nomeSubcategoria, idCategoria)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $_POST['nomeSubcategoria'],
            $_POST['idCategoria']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "editar") {
        $stmt = $pdo->prepare("
            UPDATE subcategorias
            SET nomeSubcategoria=?, idCategoria=?
            WHERE idSubcategoria=?
        ");
        $stmt->execute([
            $_POST['nomeSubcategoria'],
            $_POST['idCategoria'],
            $_POST['idSubcategoria']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "deletarMultiplas" && !empty($_POST['selecionadas'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionadas']));
        $pdo->query("DELETE FROM subcategorias WHERE idSubcategoria IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$busca = $_GET['busca'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';

$sql = "
    SELECT s.*, c.nomeCategoria
    FROM subcategorias s
    INNER JOIN categorias c ON c.idCategoria = s.idCategoria
    WHERE 1=1
";

$params = [];

if ($busca !== '') {
    $sql .= " AND s.nomeSubcategoria LIKE ?";
    $params[] = "%$busca%";
}

if ($filtroCategoria !== '') {
    $sql .= " AND s.idCategoria = ?";
    $params[] = $filtroCategoria;
}

$sql .= " ORDER BY s.nomeSubcategoria ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Subcategorias</title>
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
aside.sidebar input,
aside.sidebar select,
aside.sidebar button {
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
}
.btn-add:hover {
    background: #27ae60;
}
</style>
</head>

<body>

<header>
    <h1>Gerenciar Subcategorias</h1>
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

<!-- SIDEBAR -->
<aside class="sidebar">
<form method="GET">
    <h3>Buscar</h3>

    <input type="text" name="busca"
           placeholder="Nome da subcategoria..."
           value="<?= htmlspecialchars($busca) ?>">

    <select name="categoria">
        <option value="">Todas as categorias</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['idCategoria'] ?>"
                <?= ($filtroCategoria == $c['idCategoria']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nomeCategoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">🔍 Buscar</button>
</form>
</aside>

<!-- CONTEÚDO -->
<section class="conteudo">

<h2>Subcategorias</h2>

<button type="button" class="btn-add" onclick="abrirModalAdicionar()">
    ➕ Adicionar Subcategoria
</button>

<form method="POST" id="formDelete">
<input type="hidden" name="acao" value="deletarMultiplas">

<button type="button" class="btn-delete" onclick="deletarSelecionadas()">
    🗑️ Deletar Selecionadas
</button>

<table>
<thead>
<tr>
    <th><input type="checkbox" id="checkAll"></th>
    <th>ID</th>
    <th>Categoria</th>
    <th>SubCategoria</th>
    <th>Ações</th>
</tr>
</thead>
<tbody>
<?php foreach ($subcategorias as $s): ?>
<tr>
<td><input type="checkbox" name="selecionadas[]" value="<?= $s['idSubcategoria'] ?>"></td>
<td><?= $s['idSubcategoria'] ?></td>
<td><?= htmlspecialchars($s['nomeCategoria']) ?></td>
<td><?= htmlspecialchars($s['nomeSubcategoria']) ?></td>
<td>
<button type="button"
onclick="abrirModalEditar(
    '<?= $s['idSubcategoria'] ?>',
    '<?= htmlspecialchars($s['nomeSubcategoria'], ENT_QUOTES) ?>',
    '<?= $s['idCategoria'] ?>'
)">✏️</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</form>

</section>
</main>

<!-- MODAIS -->
<div id="modalAdicionar" class="modal">
<div class="modal-content">
<h3>Adicionar Subcategoria</h3>
<form method="POST">
<input type="hidden" name="acao" value="salvar">

<input type="text" name="nomeSubcategoria" required>

<select name="idCategoria" required>
<?php foreach ($categorias as $c): ?>
<option value="<?= $c['idCategoria'] ?>">
<?= htmlspecialchars($c['nomeCategoria']) ?>
</option>
<?php endforeach; ?>
</select>

<button type="submit">Salvar</button>
<button type="button" onclick="fecharModais()">Cancelar</button>
</form>
</div>
</div>

<div id="modalEditar" class="modal">
<div class="modal-content">
<h3>Editar Subcategoria</h3>
<form method="POST">
<input type="hidden" name="acao" value="editar">
<input type="hidden" name="idSubcategoria" id="editId">

<input type="text" name="nomeSubcategoria" id="editNome" required>

<select name="idCategoria" id="editCategoria" required>
<?php foreach ($categorias as $c): ?>
<option value="<?= $c['idCategoria'] ?>">
<?= htmlspecialchars($c['nomeCategoria']) ?>
</option>
<?php endforeach; ?>
</select>

<button type="submit">Salvar</button>
<button type="button" onclick="fecharModais()">Cancelar</button>
</form>
</div>
</div>

<script>
document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="selecionadas[]"]').forEach(c => {
        c.checked = this.checked;
    });
});

function deletarSelecionadas() {
    const marcados = document.querySelectorAll('input[name="selecionadas[]"]:checked');
    if (marcados.length === 0) return;
    document.getElementById('formDelete').submit();
}

function abrirModalAdicionar() {
    document.getElementById('modalAdicionar').style.display = 'flex';
}

function abrirModalEditar(id, nome, categoria) {
    editId.value = id;
    editNome.value = nome;
    editCategoria.value = categoria;
    modalEditar.style.display = 'flex';
}

function fecharModais() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
}
</script>

</body>
</html>
