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
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   PROCESSAR FORMULÁRIO
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    $acao = $_POST['acao'];

    /* ===== SALVAR ===== */
    if ($acao === "salvar") {
        $nome = $_POST['nomeSubcategoria'];
        $idCategoria = $_POST['idCategoria'];

        $stmt = $pdo->prepare("INSERT INTO subcategorias (nomeSubcategoria, idCategoria) VALUES (?, ?)");
        $stmt->execute([$nome, $idCategoria]);

        header("Location: index.php");
        exit;
    }

    /* ===== EDITAR ===== */
    if ($acao === "editar") {
        $id = $_POST['idSubcategoria'];
        $nome = $_POST['nomeSubcategoria'];
        $idCategoria = $_POST['idCategoria'];

        $stmt = $pdo->prepare("UPDATE subcategorias SET nomeSubcategoria=?, idCategoria=? WHERE idSubcategoria=?");
        $stmt->execute([$nome, $idCategoria, $id]);

        header("Location: index.php");
        exit;
    }

    /* ===== DELETAR MULTIPLAS ===== */
    if ($acao === "deletarMultiplas" && isset($_POST['selecionadas'])) {

        $ids = $_POST['selecionadas'];
        $idsLista = implode(',', array_map('intval', $ids));

        $pdo->query("DELETE FROM subcategorias WHERE idSubcategoria IN ($idsLista)");

        header("Location: index.php");
        exit;
    }
}

/* ================================
   PEGAR SUBCATEGORIAS
================================ */
$subcategorias = $pdo->query("
    SELECT subcategorias.*, categorias.nomeCategoria 
    FROM subcategorias
    INNER JOIN categorias ON categorias.idCategoria = subcategorias.idCategoria
    ORDER BY idSubcategoria DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Subcategorias - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
        .checkbox-col {
            text-align: center;
        }
        th.checkbox-col {
            width: 40px;
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

<main>

    <h2>Lista de Subcategorias</h2>

    <button class="btn-add" onclick="abrirModalAdicionar()">➕ Adicionar Subcategoria</button>

    <form method="POST">
        <input type="hidden" name="acao" value="deletarMultiplas">

        <button type="submit" class="btn-delete" style="margin-left:10px;">🗑️ Deletar Selecionadas</button>

        <table>
            <thead>
                <tr>
                    <th class="checkbox-col"><input type="checkbox" id="checkAll"></th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria Pai</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($subcategorias as $sub): ?>
                <tr>
                    <td class="checkbox-col">
                        <input type="checkbox" name="selecionadas[]" value="<?= $sub['idSubcategoria'] ?>">
                    </td>

                    <td><?= $sub['idSubcategoria'] ?></td>
                    <td><?= htmlspecialchars($sub['nomeSubcategoria']) ?></td>
                    <td><?= htmlspecialchars($sub['nomeCategoria']) ?></td>

                    <td>
                        <button type="button" class="btn-edit"
                            onclick="abrirModalEditar(
                                '<?= $sub['idSubcategoria'] ?>',
                                '<?= htmlspecialchars($sub['nomeSubcategoria'], ENT_QUOTES) ?>',
                                '<?= $sub['idCategoria'] ?>'
                        )">✏️ Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </form>

</main>

<!-- MODAL ADICIONAR -->
<div id="modalAdicionar" class="modal">
    <div class="modal-content">
        <h3>Adicionar Subcategoria</h3>

        <form method="POST">
            <input type="hidden" name="acao" value="salvar">

            <label>Nome da Subcategoria:</label>
            <input type="text" name="nomeSubcategoria" required>

            <label>Categoria Pai:</label>
            <select name="idCategoria" required>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModais()">Cancelar</button>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <h3>Editar Subcategoria</h3>

        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="idSubcategoria" id="editId">

            <label>Nome:</label>
            <input type="text" name="nomeSubcategoria" id="editNome" required>

            <label>Categoria Pai:</label>
            <select name="idCategoria" id="editCategoria" required>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Salvar Alterações</button>
            <button type="button" onclick="fecharModais()">Cancelar</button>
        </form>
    </div>
</div>

<script>
document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="selecionadas[]"]').forEach(cb => cb.checked = this.checked);
});

function abrirModalAdicionar() {
    document.getElementById('modalAdicionar').style.display = 'flex';
}

function abrirModalEditar(id, nome, idCategoria) {
    document.getElementById('editId').value = id;
    document.getElementById('editNome').value = nome;
    document.getElementById('editCategoria').value = idCategoria;

    document.getElementById('modalEditar').style.display = 'flex';
}

function fecharModais() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
}
</script>

</body>
</html>
