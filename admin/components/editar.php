<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

$nome = basename($_GET['nome'] ?? '');
$arquivo = "../../data/components/{$nome}.json";
if (!file_exists($arquivo)) die("Arquivo não encontrado.");

$dados = json_decode(file_get_contents($arquivo), true);
$tipo = $dados['tipo'] ?? null;
$def = include __DIR__."/tipos/{$tipo}.php";
$usaGrid  = $def['usa_grid']  ?? true;
$usaCores = $def['usa_cores'] ?? true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $novoNome = preg_replace('/[^a-z0-9_-]/i', '', $_POST['nome_arquivo']);
    if ($novoNome && $novoNome !== $nome) {
        $novoArquivo = "../../data/components/{$novoNome}.json";
        rename($arquivo, $novoArquivo);
        $arquivo = $novoArquivo;
        $nome = $novoNome;
    }

    $dados['titulo'] = trim($_POST['titulo'] ?? '');
    $dados['layout'] = [
        'linhas'  => (int)($_POST['linhas'] ?? 1),
        'colunas' => (int)($_POST['colunas'] ?? 1),
        'limite'  => (int)($_POST['limite'] ?? 12),
    ];

    foreach ($def['campos'] as $campo) {
        $n = $campo['nome'];
        $dados['config'][$n] = $campo['tipo'] === 'checkbox'
            ? isset($_POST[$n])
            : trim($_POST[$n] ?? '');
    }

    $dados['config']['cor_fundo'] = $_POST['cor_fundo'] ?? '#ffffff';
    $dados['config']['cor_texto'] = $_POST['cor_texto'] ?? '#000000';

    if (!empty($dados['config']['imagem'])) {
        $dados['config']['cor_texto_bg'] = $_POST['cor_texto_bg'] ?? 'rgba(0,0,0,.6)';
        $dados['config']['texto_x'] = (int)($_POST['texto_x'] ?? 10);
        $dados['config']['texto_y'] = (int)($_POST['texto_y'] ?? 10);
        $dados['config']['texto_tamanho'] = (int)($_POST['texto_tamanho'] ?? 14);
    }

    file_put_contents($arquivo, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
main{display:flex;flex-direction:column;gap:20px}
.editor-container{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
    max-width:900px;
    margin:auto;
}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.preview{margin-top:15px;padding:15px;border-radius:8px}
#grid-preview{display:grid;gap:6px;margin-top:10px}
.cell{background:rgba(0,0,0,0.08);border:1px dashed rgba(0,0,0,0.2);min-height:45px;display:flex;align-items:center;justify-content:center;font-size:13px;border-radius:4px}
.cell.empty{opacity:0.2}
.voltar-btn{display:inline-block;padding:8px 14px;background:#f1f1f1;border:1px solid #ccc;border-radius:8px;font-weight:500;color:#333;text-decoration:none;transition:.2s}
.voltar-btn:hover{background:#e0e0e0;border-color:#999}
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
<div class="editor-container">

<h2>Editar componente: <?= htmlspecialchars($nome) ?></h2>

<form method="post">

<div class="grid">
<div><label>Nome do arquivo</label><input name="nome_arquivo" value="<?= htmlspecialchars($nome) ?>"></div>
<div><label>Título exibido</label><input name="titulo" value="<?= htmlspecialchars($dados['titulo'] ?? '') ?>"></div>

<?php if ($usaGrid): ?>
<div><label>Linhas</label><input type="number" name="linhas" value="<?= $dados['layout']['linhas'] ?? 1 ?>" oninput="atualizarGrid()"></div>
<div><label>Colunas</label><input type="number" name="colunas" value="<?= $dados['layout']['colunas'] ?? 1 ?>" oninput="atualizarGrid()"></div>
<div><label>Limite de itens</label><input type="number" name="limite" value="<?= $dados['layout']['limite'] ?? 12 ?>" oninput="atualizarGrid()"></div>
<?php endif; ?>

<?php if ($usaCores): ?>
<div><label>Cor de fundo</label><input type="color" name="cor_fundo" value="<?= $dados['config']['cor_fundo'] ?? '#ffffff' ?>" oninput="atualizarPreview()"></div>
<div><label>Cor do texto</label><input type="color" name="cor_texto" value="<?= $dados['config']['cor_texto'] ?? '#000000' ?>" oninput="atualizarPreview()"></div>
<?php endif; ?>

<?php if (!empty($dados['config']['imagem'])): ?>
<div><label>Cor de fundo do texto</label><input type="color" name="cor_texto_bg" value="<?= $dados['config']['cor_texto_bg'] ?? '#000000' ?>" oninput="atualizarPreview()"></div>
<div><label>Posição X</label><input type="number" name="texto_x" value="<?= $dados['config']['texto_x'] ?? 10 ?>" oninput="atualizarPreview()"></div>
<div><label>Posição Y</label><input type="number" name="texto_y" value="<?= $dados['config']['texto_y'] ?? 10 ?>" oninput="atualizarPreview()"></div>
<div><label>Tamanho do texto</label><input type="number" name="texto_tamanho" value="<?= $dados['config']['texto_tamanho'] ?? 14 ?>" oninput="atualizarPreview()"></div>
<?php endif; ?>
</div>

<hr>

<?php foreach ($def['campos'] as $campo): $v=$dados['config'][$campo['nome']]??''; ?>
<div>
<label><?= $campo['label'] ?></label>
<?php if ($campo['tipo']==='checkbox'): ?>
<input type="checkbox" name="<?= $campo['nome'] ?>" <?= $v?'checked':'' ?>>
<?php else: ?>
<input type="<?= $campo['tipo'] ?>" name="<?= $campo['nome'] ?>" value="<?= htmlspecialchars($v) ?>">
<?php endif; ?>
</div>
<?php endforeach; ?>

<div class="preview" id="preview" style="background:<?= $dados['config']['cor_fundo'] ?? '#fff' ?>;color:<?= $dados['config']['cor_texto'] ?? '#000' ?>">
<strong>Preview</strong>

<?php if (!empty($dados['config']['imagem'])): ?>
<div style="position:relative;display:inline-block;max-width:100%">
<img src="<?= htmlspecialchars($dados['config']['imagem']) ?>" style="max-width:100%;border-radius:8px">
<?php if (!empty($dados['config']['texto'])): ?>
<div id="preview-text-bg" style="position:absolute;top:<?= $dados['config']['texto_y'] ?? 10 ?>px;left:<?= $dados['config']['texto_x'] ?? 10 ?>px;padding:6px 10px;background:<?= $dados['config']['cor_texto_bg'] ?? 'rgba(0,0,0,.6)' ?>;color:white;border-radius:6px;font-size:<?= $dados['config']['texto_tamanho'] ?? 14 ?>px;">
<?= htmlspecialchars($dados['config']['texto']) ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<?php if ($usaGrid): ?><div id="grid-preview"></div><?php endif; ?>
</div>

<div style="display:flex;justify-content:space-between;margin-top:20px">
<a href="index.php" class="voltar-btn">← Voltar</a>
<button>💾 Salvar</button>
</div>

</form>
</div>
</main>

<script>
function atualizarPreview(){
  const bg=document.querySelector('[name=cor_fundo]')?.value;
  const tx=document.querySelector('[name=cor_texto]')?.value;
  const txtBg=document.querySelector('[name=cor_texto_bg]')?.value;
  const x=document.querySelector('[name=texto_x]')?.value;
  const y=document.querySelector('[name=texto_y]')?.value;
  const size=document.querySelector('[name=texto_tamanho]')?.value;

  const p=document.getElementById('preview');
  if(bg) p.style.background=bg;
  if(tx) p.style.color=tx;

  const overlay=document.getElementById('preview-text-bg');
  if(overlay){
    if(txtBg) overlay.style.background=txtBg;
    if(x) overlay.style.left=x+'px';
    if(y) overlay.style.top=y+'px';
    if(size) overlay.style.fontSize=size+'px';
  }
}

function atualizarGrid(){
  const l=parseInt(document.querySelector('[name=linhas]')?.value||1);
  const c=parseInt(document.querySelector('[name=colunas]')?.value||1);
  const limite=parseInt(document.querySelector('[name=limite]')?.value||0);
  const grid=document.getElementById('grid-preview');
  if(!grid) return;

  grid.innerHTML='';
  grid.style.gridTemplateColumns=`repeat(${c},1fr)`;

  const total=l*c;
  for(let i=1;i<=total;i++){
    const d=document.createElement('div');
    d.className='cell'+(i>limite?' empty':'');
    d.textContent=i<=limite?i:'';
    grid.appendChild(d);
  }
}

atualizarGrid();
</script>

</body>
</html>
