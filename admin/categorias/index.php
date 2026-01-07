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
        $stmt = $pdo->prepare("INSERT INTO categorias (nomeCategoria) VALUES (?)");
        $stmt->execute([$_POST['nomeCategoria']]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "editar") {
        $stmt = $pdo->prepare("
            UPDATE categorias 
            SET nomeCategoria = ?
            WHERE idCategoria = ?
        ");
        $stmt->execute([
            $_POST['nomeCategoria'],
            $_POST['idCategoria']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "deletarMultiplas" && !empty($_POST['selecionadas'])) {
        $placeholders = implode(',', array_fill(0, count($_POST['selecionadas']), '?'));
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE idCategoria IN ($placeholders)");
        $stmt->execute($_POST['selecionadas']);
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$buscaCategoria  = $_GET['busca'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';

$sql = "SELECT * FROM categorias WHERE 1=1";
$params = [];

if (!empty($buscaCategoria)) {
    $sql .= " AND nomeCategoria LIKE ?";
    $params[] = "%$buscaCategoria%";
}

if (!empty($filtroCategoria)) {
    $sql .= " AND nomeCategoria = ?";
    $params[] = $filtroCategoria;
}

$sql .= " ORDER BY nomeCategoria ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$listaCategorias = $pdo->query(
    "SELECT nomeCategoria FROM categorias ORDER BY nomeCategoria ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Categorias</title>
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
    border-radius: 6px;
    min-width: 320px;
}
</style>
</head>

<body>

<header>
<h1>Gerenciar Categorias</h1>
<nav>
    <a href="../dashboard/">🏠 Dashboard</a>
    <a href="../produtos/">📦 Produtos</a>
    <a href="../promocoes/">💰 Promoções</a>
    <a href="../novidades/">📰 Novidades</a>
    <a href="../lojas/">🏪 Lojas</a>
    <a href="../layout/">🧩 Layouts</a>
    <a href="../components/">🧱 Components</a>
    <a href="../categorias/">📂 Categorias</a>
    <a href="../subcategorias/">📁 Subcategorias</a>
    <a href="../logout.php">🚪 Sair</a>
</nav>

</header>

<main class="layout-admin">

<!-- SIDEBAR -->
<aside class="sidebar">
<form method="GET">
    <h3>Buscar Categoria</h3>

    <input type="text" name="busca" placeholder="Nome da categoria..."
           value="<?= htmlspecialchars($buscaCategoria) ?>">

    <select name="categoria">
        <option value="">Todas categorias</option>
        <?php foreach ($listaCategorias as $c): ?>
            <option value="<?= htmlspecialchars($c['nomeCategoria']) ?>"
                <?= $filtroCategoria === $c['nomeCategoria'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nomeCategoria']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">🔍 Buscar</button>
</form>
</aside>

<section class="conteudo">

<h2>Lista de Categorias</h2>

<form method="POST">
<input type="hidden" name="acao" value="deletarMultiplas">

<div class="botoes">
    <button type="button" class="btn-add" onclick="abrirModalAdicionar()">
        ➕ Adicionar Categoria
    </button>
    <button type="button" class="btn-delete" id="btnDelete">
        🗑️ Deletar Selecionadas
    </button>
</div>

<table>
<thead>
<tr>
    <th><input type="checkbox" id="checkAll"></th>
    <th>ID</th>
    <th>Nome</th>
    <th>Ações</th>
</tr>
</thead>
<tbody>

<?php foreach ($categorias as $c): ?>
<tr>
    <td><input type="checkbox" name="selecionadas[]" value="<?= $c['idCategoria'] ?>"></td>
    <td><?= $c['idCategoria'] ?></td>
    <td><?= htmlspecialchars($c['nomeCategoria']) ?></td>
    <td>
        <button type="button"
            onclick="abrirModalEditar(
                '<?= $c['idCategoria'] ?>',
                '<?= htmlspecialchars($c['nomeCategoria'], ENT_QUOTES) ?>'
            )">
            ✏️
        </button>
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
<h3>Adicionar Categoria</h3>
<form method="POST">
    <input type="hidden" name="acao" value="salvar">
    <input type="text" name="nomeCategoria" required>
    <br><br>
    <button type="submit">Salvar</button>
    <button type="button" onclick="fecharModais()">Cancelar</button>
</form>
</div>
</div>

<div id="modalEditar" class="modal">
<div class="modal-content">
<h3>Editar Categoria</h3>
<form method="POST">
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="idCategoria" id="editId">
    <input type="text" name="nomeCategoria" id="editNome" required>
    <br><br>
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

document.getElementById('btnDelete').addEventListener('click', function () {
    const form = document.querySelector('form[method="POST"]');
    const selecionados = form.querySelectorAll('input[name="selecionadas[]"]:checked');
    if (selecionados.length > 0) {
        form.submit();
    }
});

function abrirModalAdicionar() {
    document.getElementById('modalAdicionar').style.display = 'flex';
}

function abrirModalEditar(id, nome) {
    document.getElementById('editId').value = id;
    document.getElementById('editNome').value = nome;
    document.getElementById('modalEditar').style.display = 'flex';
}

function fecharModais() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
}
</script>

</body>
</html>
