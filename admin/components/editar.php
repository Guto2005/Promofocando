<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

if (!isset($_GET['nome'])) {
    die("Componente não informado.");
}

$nome = basename($_GET['nome']);
$arquivo = "../../data/components/{$nome}.json";

if (!file_exists($arquivo)) {
    die("Arquivo não encontrado.");
}

$dados = json_decode(file_get_contents($arquivo), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['config']['imagem'] = trim($_POST['imagem'] ?? '');
    $dados['config']['link']   = trim($_POST['link'] ?? '');
    $dados['config']['texto']  = trim($_POST['texto'] ?? '');
    $dados['config']['cor']    = trim($_POST['cor'] ?? '#000000');

    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar <?= htmlspecialchars($nome) ?></title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .form-box {
            max-width: 700px;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type=text], input[type=url], input[type=color], textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .preview {
            margin-top: 10px;
        }

        .preview img {
            max-width: 100%;
            border-radius: 10px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .actions button {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        .save { background: #2ecc71; color: white; }
        .back { background: #bdc3c7; color: #333; text-decoration: none; padding: 10px 20px; border-radius: 8px; }
    </style>
</head>
<body>

<header>
    <h1>Editar componente: <?= htmlspecialchars($nome) ?></h1>
</header>

<main>
    <form method="post" class="form-box">

        <div>
            <label>Imagem (URL direta)</label>
            <input type="url" name="imagem" value="<?= htmlspecialchars($dados['config']['imagem'] ?? '') ?>">
        </div>

        <div>
            <label>Link ao clicar</label>
            <input type="url" name="link" value="<?= htmlspecialchars($dados['config']['link'] ?? '') ?>">
        </div>

        <div>
            <label>Texto</label>
            <textarea name="texto"><?= htmlspecialchars($dados['config']['texto'] ?? '') ?></textarea>
        </div>

        <div>
            <label>Cor do texto</label>
            <input type="color" name="cor" value="<?= htmlspecialchars($dados['config']['cor'] ?? '#000000') ?>">
        </div>

        <div class="preview">
            <label>Preview:</label><br>
            <?php if (!empty($dados['config']['imagem'])): ?>
                <img src="<?= htmlspecialchars($dados['config']['imagem']) ?>">
            <?php else: ?>
                <em>Nenhuma imagem definida.</em>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="index.php" class="back">← Voltar</a>
            <button class="save" type="submit">💾 Salvar alterações</button>
        </div>

    </form>
</main>

</body>
</html>
