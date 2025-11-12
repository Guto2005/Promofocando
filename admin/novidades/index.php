<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

// =====================
// 🗑️ Deletar novidade
// =====================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM novidades WHERE idNovidade = ?");
    $stmt->execute([$id]);
    header("Location: index.php?msg=deletado");
    exit;
}

// =====================
// ✏️ Atualizar novidade
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id']) && $_POST['editar_id'] !== '') {
    $id = $_POST['editar_id'];
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];
    $link = $_POST['link'];
    $dataPublicacao = date('Y-m-d');

    // Lógica da imagem
    if (isset($_POST['imagem_url']) && trim($_POST['imagem_url']) !== '') {
        $imagem = trim($_POST['imagem_url']);
    } elseif (!empty($_FILES['imagem']['name'])) {
        $nomeArquivo = uniqid() . "_" . basename($_FILES['imagem']['name']);
        $caminho = "../../uploads/" . $nomeArquivo;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho);
        $imagem = $nomeArquivo;
    } else {
        $imagem = $_POST['imagem_atual']; // mantém a imagem antiga
    }

    $stmt = $pdo->prepare("UPDATE novidades SET titulo=?, conteudo=?, imagemNovidade=?, linkNovidade=?, dataPublicacao=? WHERE idNovidade=?");
    $stmt->execute([$titulo, $conteudo, $imagem, $link, $dataPublicacao, $id]);

    header("Location: index.php?msg=atualizado");
    exit;
}

// =====================
// ➕ Inserir nova novidade
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo']) && empty($_POST['editar_id'])) {
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];
    $link = $_POST['link'];
    $dataPublicacao = date('Y-m-d');

    // Upload ou link
    if (isset($_POST['imagem_url']) && trim($_POST['imagem_url']) !== '') {
        $imagem = trim($_POST['imagem_url']);
    } elseif (!empty($_FILES['imagem']['name'])) {
        $nomeArquivo = uniqid() . "_" . basename($_FILES['imagem']['name']);
        $caminho = "../../uploads/" . $nomeArquivo;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho);
        $imagem = $nomeArquivo;
    } else {
        $imagem = null;
    }

    $stmt = $pdo->prepare("INSERT INTO novidades (titulo, conteudo, imagemNovidade, linkNovidade, dataPublicacao) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $conteudo, $imagem, $link, $dataPublicacao]);

    header("Location: index.php?msg=criado");
    exit;
}

// =====================
// 📋 Listar novidades
// =====================
$novidades = $pdo->query("SELECT * FROM novidades ORDER BY idNovidade DESC")->fetchAll(PDO::FETCH_ASSOC);

// Mensagem de feedback
$msg = '';
$alertClass = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'criado':
            $msg = "✅ Novidade adicionada com sucesso!";
            $alertClass = "success";
            break;
        case 'atualizado':
            $msg = "✏️ Novidade atualizada com sucesso!";
            $alertClass = "updated";
            break;
        case 'deletado':
            $msg = "🗑️ Novidade excluída com sucesso!";
            $alertClass = "deleted";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Novidades - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<header>
    <h1>Gerenciar Novidades</h1>
    <nav>
        <a href="../dashboard/">🏠 Dashboard</a>
        <a href="../promocoes/">💰 Promoções</a>
        <a href="../novidades/">📰 Novidades</a>
        <a href="../logout.php">🚪 Sair</a>
    </nav>
</header>

<main>
    <h2>Lista de Novidades</h2>

    <?php if ($msg): ?>
        <div class="alert <?= htmlspecialchars($alertClass) ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <button class="btn-add" onclick="abrirModal()">+ Adicionar Novidade</button>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Imagem</th>
                <th>Link</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($novidades as $nov): ?>
                <tr>
                    <td><?= $nov['idNovidade'] ?></td>
                    <td><?= htmlspecialchars($nov['titulo']) ?></td>
                    <td>
                        <?php if (preg_match('/^https?:/', $nov['imagemNovidade'])): ?>
                            <img src="<?= htmlspecialchars($nov['imagemNovidade']) ?>" width="60">
                        <?php elseif (!empty($nov['imagemNovidade'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($nov['imagemNovidade']) ?>" width="60">
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= htmlspecialchars($nov['linkNovidade']) ?>" target="_blank">Abrir</a></td>
                    <td><?= date('d/m/Y', strtotime($nov['dataPublicacao'])) ?></td>
                    <td>
                        <button onclick='abrirEdicao(<?= json_encode($nov) ?>)'>✏️ Editar</button>
                        <a href="?delete=<?= $nov['idNovidade'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta novidade?')">🗑️ Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<!-- Modal Adicionar / Editar -->
<div class="modal" id="modalAdd">
    <div class="modal-content">
        <h3 id="modalTitulo">Adicionar Nova Novidade</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="editar_id" id="editar_id">
            <input type="hidden" name="imagem_atual" id="imagem_atual">

            <label>Título:</label>
            <input type="text" name="titulo" id="titulo" required>

            <label>Conteúdo:</label>
            <textarea name="conteudo" id="conteudo" required></textarea>

            <label>Link:</label>
            <input type="url" name="link" id="link" placeholder="https://exemplo.com" required>

            <label>Imagem (arquivo):</label>
            <input type="file" name="imagem" accept="image/*">

            <label>OU Link da imagem:</label>
            <input type="url" name="imagem_url" id="imagem_url" placeholder="https://exemplo.com/imagem.webp">

            <div class="modal-buttons">
                <button type="submit">Salvar</button>
                <button type="button" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById("modalAdd").style.display = "flex";
    document.getElementById("modalTitulo").innerText = "Adicionar Nova Novidade";
    document.querySelector("form").reset();
    document.getElementById("editar_id").value = "";
}

function fecharModal() {
    document.getElementById("modalAdd").style.display = "none";
}

function abrirEdicao(nov) {
    abrirModal();
    document.getElementById("modalTitulo").innerText = "Editar Novidade";
    document.getElementById("editar_id").value = nov.idNovidade;
    document.getElementById("titulo").value = nov.titulo;
    document.getElementById("conteudo").value = nov.conteudo;
    document.getElementById("link").value = nov.linkNovidade;
    document.getElementById("imagem_url").value = "";
    document.getElementById("imagem_atual").value = nov.imagemNovidade;
}
// ======== Exibir alerta e ocultar automaticamente ======== //
document.addEventListener("DOMContentLoaded", () => {
    const alertBox = document.querySelector(".alert");

    if (alertBox) {
        // Faz o alerta desaparecer depois de 3 segundos
        setTimeout(() => {
            alertBox.style.opacity = "0";
            alertBox.style.transform = "translateY(-10px)";
            setTimeout(() => alertBox.remove(), 300); // remove do DOM depois da animação
        }, 3000);

        // Remove o parâmetro ?msg= da URL para não reaparecer após atualização
        const url = new URL(window.location);
        if (url.searchParams.has("msg")) {
            url.searchParams.delete("msg");
            window.history.replaceState({}, document.title, url);
        }
    }
});

</script>

</body>
</html>
