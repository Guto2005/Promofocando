<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ==========================================
   PROCESSAR AÇÕES DO FORMULÁRIO
   ========================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    /* ========= SALVAR NOVA CATEGORIA ========= */
    if ($acao === "salvar") {

        $nome = $_POST['nomeCategoria'];

        $stmt = $pdo->prepare("INSERT INTO categorias (nomeCategoria) VALUES (?)");
        $stmt->execute([$nome]);

        header("Location: index.php");
        exit;
    }

    /* ========= EDITAR CATEGORIA ========= */
    if ($acao === "editar") {

        $id = $_POST['idCategoria'];
        $nome = $_POST['nomeCategoria'];

        $stmt = $pdo->prepare("UPDATE categorias SET nomeCategoria=? WHERE idCategoria=?");
        $stmt->execute([$nome, $id]);

        header("Location: index.php");
        exit;
    }

    /* ========= DELETAR SELECIONADAS ========= */
    if ($acao === "deletarMultiplas" && isset($_POST['selecionadas'])) {

        $ids = $_POST['selecionadas'];
        $idsLista = implode(',', array_map('intval', $ids));

        $pdo->query("DELETE FROM categorias WHERE idCategoria IN ($idsLista)");

        header("Location: index.php");
        exit;
    }
}

/* ==========================================
   PEGAR CATEGORIAS
   ========================================== */
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY idCategoria DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Categorias - Promofocando</title>
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

<main>

    <h2>Lista de Categorias</h2>

    <button class="btn-add" onclick="abrirModalAdicionar()">➕ Adicionar Categoria</button>

    <form method="POST">
        <input type="hidden" name="acao" value="deletarMultiplas">

        <button type="submit" class="btn-delete" style="margin-left:10px;">🗑️ Deletar Selecionadas</button>

        <table>
            <thead>
                <tr>
                    <th class="checkbox-col">
                        <input type="checkbox" id="checkAll">
                    </th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td class="checkbox-col">
                            <input type="checkbox" name="selecionadas[]" value="<?= $cat['idCategoria'] ?>">
                        </td>

                        <td><?= $cat['idCategoria'] ?></td>
                        <td><?= htmlspecialchars($cat['nomeCategoria']) ?></td>

                        <td>
                            <button type="button" class="btn-edit"
                                onclick="abrirModalEditar(
                                    '<?= $cat['idCategoria'] ?>',
                                    '<?= htmlspecialchars($cat['nomeCategoria'], ENT_QUOTES) ?>'
                            )">✏️ Editar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>

</main>

<!-- ============================ -->
<!--  MODAL ADICIONAR            -->
<!-- ============================ -->
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

<!-- ============================ -->
<!--  MODAL EDITAR               -->
<!-- ============================ -->
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
    // Selecionar tudo
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('input[type=checkbox][name="selecionadas[]"]').forEach(cb => cb.checked = this.checked);
    });

    // Abrir modal adicionar
    function abrirModalAdicionar() {
        document.getElementById('modalAdicionar').style.display = 'flex';
    }

    // Abrir modal editar
    function abrirModalEditar(id, nome) {
        document.getElementById('editId').value = id;
        document.getElementById('editNome').value = nome;

        document.getElementById('modalEditar').style.display = 'flex';
    }

    // Fechar modais
    function fecharModais() {
        document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    }
</script>

</body>
</html>
