<?php
session_start();
require_once "../../assets/includes/conexao.php";

/* ================================
   PROTEÇÃO
================================ */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ================================
   AÇÕES
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    /* SALVAR */
    if ($_POST['acao'] === 'salvar') {

        $categoriaTxt = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=" . $_POST['idCategoria'])->fetchColumn();
        $subTxt = !empty($_POST['idSubcategoria'])
            ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=" . $_POST['idSubcategoria'])->fetchColumn()
            : null;
        $lojaTxt = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=" . $_POST['idLoja'])->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO produtos
            (idCategoria, idSubcategoria, idLoja, nomeProduto, descricaoProduto,
             precoProduto, imagemProduto, categoriaProduto, subcategoriaProduto,
             lojaProduto, linkProduto, dataCadastro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

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
    if ($_POST['acao'] === 'editar') {

        $categoriaTxt = $pdo->query("SELECT nomeCategoria FROM categorias WHERE idCategoria=" . $_POST['idCategoria'])->fetchColumn();
        $subTxt = !empty($_POST['idSubcategoria'])
            ? $pdo->query("SELECT nomeSubcategoria FROM subcategorias WHERE idSubcategoria=" . $_POST['idSubcategoria'])->fetchColumn()
            : null;
        $lojaTxt = $pdo->query("SELECT nomeLoja FROM lojas WHERE idLoja=" . $_POST['idLoja'])->fetchColumn();

        $stmt = $pdo->prepare("
            UPDATE produtos SET
                idCategoria=?, idSubcategoria=?, idLoja=?, nomeProduto=?,
                descricaoProduto=?, precoProduto=?, imagemProduto=?,
                categoriaProduto=?, subcategoriaProduto=?, lojaProduto=?,
                linkProduto=?
            WHERE idProduto=?
        ");

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

    /* DELETAR */
    if ($_POST['acao'] === 'deletarMultiplos' && !empty($_POST['selecionados'])) {
        $ids = implode(',', array_map('intval', $_POST['selecionados']));
        $pdo->query("DELETE FROM produtos WHERE idProduto IN ($ids)");
        header("Location: index.php");
        exit;
    }
}

/* ================================
   FILTROS
================================ */
$busca        = $_GET['busca'] ?? null;
$categoria    = $_GET['categoria'] ?? null;
$subcategoria = $_GET['subcategoria'] ?? null;
$loja         = $_GET['loja'] ?? null;

/* ================================
   BUSCA PRODUTOS
================================ */
$sql = "
SELECT p.*, s.nomeSubcategoria
FROM produtos p
LEFT JOIN subcategorias s ON s.idSubcategoria = p.idSubcategoria
WHERE 1=1
";
$params = [];

if ($busca) {
    $sql .= " AND (p.nomeProduto LIKE ? OR s.nomeSubcategoria LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
if ($categoria) {
    $sql .= " AND p.idCategoria = ?";
    $params[] = $categoria;
}
if ($subcategoria) {
    $sql .= " AND p.idSubcategoria = ?";
    $params[] = $subcategoria;
}
if ($loja) {
    $sql .= " AND p.idLoja = ?";
    $params[] = $loja;
}

$sql .= " ORDER BY p.idProduto DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================================
   BASE
================================ */
$categorias    = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria")->fetchAll(PDO::FETCH_ASSOC);
$lojas         = $pdo->query("SELECT * FROM lojas ORDER BY nomeLoja")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos</title>
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

        .produto-img {
            max-width: 100px;
            /* largura máxima da imagem */
            max-height: 100px;
            /* altura máxima da imagem */
            object-fit: contain;
            /* mantém proporção da imagem */
            display: block;
            margin: 0 auto;
            /* centraliza dentro da célula */
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
        }

        aside.sidebar a:hover {
            background: #eaeaea;
        }

        aside.sidebar input,
        aside.sidebar select {
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

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            width: 500px;
            border-radius: 8px;
        }

        .modal-content label {
            display: block;
            margin-top: 10px;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
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
        <h1>Gerenciar Produtos</h1>
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

            <h3>Buscar</h3>
            <form method="GET">
                <input type="text" name="busca" placeholder="Produto ou subcategoria"
                    value="<?= htmlspecialchars($busca ?? '') ?>">

                <select name="categoria">
                    <option value="">Todas categorias</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['idCategoria'] ?>" <?= ($categoria == $c['idCategoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nomeCategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="subcategoria">
                    <option value="">Todas subcategorias</option>
                    <?php foreach ($subcategorias as $s): ?>
                        <option value="<?= $s['idSubcategoria'] ?>" <?= ($subcategoria == $s['idSubcategoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nomeSubcategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="loja">
                    <option value="">Todas lojas</option>
                    <?php foreach ($lojas as $l): ?>
                        <option value="<?= $l['idLoja'] ?>" <?= ($loja == $l['idLoja']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['nomeLoja']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">🔍 Buscar</button>
            </form>

        </aside>

        <!-- CONTEÚDO -->
        <section class="conteudo">

            <h2>Lista de Produtos</h2>



            <form method="POST">
                <div class="botoes">
                    <button type="button" class="btn-add" onclick="abrirModalNovo()">➕ Novo Produto</button>
                    <button class="btn-delete">🗑️ Deletar Selecionados</button>
                </div>
                <input type="hidden" name="acao" value="deletarMultiplos">

                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Subcategoria</th>
                            <th>Preço</th>
                            <th>Imagem</th>
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
                                <td><?= htmlspecialchars($p['categoriaProduto']) ?></td>
                                <td><?= htmlspecialchars($p['subcategoriaProduto'] ?? '—') ?></td>
                                <td><?= $p['precoProduto'] ? 'R$ ' . number_format($p['precoProduto'], 2, ',', '.') : '—' ?></td>
                                <td class="img-col">
                                    <?php if (!empty($p['imagemProduto'])): ?>
                                        <img src="<?= htmlspecialchars($p['imagemProduto']) ?>" class="produto-img" alt="Imagem do produto">
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['linkProduto'])): ?>
                                        <a href="<?= htmlspecialchars($p['linkProduto']) ?>" target="_blank">🔗</a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>


                                <td>
                                    <button type="button" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>✏️</button>
                                </td>
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
            <h3 id="tituloModal"></h3>

            <form method="POST">
                <input type="hidden" name="acao" id="acaoModal">
                <input type="hidden" name="idProduto" id="idProduto">

                <label>Nome</label>
                <input type="text" name="nomeProduto" id="nomeProduto" required>

                <label>Descrição</label>
                <textarea name="descricaoProduto" id="descricaoProduto"></textarea>

                <label>Categoria</label>
                <select name="idCategoria" id="idCategoria" required>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['idCategoria'] ?>"><?= htmlspecialchars($c['nomeCategoria']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Subcategoria</label>
                <select name="idSubcategoria" id="idSubcategoria">
                    <option value="">— Nenhuma —</option>
                    <?php foreach ($subcategorias as $s): ?>
                        <option value="<?= $s['idSubcategoria'] ?>"><?= htmlspecialchars($s['nomeSubcategoria']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Loja</label>
                <select name="idLoja" id="idLoja" required>
                    <?php foreach ($lojas as $l): ?>
                        <option value="<?= $l['idLoja'] ?>"><?= htmlspecialchars($l['nomeLoja']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Preço</label>
                <input type="number" step="0.01" name="precoProduto" id="precoProduto">

                <label>Imagem (URL)</label>
                <input type="text" name="imagemProduto" id="imagemProduto">

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
        const modal = document.getElementById('modalProduto');

        function abrirModalNovo() {
            document.getElementById('tituloModal').innerText = 'Novo Produto';
            document.getElementById('acaoModal').value = 'salvar';
            document.querySelector('#modalProduto form').reset();
            modal.style.display = 'flex';
        }

        function abrirModalEditar(p) {
            document.getElementById('tituloModal').innerText = 'Editar Produto';
            document.getElementById('acaoModal').value = 'editar';

            idProduto.value = p.idProduto;
            nomeProduto.value = p.nomeProduto;
            descricaoProduto.value = p.descricaoProduto;
            idCategoria.value = p.idCategoria;
            idSubcategoria.value = p.idSubcategoria;
            idLoja.value = p.idLoja;
            precoProduto.value = p.precoProduto;
            imagemProduto.value = p.imagemProduto;
            linkProduto.value = p.linkProduto;

            modal.style.display = 'flex';
        }

        function fecharModal() {
            modal.style.display = 'none';
        }

        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('input[name="selecionados[]"]').forEach(c => c.checked = this.checked);
        });

        document.querySelector('.btn-delete').addEventListener('click', function() {
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