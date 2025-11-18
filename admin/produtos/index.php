<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ==========================
    Carregar categorias, subcategorias e lojas
========================== */
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$lojas = $pdo->query("SELECT * FROM lojas ORDER BY nomeLoja ASC")->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
    Processar ADICIONAR
========================== */
if (isset($_POST['add_produto'])) {
    $idCategoria = $_POST['idCategoria'];
    $idSubcategoria = $_POST['idSubcategoria'] != "" ? $_POST['idSubcategoria'] : null;
    $idLoja = $_POST['idLoja'];
    $nomeProduto = $_POST['nomeProduto'];
    $descricaoProduto = $_POST['descricaoProduto'];
    $precoProduto = $_POST['precoProduto'] !== "" ? $_POST['precoProduto'] : null;
    $imagemProduto = $_POST['imagemProduto'];
    $linkProduto = $_POST['linkProduto'];

    $categoriaTexto = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=$idCategoria")->fetchColumn();

    $subcategoriaTexto = null;
    if ($idSubcategoria != null) {
        $subcategoriaTexto = $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=$idSubcategoria")->fetchColumn();
    }

    $lojaTexto = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=$idLoja")->fetchColumn();

    $stmt = $pdo->prepare("
        INSERT INTO produtos
            (idCategoria, idSubcategoria, idLoja, nomeProduto, descricaoProduto, precoProduto, imagemProduto, categoriaProduto, subcategoriaProduto, lojaProduto, linkProduto, dataCadastro)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([$idCategoria, $idSubcategoria, $idLoja, $nomeProduto, $descricaoProduto, $precoProduto, $imagemProduto, $categoriaTexto, $subcategoriaTexto, $lojaTexto, $linkProduto]);

    header("Location: index.php");
    exit;
}

/* ==========================
    Processar EDITAR
========================== */
if (isset($_POST['edit_produto'])) {
    $idProduto = $_POST['idProduto'];
    $idCategoria = $_POST['idCategoria'];
    $idSubcategoria = $_POST['idSubcategoria'] != "" ? $_POST['idSubcategoria'] : null;
    $idLoja = $_POST['idLoja'];
    $nomeProduto = $_POST['nomeProduto'];
    $descricaoProduto = $_POST['descricaoProduto'];
    $precoProduto = $_POST['precoProduto'] !== "" ? $_POST['precoProduto'] : null;
    $imagemProduto = $_POST['imagemProduto'];
    $linkProduto = $_POST['linkProduto'];

    $categoriaTexto = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=$idCategoria")->fetchColumn();

    $subcategoriaTexto = null;
    if ($idSubcategoria != null) {
        $subcategoriaTexto = $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=$idSubcategoria")->fetchColumn();
    }

    $lojaTexto = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=$idLoja")->fetchColumn();

    $stmt = $pdo->prepare("
        UPDATE produtos SET
            idCategoria=?, idSubcategoria=?, idLoja=?, nomeProduto=?, descricaoProduto=?, precoProduto=?, imagemProduto=?, categoriaProduto=?, subcategoriaProduto=?, lojaProduto=?, linkProduto=?
        WHERE idProduto=?
    ");

    $stmt->execute([$idCategoria, $idSubcategoria, $idLoja, $nomeProduto, $descricaoProduto, $precoProduto, $imagemProduto, $categoriaTexto, $subcategoriaTexto, $lojaTexto, $linkProduto, $idProduto]);

    header("Location: index.php");
    exit;
}

/* ==========================
    Deletar múltiplos
========================== */
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selecionados'])) {
        $ids = implode(",", array_map("intval", $_POST['selecionados']));
        $pdo->query("DELETE FROM produtos WHERE idProduto IN ($ids)");
    }
    header("Location: index.php");
    exit;
}

/* ==========================
    Listar produtos
========================== */
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY idProduto DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
    /* ==========================
       Limitar tamanho das imagens
    ========================== */
    .thumb {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 5px;
    }

    /* Modal centralizado */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        max-width: 600px;
        width: 90%;
        border-radius: 10px;
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

<main>
    <h2>Lista de Produtos</h2>

    <form method="POST">

        <button type="button" onclick="openAdd()">➕ Adicionar Produto</button>
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
                    <td><?= $p['precoProduto'] === null ? "—" : "R$ " . number_format($p['precoProduto'], 2, ",", ".") ?></td>
                    <td><?= htmlspecialchars($p['categoriaProduto']) ?></td>
                    <td><?= htmlspecialchars($p['subcategoriaProduto'] ?? "—") ?></td>
                    <td><?= htmlspecialchars($p['lojaProduto']) ?></td>
                    <td><a href="<?= $p['linkProduto'] ?>" target="_blank">Abrir</a></td>
                    <td><button type="button" onclick='openEdit(<?= json_encode($p) ?>)'>✏ Editar</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </form>
</main>

<!-- ==========================
      MODAL ADICIONAR
========================== -->
<div id="modalAdd" class="modal">
    <div class="modal-content">
        <h2>Adicionar Produto</h2>
        <form method="POST">

            <label>Nome:</label>
            <input type="text" name="nomeProduto" required>

            <label>Descrição:</label>
            <textarea name="descricaoProduto"></textarea>

            <label>Preço (opcional):</label>
            <input type="number" step="0.01" name="precoProduto">

            <label>Imagem (URL):</label>
            <input type="text" name="imagemProduto" required>

            <label>Link do Produto:</label>
            <input type="text" name="linkProduto">

            <label>Categoria:</label>
            <select name="idCategoria" required onchange="filterSubcategoriasAdd()">
                <option value="">Selecione</option>
                <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>Subcategoria:</label>
            <select name="idSubcategoria" id="add_subcategoria">
                <option value="">Nenhuma</option>
                <?php foreach ($subcategorias as $s): ?>
                <option value="<?= $s['idSubcategoria'] ?>" data-cat="<?= $s['idCategoria'] ?>">
                    <?= $s['nomeSubcategoria'] ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Loja:</label>
            <select name="idLoja" required>
                <?php foreach ($lojas as $l): ?>
                <option value="<?= $l['idLoja'] ?>"><?= $l['nomeLoja'] ?></option>
                <?php endforeach; ?>
            </select>

            <button name="add_produto">Salvar</button>
            <button type="button" onclick="closeAdd()">Cancelar</button>
        </form>
    </div>
</div>

<!-- ==========================
      MODAL EDITAR
========================== -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <h2>Editar Produto</h2>

        <form method="POST">

            <input type="hidden" name="idProduto" id="edit_idProduto">

            <label>Nome:</label>
            <input type="text" name="nomeProduto" id="edit_nomeProduto" required>

            <label>Descrição:</label>
            <textarea name="descricaoProduto" id="edit_descricaoProduto"></textarea>

            <label>Preço (opcional):</label>
            <input type="number" step="0.01" name="precoProduto" id="edit_precoProduto">

            <label>Imagem (URL):</label>
            <input type="text" name="imagemProduto" id="edit_imagemProduto">

            <label>Link do Produto:</label>
            <input type="text" name="linkProduto" id="edit_linkProduto">

            <label>Categoria:</label>
            <select name="idCategoria" id="edit_idCategoria" onchange="filterSubcategoriasEdit()">
                <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>Subcategoria:</label>
            <select name="idSubcategoria" id="edit_idSubcategoria">
                <option value="">Nenhuma</option>
                <?php foreach ($subcategorias as $s): ?>
                <option value="<?= $s['idSubcategoria'] ?>" data-cat="<?= $s['idCategoria'] ?>">
                    <?= $s['nomeSubcategoria'] ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Loja:</label>
            <select name="idLoja" id="edit_idLoja">
                <?php foreach ($lojas as $l): ?>
                <option value="<?= $l['idLoja'] ?>"><?= $l['nomeLoja'] ?></option>
                <?php endforeach; ?>
            </select>

            <button name="edit_produto">Salvar Alterações</button>
            <button type="button" onclick="closeEdit()">Cancelar</button>
        </form>
    </div>
</div>

<script>
function toggleAll(src) {
    document.querySelectorAll("tbody input[type=checkbox]").forEach(c => c.checked = src.checked);
}

function openAdd() {
    document.getElementById("modalAdd").style.display = "flex";
}

function closeAdd() {
    document.getElementById("modalAdd").style.display = "none";
}

function openEdit(data) {
    document.getElementById("modalEdit").style.display = "flex";

    document.getElementById("edit_idProduto").value = data.idProduto;
    document.getElementById("edit_nomeProduto").value = data.nomeProduto;
    document.getElementById("edit_descricaoProduto").value = data.descricaoProduto;
    document.getElementById("edit_precoProduto").value = data.precoProduto;
    document.getElementById("edit_imagemProduto").value = data.imagemProduto;
    document.getElementById("edit_linkProduto").value = data.linkProduto;

    document.getElementById("edit_idCategoria").value = data.idCategoria;
    filterSubcategoriasEdit();
    document.getElementById("edit_idSubcategoria").value = data.idSubcategoria;

    document.getElementById("edit_idLoja").value = data.idLoja;
}

function closeEdit() {
    document.getElementById("modalEdit").style.display = "none";
}

/* FILTRAR SUBCATEGORIAS NO ADD */
function filterSubcategoriasAdd() {
    let cat = document.querySelector("select[name='idCategoria']").value;
    let subs = document.querySelectorAll("#add_subcategoria option");

    subs.forEach(op => {
        if (op.value === "") {
            op.style.display = "block";
            return;
        }

        if (op.dataset.cat === cat) {
            op.style.display = "block";
        } else {
            op.style.display = "none";
        }
    });
}

/* FILTRAR SUBCATEGORIAS NO EDIT */
function filterSubcategoriasEdit() {
    let cat = document.getElementById("edit_idCategoria").value;
    let subs = document.querySelectorAll("#edit_idSubcategoria option");

    subs.forEach(op => {
        if (op.value === "") {
            op.style.display = "block";
            return;
        }

        if (op.dataset.cat === cat) {
            op.style.display = "block";
        } else {
            op.style.display = "none";
        }
    });
}
</script>

</body>
</html>
