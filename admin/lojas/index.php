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
            INSERT INTO lojas (nomeLoja, siteLoja, logoLoja)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nomeLoja'],
            $_POST['siteLoja'],
            $_POST['logoLoja']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "editar") {
        $stmt = $pdo->prepare("
            UPDATE lojas SET
                nomeLoja=?,
                siteLoja=?,
                logoLoja=?
            WHERE idLoja=?
        ");
        $stmt->execute([
            $_POST['nomeLoja'],
            $_POST['siteLoja'],
            $_POST['logoLoja'],
            $_POST['idLoja']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "deletarMultiplas" && !empty($_POST['selecionadas'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionadas']));
        $pdo->query("DELETE FROM lojas WHERE idLoja IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$buscaLoja  = $_GET['busca'] ?? '';
$filtroLoja = $_GET['loja'] ?? '';

/* ================================
   BUSCA LOJAS
================================ */
$sql = "SELECT * FROM lojas WHERE 1=1";
$params = [];

if (!empty($buscaLoja)) {
    $sql .= " AND nomeLoja LIKE ?";
    $params[] = "%$buscaLoja%";
}

if (!empty($filtroLoja)) {
    $sql .= " AND nomeLoja = ?";
    $params[] = $filtroLoja;
}

$sql .= " ORDER BY nomeLoja ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   DROPDOWN DE LOJAS
================================ */
$listaLojas = $pdo->query("
    SELECT nomeLoja FROM lojas ORDER BY nomeLoja ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Lojas</title>
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
.logo-thumb {
    max-width: 60px;
    max-height: 60px;
    object-fit: contain;
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
.modal-content input {
    width: 100%;
    padding: 8px;
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
    <h1>Gerenciar Lojas</h1>
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
    <h3>Buscar Loja</h3>

    <input type="text" name="busca" placeholder="Nome da loja..."
           value="<?= htmlspecialchars($buscaLoja) ?>">

    <select name="loja">
        <option value="">Todas as lojas</option>
        <?php foreach ($listaLojas as $l): ?>
            <option value="<?= htmlspecialchars($l['nomeLoja']) ?>"
                <?= $filtroLoja === $l['nomeLoja'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nomeLoja']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">🔍 Buscar</button>
</form>
</aside>

<!-- CONTEÚDO -->
<section class="conteudo">

<h2>Lista de Lojas</h2>

<div class="botoes">
    <button class="btn-add" onclick="abrirModalNova()">➕ Nova Loja</button>
    <button type="button" class="btn-delete">🗑️ Deletar Selecionadas</button>
</div>

<form method="POST">
<input type="hidden" name="acao" value="deletarMultiplas">

<table>
<thead>
<tr>
    <th><input type="checkbox" id="checkAll"></th>
    <th>ID</th>
    <th>Nome</th>
    <th>Site</th>
    <th>Logo</th>
    <th>Ações</th>
</tr>
</thead>
<tbody>

<?php foreach ($lojas as $l): ?>
<tr>
    <td><input type="checkbox" name="selecionadas[]" value="<?= $l['idLoja'] ?>"></td>
    <td><?= $l['idLoja'] ?></td>
    <td><?= htmlspecialchars($l['nomeLoja']) ?></td>
    <td><?= $l['siteLoja'] ? '<a href="'.$l['siteLoja'].'" target="_blank">🔗</a>' : '—' ?></td>
    <td><?= $l['logoLoja'] ? '<img src="'.$l['logoLoja'].'" class="logo-thumb">' : '—' ?></td>
    <td>
        <button type="button" onclick='abrirModalEditar(<?= json_encode($l) ?>)'>✏️</button>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</form>

</section>
</main>

<!-- MODAL -->
<div id="modalLoja" class="modal">
<div class="modal-content">
<h2 id="tituloModal"></h2>

<form method="POST">
<input type="hidden" name="acao" id="acaoModal">
<input type="hidden" name="idLoja" id="idLoja">

<label>Nome da Loja</label>
<input type="text" name="nomeLoja" id="nomeLoja" required>

<label>Site</label>
<input type="url" name="siteLoja" id="siteLoja" required>

<label>Logo (URL)</label>
<input type="text" name="logoLoja" id="logoLoja">

<div class="modal-actions">
    <button type="submit">💾 Salvar</button>
    <button type="button" onclick="fecharModal()">❌ Cancelar</button>
</div>
</form>
</div>
</div>

<script>
// MODAL
const modal = document.getElementById('modalLoja');

function abrirModalNova() {
    tituloModal.innerText = 'Nova Loja';
    acaoModal.value = 'salvar';
    document.querySelector('#modalLoja form').reset();
    modal.style.display = 'flex';
}

function abrirModalEditar(l) {
    tituloModal.innerText = 'Editar Loja';
    acaoModal.value = 'editar';
    idLoja.value = l.idLoja;
    nomeLoja.value = l.nomeLoja;
    siteLoja.value = l.siteLoja;
    logoLoja.value = l.logoLoja;
    modal.style.display = 'flex';
}

function fecharModal() {
    modal.style.display = 'none';
}

// CHECK ALL
document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="selecionadas[]"]').forEach(c => {
        c.checked = this.checked;
    });
});

// DELETE SEM CONFIRMAÇÃO
document.querySelector('.btn-delete').addEventListener('click', function () {
    const form = document.querySelector('form[method="POST"]');
    const selecionados = form.querySelectorAll('input[name="selecionadas[]"]:checked');

    if (selecionados.length === 0) {
        return;
    }

    form.submit();
});
</script>

</body>
</html>

