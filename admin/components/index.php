<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

$arquivos = glob("../../data/components/*.json");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Components - Promofocando</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
        main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .component-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .component {
            display: grid;
            grid-template-columns: 1fr 140px 160px 80px;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #ddd;
            gap: 10px;
        }

        .component:last-child {
            border-bottom: none;
        }

        .component small {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<header>
    <h1>Painel Administrativo</h1>
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

<main>
    <div class="component-container">
        <h2>Componentes Criados</h2>

        <?php if (empty($arquivos)): ?>
            <p>Nenhum componente criado ainda.</p>
        <?php else: ?>
            <?php foreach ($arquivos as $file): 
                $nome = basename($file, '.json');
                $dados = json_decode(file_get_contents($file), true);

                $tipo = $dados['tipo'] ?? 'desconhecido';
                $data = date('d/m/Y H:i', filemtime($file));

                $img = '';
                if ($tipo === 'banner' && !empty($dados['config']['imagem'])) {
                    $img = $dados['config']['imagem'];
                }
            ?>
                <div class="component">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php if ($img): ?>
                            <img src="<?= htmlspecialchars($img) ?>" 
                                 style="width:90px;height:50px;object-fit:cover;border-radius:6px;">
                        <?php endif; ?>
                        <div>
                            <strong><?= htmlspecialchars($nome) ?></strong><br>
                            <small><?= htmlspecialchars($tipo) ?></small>
                        </div>
                    </div>

                    <small><?= $data ?></small>
                    <a href="editar.php?nome=<?= urlencode($nome) ?>">✏️ Editar</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top:15px;">
            <a href="criar.php">➕ Criar novo componente</a>
        </div>
    </div>
</main>

</body>
</html>

