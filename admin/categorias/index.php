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
            INSERT INTO categorias (nomeCategoria)
            VALUES (?)
        ");
        $stmt->execute([$_POST['nomeCategoria']]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "editar") {
        $stmt = $pdo->prepare("
            UPDATE categorias 
            SET nomeCategoria=?
            WHERE idCategoria=?
        ");
        $stmt->execute([
            $_POST['nomeCategoria'],
            $_POST['idCategoria']
        ]);
        header("Location: index.php");
        exit;
    }

    if ($_POST['acao'] === "deletarMultiplas" && !empty($_POST['selecionadas'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionadas']));
        $pdo->query("DELETE FROM categorias WHERE idCategoria IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   BUSCA DADOS
================================ */
$filtroCategoria = $_GET['categoria'] ?? null;

$sql = "SELECT * FROM categorias";
$params = [];

if (!empty($filtroCategoria)) {
    $sql .= " WHERE idCategoria = ?";
    $params[] = $filtroCategoria;
}

$sql .= " ORDER BY nomeCategoria ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    width: 230px;
    background: #f5f5f5;
    padding: 15px;
    border-radius: 6px;
}

aside.sidebar h3 {
    margin-bottom: 10px;
}

aside.sidebar a {
    display: block;
    padding: 8px;
    margin-bottom: 6px;
    background: #fff;
    border-radius: 4px;
    text-decoration: none;
    color: #000;
}

aside.sidebar a:hover {
    background: #eaeaea;
}

section.conteudo {
    flex: 1;
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
        <a href="../categorias/">📂 Categorias</a>
        <a href="../subcategorias/">📁 Subcategorias</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main class="layout-admin">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h3>Categorias</h3>
        <a href="index.php">📂 Mostrar tudo</a>

        <?php foreach ($categorias as $c): ?>
            <a href="index.php?categoria=<?= $c['idCategoria'] ?>">
                <?= htmlspecialchars($c['nomeCategoria']) ?>
            </a>
        <?php endforeach; ?>
    </aside>

    <!-- CONTEÚDO -->
    <section class="conteudo">

        <h2>Lista de Categorias</h2>

        <button class="btn-add" onclick="abrirModalAdicionar()">➕ Adicionar Categoria</button>

        <form method="POST">
            <input type="hidden" name="acao" value="deletarMultiplas">

            <button class="btn-delete">🗑️ Deletar Selecionadas</button>

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
                        <td>
                            <input type="checkbox" name="selecionadas[]" value="<?= $c['idCategoria'] ?>">
                        </td>
                        <td><?= $c['idCategoria'] ?></td>
                        <td><?= htmlspecialchars($c['nomeCategoria']) ?></td>
                        <td>
                            <button type="button" class="btn-edit"
                                onclick="abrirModalEditar(
                                    '<?= $c['idCategoria'] ?>',
                                    '<?= htmlspecialchars($c['nomeCategoria'], ENT_QUOTES) ?>'
                                )">✏️ Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

    </section>
</main>

<!-- MODAL ADICIONAR -->
<div id="modalAdicionar" class="modal">
    <div class="modal-content">
        <h3>Adicionar Categoria</h3>

        <form method="POST">
            <input type="hidden" name="acao" value="salvar">

            <label>Nome:</label>
            <input type="text" name="nomeCategoria" required>

            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModais()">Cancelar</button>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <h3>Editar Categoria</h3>

        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="idCategoria" id="editId">

            <label>Nome:</label>
            <input type="text" name="nomeCategoria" id="editNome" required>

            <button type="submit">Salvar Alterações</button>
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

