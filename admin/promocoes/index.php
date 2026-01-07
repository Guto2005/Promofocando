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
$filtroCategoria = $_GET['categoria'] ?? '';
$filtroStatus    = $_GET['status'] ?? '';
$buscaProduto    = $_GET['busca'] ?? '';

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

if ($filtroCategoria !== '') {
    $sql .= " AND c.idCategoria = ?";
    $params[] = $filtroCategoria;
}

if ($filtroStatus !== '') {
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
   CATEGORIAS
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
            <a href="../layout/">🧩 Layouts</a>
            <a href="../components/">🧱 Components</a>
            <a href="../categorias/">📂 Categorias</a>
            <a href="../subcategorias/">📁 Subcategorias</a>
            <a href="../logout.php">🚪 Sair</a>
        </nav>

    </header>

    <main class="layout-admin">

        <aside class="sidebar">
            <form method="GET">
                <h3>Buscar</h3>

                <input type="text" name="busca" placeholder="Produto..." value="<?= htmlspecialchars($buscaProduto) ?>">

                <select name="status">
                    <option value="">Todas promoções</option>
                    <option value="1" <?= $filtroStatus === '1' ? 'selected' : '' ?>>Ativas</option>
                    <option value="0" <?= $filtroStatus === '0' ? 'selected' : '' ?>>Inativas</option>
                </select>

                <select name="categoria">
                    <option value="">Todas categorias</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['idCategoria'] ?>" <?= $filtroCategoria == $c['idCategoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nomeCategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">🔍 Buscar</button>
            </form>
        </aside>

        <section class="conteudo">

            <h2>Lista de Promoções</h2>


            <form method="POST">
                <div class="botoes">
                    <button type="button" class="btn-add" onclick="abrirModalNova()">➕ Nova Promoção</button>
                    <button class="btn-delete">🗑️ Deletar Selecionadas</button>
                </div>
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
                                <td><button type="button" onclick='abrirModalEditar(<?= json_encode($p) ?>)'>✏️</button></td>
                            </tr>
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
                <select name="idProduto" id="idProduto">
                    <?php foreach ($produtos as $pr): ?>
                        <option value="<?= $pr['idProduto'] ?>"><?= htmlspecialchars($pr['nomeProduto']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Preço</label>
                <input type="number" step="0.01" name="precoPromocional" id="precoPromocional">

                <label>Data Início</label>
                <input type="date" name="dataInicio" id="dataInicio">

                <label>Data Fim</label>
                <input type="date" name="dataFim" id="dataFim">

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
            tituloModal.innerText = 'Nova Promoção';
            acaoModal.value = 'salvar';
            modal.style.display = 'flex';
        }

        function abrirModalEditar(p) {
            tituloModal.innerText = 'Editar Promoção';
            acaoModal.value = 'editar';
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

        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('input[name="selecionadas[]"]').forEach(c => c.checked = this.checked);
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