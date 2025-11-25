<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

/* ==========================================
   CONFIGURAÇÃO DE NOVIDADES
   Produtos com até 10 dias aparecem aqui
========================================== */
$novidadeDias = 10;

/* ==========================================
   AJAX CRUD (EDITAR / EXCLUIR / MÚLTIPLOS)
========================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    header("Content-Type: application/json");
    $acao = $_POST['acao'];

    // EDITAR
    if ($acao === "editar") {
        $idProduto = $_POST['idProduto'] ?? null;
        $nomeProduto = $_POST['nomeProduto'] ?? null;
        $linkProduto = $_POST['linkProduto'] ?? null;
        $idCategoria = $_POST['idCategoria'] ?? null;
        $idSubcategoria = $_POST['idSubcategoria'] ?? null;

        if ($idProduto && $nomeProduto && $idCategoria) {
            $stmt = $pdo->prepare("
                UPDATE produtos 
                SET nomeProduto=?, linkProduto=?, idCategoria=?, idSubcategoria=? 
                WHERE idProduto=?
            ");
            $ok = $stmt->execute([$nomeProduto, $linkProduto, $idCategoria, $idSubcategoria, $idProduto]);
            echo json_encode(['sucesso' => $ok]);
            exit;
        }

        echo json_encode(['sucesso' => false]);
        exit;
    }

    // EXCLUIR
    if ($acao === "excluir") {
        $id = $_POST['idProduto'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduto=?");
            echo json_encode(['sucesso' => $stmt->execute([$id])]);
            exit;
        }
    }

    // EXCLUIR MÚLTIPLOS
    if ($acao === "excluir_multiplos") {
        $ids = $_POST['ids'] ?? "";
        if (!empty($ids)) {
            $idsArray = array_map("intval", explode(",", $ids));
            $placeholders = implode(",", array_fill(0, count($idsArray), "?"));
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduto IN ($placeholders)");
            echo json_encode(['sucesso' => $stmt->execute($idsArray)]);
            exit;
        }
    }

    echo json_encode(['sucesso' => false]);
    exit;
}

/* ==================================================
   CARREGA PRODUTOS QUE SÃO NOVIDADES (ATÉ 10 DIAS)
================================================== */
$stmt = $pdo->prepare("
    SELECT p.*, c.nomeCategoria, s.nomeSubcategoria,
           DATEDIFF(NOW(), p.dataCadastro) AS diasPassados
    FROM produtos p
    LEFT JOIN categorias c ON p.idCategoria = c.idCategoria
    LEFT JOIN subcategorias s ON p.idSubcategoria = s.idSubcategoria
    WHERE p.dataCadastro >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ORDER BY p.dataCadastro DESC
");
$stmt->execute([$novidadeDias]);
$novidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==================================================
   DADOS DE CATEGORIAS E SUBCATEGORIAS
================================================== */
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Novidades</title>

<style>
body { margin:0; font-family:Arial; display:flex; }
header { position:fixed; top:0; left:0; width:100%; background:#111; color:#fff; padding:12px; z-index:10; }
header nav a { color:white; margin-right:15px; text-decoration:none; }
#sidebar { width:230px; margin-top:100px; height:calc(100vh - 60px); overflow-y:auto; background:#f2f2f2; padding:10px; border-right:1px solid #ccc; }
main { flex:1; padding:20px; margin-top:80px; }
table { width:100%; border-collapse:collapse; }
table th, table td { border:1px solid #ccc; padding:6px; }
img { max-width:60px; max-height:60px; }
button { cursor:pointer; }
#modalEditar { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
#modalEditar .box { background:white; padding:20px; width:350px; border-radius:5px; }
</style>
</head>
<body>

<header>
    <h2 style="display:inline-block;">📰 Novidades (últimos <?= $novidadeDias ?> dias)</h2>
    <nav style="display:inline-block; margin-left:20px;">
        <a href="../dashboard/">Dashboard</a>
        <a href="../produtos/">Produtos</a>
        <a href="../promocoes/">Promoções</a>
        <a href="../novidades/">Novidades</a>
        <a href="../categorias/">Categorias</a>
        <a href="../subcategorias/">Subcategorias</a>
        <a href="../logout.php">Sair</a>
    </nav>
</header>

<div id="sidebar">
    <h3>Categorias</h3>

    <?php foreach ($categorias as $cat): ?>
        <button style="width:100%;" onclick="toggleSub(<?= $cat['idCategoria'] ?>)">
            <?= $cat['nomeCategoria'] ?>
        </button>
        <div id="sub-<?= $cat['idCategoria'] ?>" style="display:none; margin-left:10px;">
            <?php foreach ($subcategorias as $sub): ?>
                <?php if ($sub['idCategoria'] == $cat['idCategoria']): ?>
                    <button style="width:100%;" onclick="filtrar(<?= $cat['idCategoria'] ?>, <?= $sub['idSubcategoria'] ?>)">
                        <?= $sub['nomeSubcategoria'] ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <button onclick="filtrar(null,null)">Mostrar tudo</button>
</div>

<main>

<h2>Produtos Recentes</h2>

<button onclick="excluirSelecionados()">🗑️ Excluir Selecionados</button>

<table id="tabela">
    <thead>
        <tr>
            <th><input type="checkbox" onclick="marcarTodos(this)"></th>
            <th>ID</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Subcategoria</th>
            <th>Imagem</th>
            <th>Link</th>
            <th>Cadastrado</th>
            <th>Dias</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($novidades as $p): ?>
        <tr data-cat="<?= $p['idCategoria'] ?>" data-sub="<?= $p['idSubcategoria'] ?>" data-id="<?= $p['idProduto'] ?>">
            <td><input type="checkbox" class="checkItem" value="<?= $p['idProduto'] ?>"></td>
            <td><?= $p['idProduto'] ?></td>
            <td><?= htmlspecialchars($p['nomeProduto']) ?></td>
            <td><?= htmlspecialchars($p['nomeCategoria']) ?></td>
            <td><?= htmlspecialchars($p['nomeSubcategoria']) ?></td>
            <td><?php if ($p['imagemProduto']): ?><img src="<?= $p['imagemProduto'] ?>"><?php endif; ?></td>
            <td><a href="<?= $p['linkProduto'] ?>" target="_blank">Abrir</a></td>
            <td><?= date("d/m/Y", strtotime($p['dataCadastro'])) ?></td>
            <td><?= $p['diasPassados'] ?></td>
            <td>
                <button onclick="editar(<?= $p['idProduto'] ?>)">✏️</button>
                <button onclick="excluir(<?= $p['idProduto'] ?>)">🗑️</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</main>

<!-- MODAL -->
<div id="modalEditar">
    <div class="box">
        <h3>Editar Produto</h3>
        <form id="formEditar">
            <input type="hidden" name="idProduto" id="editId">

            Nome:<br>
            <input type="text" name="nomeProduto" id="editNome" style="width:100%;"><br><br>

            Link:<br>
            <input type="text" name="linkProduto" id="editLink" style="width:100%;"><br><br>

            Categoria:<br>
            <select name="idCategoria" id="editCategoria" style="width:100%;">
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                <?php endforeach; ?>
            </select><br><br>

            Subcategoria:<br>
            <select name="idSubcategoria" id="editSub" style="width:100%;">
                <?php foreach ($subcategorias as $s): ?>
                    <option value="<?= $s['idSubcategoria'] ?>"><?= $s['nomeSubcategoria'] ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button>Salvar</button>
            <button type="button" onclick="fechar()">Cancelar</button>
        </form>
    </div>
</div>

<script>
/* Sidebar */
function toggleSub(id){
    let el = document.getElementById("sub-"+id);
    el.style.display = el.style.display === "block" ? "none" : "block";
}

function filtrar(cat, sub){
    document.querySelectorAll("#tabela tbody tr").forEach(row => {
        if(cat === null){
            row.style.display = "";
            return;
        }
        if(row.dataset.cat == cat && (sub === null || row.dataset.sub == sub)){
            row.style.display = "";
        } else row.style.display = "none";
    });
}

/* Seleção */
function marcarTodos(c){
    document.querySelectorAll(".checkItem").forEach(x => x.checked = c.checked);
}

/* EDITAR */
function editar(id){
    let row = document.querySelector(`tr[data-id="${id}"]`);
    document.getElementById("editId").value = id;
    document.getElementById("editNome").value = row.cells[2].innerText;
    document.getElementById("editLink").value = row.cells[6].querySelector("a").href;
    document.getElementById("editCategoria").value = row.dataset.cat;
    document.getElementById("editSub").value = row.dataset.sub;
    document.getElementById("modalEditar").style.display = "flex";
}

function fechar(){
    document.getElementById("modalEditar").style.display = "none";
}

document.getElementById("formEditar").onsubmit = e => {
    e.preventDefault();
    let f = new FormData(e.target);
    f.append("acao","editar");
    fetch("", { method:"POST", body:f })
    .then(r=>r.json()).then(r=>{
        if(r.sucesso) location.reload();
        else alert("Erro ao editar");
    });
};

/* EXCLUIR */
function excluir(id){
    if(!confirm("Excluir?")) return;
    let d = new URLSearchParams({ acao:"excluir", idProduto:id });
    fetch("", { method:"POST", body:d })
    .then(r=>r.json()).then(r=>{
        if(r.sucesso) location.reload();
        else alert("Erro ao excluir");
    });
}

function excluirSelecionados(){
    let ids = [...document.querySelectorAll(".checkItem:checked")].map(x=>x.value);
    if(ids.length === 0) return alert("Nenhum selecionado.");
    if(!confirm("Excluir itens selecionados?")) return;

    let d = new URLSearchParams({ acao:"excluir_multiplos", ids:ids.join(",") });

    fetch("", { method:"POST", body:d })
        .then(r=>r.json()).then(r=>{
            if(r.sucesso) location.reload();
            else alert("Erro ao excluir");
        });
}
</script>

</body>
</html>

