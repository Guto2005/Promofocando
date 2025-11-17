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

    /* ========= SALVAR NOVA LOJA ========= */
    if ($acao === "salvar") {

        $nome = $_POST['nomeLoja'];
        $site = $_POST['siteLoja'];
        $logo = null;

        // Se enviou URL da imagem
        if (!empty($_POST['logoURL'])) {
            $logo = $_POST['logoURL'];

            // Se enviou arquivo
        } elseif (!empty($_FILES['logoUpload']['name'])) {
            $arquivo = $_FILES['logoUpload'];
            $nomeArq = time() . "_" . basename($arquivo['name']);
            $destino = "../../assets/images/lojas/" . $nomeArq;
            move_uploaded_file($arquivo['tmp_name'], $destino);
            $logo = $nomeArq;
        }

        $stmt = $pdo->prepare("INSERT INTO lojas (nomeLoja, siteLoja, logoLoja) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $site, $logo]);

        header("Location: index.php");
        exit;
    }

    /* ========= EDITAR LOJA ========= */
    if ($acao === "editar") {

        $id = $_POST['idLoja'];
        $nome = $_POST['nomeLoja'];
        $site = $_POST['siteLoja'];

        // Logo antiga
        $logo = $_POST['logoAtual'];

        // URL nova enviada?
        if (!empty($_POST['logoURL'])) {
            $logo = $_POST['logoURL'];

            // Arquivo enviado?
        } elseif (!empty($_FILES['logoUpload']['name'])) {
            $arquivo = $_FILES['logoUpload'];
            $nomeArq = time() . "_" . basename($arquivo['name']);
            $destino = "../../assets/images/lojas/" . $nomeArq;
            move_uploaded_file($arquivo['tmp_name'], $destino);
            $logo = $nomeArq;
        }

        $stmt = $pdo->prepare("UPDATE lojas SET nomeLoja=?, siteLoja=?, logoLoja=? WHERE idLoja=?");
        $stmt->execute([$nome, $site, $logo, $id]);

        header("Location: index.php");
        exit;
    }

    /* ========= DELETAR SELECIONADAS ========= */
    if ($acao === "deletarMultiplas" && isset($_POST['selecionadas'])) {

        $ids = $_POST['selecionadas'];
        $idsLista = implode(',', array_map('intval', $ids));

        $pdo->query("DELETE FROM lojas WHERE idLoja IN ($idsLista)");

        header("Location: index.php");
        exit;
    }
}

/* ==========================================
   PEGAR LOJAS
   ========================================== */
$lojas = $pdo->query("SELECT * FROM lojas ORDER BY idLoja DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Lojas - Promofocando</title>
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
        <h1>Gerenciar Lojas</h1>
        <nav>
            <a href="../dashboard/">🏠 Dashboard</a>
            <a href="../promocoes/">💰 Promoções</a>
            <a href="../novidades/">📰 Novidades</a>
            <a href="../lojas/">🏪 Lojas</a>
            <a href="../logout.php">🚪 Sair</a>
        </nav>
    </header>

    <main>

        <h2>Lista de Lojas</h2>

        <button class="btn-add" onclick="abrirModalAdicionar()">➕ Adicionar Loja</button>

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
                        <th>Site</th>
                        <th>Logo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lojas as $loja): ?>
                        <tr>
                            <td class="checkbox-col">
                                <input type="checkbox" name="selecionadas[]" value="<?= $loja['idLoja'] ?>">
                            </td>

                            <td><?= $loja['idLoja'] ?></td>
                            <td><?= htmlspecialchars($loja['nomeLoja']) ?></td>

                            <td><a href="<?= htmlspecialchars($loja['siteLoja']) ?>" target="_blank">Visitar</a></td>

                            <td>
                                <?php if (!empty($loja['logoLoja'])): ?>
                                    <img src="<?= (str_starts_with($loja['logoLoja'], 'http') ? $loja['logoLoja'] : '../../assets/images/lojas/' . $loja['logoLoja']) ?>" width="50">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td>
                                <button type="button" class="btn-edit"
                                    onclick="abrirModalEditar(
                                '<?= $loja['idLoja'] ?>',
                                '<?= htmlspecialchars($loja['nomeLoja'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($loja['siteLoja'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($loja['logoLoja'], ENT_QUOTES) ?>'
                            )">✏️ Editar
                                </button>
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
            <h3>Adicionar Nova Loja</h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="salvar">

                <label>Nome:</label>
                <input type="text" name="nomeLoja" required>

                <label>Site:</label>
                <input type="url" name="siteLoja" required>

                <label>Logo (URL):</label>
                <input type="url" name="logoURL">

                <label>OU envie um arquivo:</label>
                <input type="file" name="logoUpload" accept="image/*">

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
            <h3>Editar Loja</h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="idLoja" id="editIdLoja">
                <input type="hidden" name="logoAtual" id="editLogoAtual">

                <label>Nome:</label>
                <input type="text" name="nomeLoja" id="editNomeLoja" required>

                <label>Site:</label>
                <input type="url" name="siteLoja" id="editSiteLoja" required>

                <label>Logo Atual:</label>
                <img id="editLogoPreview" width="70" style="display:block; margin-bottom:10px;">

                <label>Nova Logo (URL):</label>
                <input type="url" name="logoURL">

                <label>OU envie um arquivo:</label>
                <input type="file" name="logoUpload" accept="image/*">

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
        function abrirModalEditar(id, nome, site, logo) {
            document.getElementById('editIdLoja').value = id;
            document.getElementById('editNomeLoja').value = nome;
            document.getElementById('editSiteLoja').value = site;
            document.getElementById('editLogoAtual').value = logo;

            if (logo.startsWith("http")) {
                document.getElementById('editLogoPreview').src = logo;
            } else {
                document.getElementById('editLogoPreview').src = "../../assets/images/lojas/" + logo;
            }

            document.getElementById('modalEditar').style.display = 'flex';
        }

        // Fechar modais
        function fecharModais() {
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        }
    </script>

</body>

</html>
