<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

$dir = __DIR__ . "/tipos";
$arquivos = glob($dir . "/*.php");

$componentes = [];
foreach ($arquivos as $file) {
    $tipo = basename($file, '.php');
    $componentes[$tipo] = include $file;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Criar componente</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<style>
.form-container{background:#fff;padding:25px;border-radius:10px;max-width:700px;margin:auto}
label{font-weight:bold;font-size:.9em}
input,textarea,select{width:100%;padding:8px;border-radius:6px;border:1px solid #ccc}
button{margin-top:15px;padding:10px 18px;border:none;border-radius:6px;background:#d4af37;font-weight:bold;cursor:pointer}

.bloco{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:12px;
    margin:15px 0;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
    background:#fafafa;
}

#area-campos button{
    background:#3498db;
    color:#fff;
}
</style>

<script>
const defs = <?= json_encode($componentes) ?>;

function trocarTipo(){
    const tipo = document.getElementById('tipo').value;
    const area = document.getElementById('area-campos');
    area.innerHTML = '';

    if(!defs[tipo]) return;

    const def = defs[tipo];
    let total = def.min || 1;

    function criarBloco(){
        const bloco = document.createElement('div');
        bloco.className = 'bloco';

        def.campos.forEach(c=>{
            const wrap = document.createElement('div');

            const label = document.createElement('label');
            label.innerText = c.label;

            let input;
            if(c.tipo === 'textarea'){
                input = document.createElement('textarea');
                input.rows = 3;
            } else {
                input = document.createElement('input');
                input.type = c.tipo;
            }

            input.name = tipo + '[][' + c.nome + ']';

            wrap.appendChild(label);
            wrap.appendChild(input);
            bloco.appendChild(wrap);
        });

        area.appendChild(bloco);
    }

    for(let i=0;i<total;i++) criarBloco();

    if(def.estrutura === 'dinamico' || def.estrutura === 'blocos'){
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerText = def.estrutura === 'blocos'
            ? '+ ' + def.bloco + ' itens'
            : '+ imagem';

        btn.onclick = ()=>{
            for(let i=0;i<(def.bloco || 1);i++) criarBloco();
        };

        area.appendChild(btn);
    }
}
</script>
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
<div class="form-container">
<h2>Novo componente</h2>

<form method="post" action="salvar.php">

<label>Nome</label>
<input type="text" name="nome" required>

<label>Tipo</label>
<select name="tipo" id="tipo" onchange="trocarTipo()" required>
    <option value="">Selecione</option>
    <?php foreach ($componentes as $tipo => $def): ?>
        <option value="<?= $tipo ?>"><?= $def['label'] ?></option>
    <?php endforeach; ?>
</select>

<div id="area-campos"></div>

<button type="submit">💾 Criar componente</button>

</form>
</div>
</main>

</body>
</html>
