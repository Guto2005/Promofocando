<?php
session_start();
require_once "../../assets/includes/conexao.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../pages/login.php");
    exit;
}

// ======================
// TRATAMENTO AJAX CRUD
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    header('Content-Type: application/json');
    $acao = $_POST['acao'];

    // EDITAR
    if ($acao === 'editar') {
        $idProduto = $_POST['idProduto'] ?? null;
        $nomeProduto = $_POST['nomeProduto'] ?? null;
        $linkProduto = $_POST['linkProduto'] ?? null;
        $idCategoria = $_POST['idCategoria'] ?? null;
        $idSubcategoria = $_POST['idSubcategoria'] ?? null;

        if ($idProduto && $nomeProduto && $idCategoria) {
            $stmt = $pdo->prepare("UPDATE produtos SET nomeProduto=?, linkProduto=?, idCategoria=?, idSubcategoria=? WHERE idProduto=?");
            $res = $stmt->execute([$nomeProduto, $linkProduto, $idCategoria, $idSubcategoria, $idProduto]);
            echo json_encode(['sucesso' => $res]);
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Dados incompletos']);
            exit;
        }
    }

    // EXCLUIR 1
    if ($acao === 'excluir') {
        $idProduto = $_POST['idProduto'] ?? null;
        if ($idProduto) {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduto=?");
            $res = $stmt->execute([$idProduto]);
            echo json_encode(['sucesso' => $res]);
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'ID inválido']);
            exit;
        }
    }

    // EXCLUIR MÚLTIPLOS
    if ($acao === 'excluir_multiplos') {
        $ids = $_POST['ids'] ?? null;
        if ($ids) {
            $idsArray = array_map('intval', explode(',', $ids));
            $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduto IN ($placeholders)");
            $res = $stmt->execute($idsArray);
            echo json_encode(['sucesso' => $res]);
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'msg' => 'Nenhum ID enviado']);
            exit;
        }
    }
}

// ======================
// CARREGAR DADOS PARA A TABELA
// ======================

// Configuração: dias que um produto fica como novidade
$novidadeDias = 10;

// Buscar produtos recentes
$novidades = $pdo->prepare("
    SELECT p.*, c.nomeCategoria, s.nomeSubcategoria
    FROM produtos p
    LEFT JOIN categorias c ON p.idCategoria=c.idCategoria
    LEFT JOIN subcategorias s ON p.idSubcategoria=s.idSubcategoria
    WHERE p.dataCadastro >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ORDER BY p.dataCadastro DESC
");
$novidades->execute([$novidadeDias]);
$novidades = $novidades->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias e subcategorias
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nomeCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nomeSubcategoria ASC")->fetchAll(PDO::FETCH_ASSOC);

// Mensagem de feedback
$msg = '';
$alertClass = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'sucesso': $msg = "✅ Operação realizada com sucesso!"; $alertClass = "success"; break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Novidades - Promofocando</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<style>
body { display:flex; margin:0; font-family:sans-serif; }
header { width:100%; padding:10px; background:#222; color:#fff; position:fixed; top:0; left:0; z-index:10; }
header h1 { margin:0; font-size:18px; display:inline-block; }
header nav { display:inline-block; margin-left:20px; }
header nav a { color:#fff; margin-right:10px; text-decoration:none; }
#sidebar { width:220px; background:#f0f0f0; padding:10px; border-right:1px solid #ccc; margin-top:60px; height:calc(100vh - 60px); overflow-y:auto; }
#sidebar h2 { margin-top:10px; }
#sidebar button { width:100%; text-align:left; margin:3px 0; padding:5px; cursor:pointer; background:#fff; border:1px solid #ccc; }
#sidebar .sub { margin-left:10px; display:none; }
main { flex:1; padding:20px; margin-top:60px; overflow-x:auto; }
#filtros { margin-bottom:10px; display:flex; gap:10px; flex-wrap:wrap; background:#f9f9f9; padding:10px; border:1px solid #ccc; }
#filtros input { padding:5px; width:150px; }
#filtros button { padding:5px 10px; }
table { width:100%; border-collapse:collapse; }
table th, table td { border:1px solid #ccc; padding:5px; text-align:left; }
.alert { padding:10px; margin-bottom:10px; }
.success { background:#d4edda; color:#155724; }
img { max-width:60px; max-height:60px; }
button { cursor:pointer; }
</style>
</head>
<body>

<header>
    <h1>Gerenciar Novidades</h1>
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

<div style="display:flex; width:100%; margin-top:60px;">
    <div id="sidebar">
        <h2>Categorias</h2>
        <?php foreach($categorias as $cat): ?>
            <button class="cat-btn" onclick="toggleSubcategoria(<?= $cat['idCategoria'] ?>)">
                <?= htmlspecialchars($cat['nomeCategoria']) ?>
            </button>
            <div class="sub" id="sub-<?= $cat['idCategoria'] ?>">
            <?php foreach($subcategorias as $sub): ?>
                <?php if($sub['idCategoria'] == $cat['idCategoria']): ?>
                    <button class="sub-btn" onclick="filtrarCategoria(<?= $cat['idCategoria'] ?>, <?= $sub['idSubcategoria'] ?>)">
                        <?= htmlspecialchars($sub['nomeSubcategoria']) ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button onclick="filtrarCategoria(null,null)">Mostrar Todos</button>
    </div>

    <main>
        <?php if ($msg): ?>
        <div class="alert <?= htmlspecialchars($alertClass) ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div id="filtros">
            <input type="text" id="filtroID" placeholder="Filtrar por ID">
            <input type="text" id="filtroTitulo" placeholder="Filtrar por Nome">
            <input type="text" id="filtroData" placeholder="Filtrar por Data (dd/mm/aaaa)">
            <button onclick="aplicarFiltros()">Aplicar Filtros</button>
            <button onclick="limparFiltros()">Limpar Filtros</button>
        </div>

        <button id="btnExcluirSelecionados" onclick="excluirSelecionados()">🗑️ Excluir Selecionados</button>

        <table id="novidadesTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll" onclick="marcarTodos(this)"></th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Subcategoria</th>
                    <th>Imagem</th>
                    <th>Link</th>
                    <th>Data Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($novidades as $prod): ?>
                <tr data-idcat="<?= $prod['idCategoria'] ?>" data-idsub="<?= $prod['idSubcategoria'] ?>" data-id="<?= $prod['idProduto'] ?>">
                    <td><input type="checkbox" class="checkItem" value="<?= $prod['idProduto'] ?>"></td>
                    <td><?= $prod['idProduto'] ?></td>
                    <td><?= htmlspecialchars($prod['nomeProduto']) ?></td>
                    <td><?= htmlspecialchars($prod['nomeCategoria']) ?></td>
                    <td><?= htmlspecialchars($prod['nomeSubcategoria']) ?></td>
                    <td>
                        <?php if (!empty($prod['imagemProduto'])): ?>
                           <img src="<?= htmlspecialchars($prod['imagemProduto']) ?>" alt="Imagem Produto">
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= htmlspecialchars($prod['linkProduto'] ?? '#') ?>" target="_blank">Abrir</a></td>
                    <td><?= date('d/m/Y', strtotime($prod['dataCadastro'])) ?></td>
                    <td>
                        <button onclick="editarProduto(<?= $prod['idProduto'] ?>)">✏️ Editar</button>
                        <button onclick="excluirProduto(<?= $prod['idProduto'] ?>)">🗑️ Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<div id="modalEditar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:5px; width:400px;">
        <h2>Editar Produto</h2>
        <form id="formEditar">
            <input type="hidden" name="idProduto" id="editId">
            <label>Nome:</label><br>
            <input type="text" name="nomeProduto" id="editNome" style="width:100%;"><br><br>
            <label>Link:</label><br>
            <input type="text" name="linkProduto" id="editLink" style="width:100%;"><br><br>
            <label>Categoria:</label><br>
            <select name="idCategoria" id="editCategoria" style="width:100%;">
                <?php foreach($categorias as $cat): ?>
                    <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nomeCategoria']) ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <label>Subcategoria:</label><br>
            <select name="idSubcategoria" id="editSubcategoria" style="width:100%;">
                <?php foreach($subcategorias as $sub): ?>
                    <option value="<?= $sub['idSubcategoria'] ?>"><?= htmlspecialchars($sub['nomeSubcategoria']) ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>
</div>

<script>
// ======== FILTROS ========
function aplicarFiltros() {
    const id = document.getElementById("filtroID").value.toLowerCase();
    const titulo = document.getElementById("filtroTitulo").value.toLowerCase();
    const data = document.getElementById("filtroData").value.toLowerCase();
    const rows = document.querySelectorAll("#novidadesTable tbody tr");
    rows.forEach(row => {
        const cID = row.cells[1].textContent.toLowerCase();
        const cTitulo = row.cells[2].textContent.toLowerCase();
        const cData = row.cells[7].textContent.toLowerCase();
        if ((id === "" || cID.includes(id)) &&
            (titulo === "" || cTitulo.includes(titulo)) &&
            (data === "" || cData.includes(data))) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
function limparFiltros() {
    document.getElementById("filtroID").value = "";
    document.getElementById("filtroTitulo").value = "";
    document.getElementById("filtroData").value = "";
    aplicarFiltros();
}

// ======== FILTRO POR CATEGORIA ========
function toggleSubcategoria(catId){
    const el = document.getElementById("sub-"+catId);
    el.style.display = el.style.display === "block" ? "none" : "block";
}
function filtrarCategoria(catId, subId){
    const rows = document.querySelectorAll("#novidadesTable tbody tr");
    rows.forEach(row => {
        if(catId === null){ row.style.display = ""; return; }
        const rowCat = row.dataset.idcat;
        const rowSub = row.dataset.idsub;
        if(rowCat == catId && (!subId || rowSub == subId)){ row.style.display=""; } else { row.style.display="none"; }
    });
}

// ======== Checkboxes ========
function marcarTodos(source){
    document.querySelectorAll('.checkItem').forEach(c => c.checked = source.checked);
}

// ======== CRUD ========
function editarProduto(id){
    const row = document.querySelector(`tr[data-id='${id}']`);
    document.getElementById('editId').value = id;
    document.getElementById('editNome').value = row.cells[2].textContent;
    document.getElementById('editLink').value = row.cells[6].querySelector('a')?.href || '';
    document.getElementById('editCategoria').value = row.dataset.idcat;
    document.getElementById('editSubcategoria').value = row.dataset.idsub;
    document.getElementById('modalEditar').style.display = 'flex';
}
function fecharModal(){ document.getElementById('modalEditar').style.display = 'none'; }

// AJAX unificado
document.getElementById('formEditar').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('acao','editar');
    fetch('', {method:'POST', body:formData})
    .then(res=>res.json()).then(res=>{
        if(res.sucesso){ location.reload(); } else { alert('Erro ao atualizar'); }
    });
});

function excluirProduto(id){
    if(!confirm('Deseja realmente excluir este produto?')) return;
    const data = new URLSearchParams({acao:'excluir', idProduto:id});
    fetch('', {method:'POST', body:data})
    .then(res=>res.json()).then(res=>{
        if(res.sucesso) location.reload(); else alert('Erro ao excluir');
    });
}

function excluirSelecionados(){
    const ids = Array.from(document.querySelectorAll('.checkItem:checked')).map(c=>c.value);
    if(ids.length === 0) return alert('Selecione ao menos um item');
    if(!confirm('Deseja excluir os produtos selecionados?')) return;
    const data = new URLSearchParams({acao:'excluir_multiplos', ids: ids.join(',')});
    fetch('', {method:'POST', body:data})
    .then(res=>res.json()).then(res=>{
        if(res.sucesso) location.reload(); else alert('Erro ao excluir');
    });
}

// ======== Alerta automático ========
document.addEventListener("DOMContentLoaded", () => {
    const alertBox = document.querySelector(".alert");
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.opacity = "0";
            alertBox.style.transform = "translateY(-10px)";
            setTimeout(() => alertBox.remove(), 300);
        }, 3000);
    }
});
</script>

</body>
</html>

