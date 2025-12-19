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

    // SALVAR
    if ($acao === 'salvar') {
        $nome = $_POST['nomeLoja'] ?? null;
        $site = $_POST['siteLoja'] ?? null;
        $logo = null;

        if (!$nome || !$site) {
            echo json_encode(['sucesso' => false, 'msg' => 'Dados incompletos']);
            exit;
        }

        if (!empty($_POST['logoURL'])) {
            $logo = $_POST['logoURL'];
        } elseif (!empty($_FILES['logoUpload']['name'])) {
            $arquivo = $_FILES['logoUpload'];
            $nomeArq = time() . "_" . basename($arquivo['name']);
            $destino = "../../assets/images/lojas/" . $nomeArq;
            move_uploaded_file($arquivo['tmp_name'], $destino);
            $logo = $nomeArq;
        }

        $stmt = $pdo->prepare("INSERT INTO lojas (nomeLoja, siteLoja, logoLoja) VALUES (?, ?, ?)");
        $res = $stmt->execute([$nome, $site, $logo]);
        echo json_encode(['sucesso' => $res]);
        exit;
    }

    // EDITAR
    if ($acao === 'editar') {
        $id = $_POST['idLoja'] ?? null;
        $nome = $_POST['nomeLoja'] ?? null;
        $site = $_POST['siteLoja'] ?? null;
        $logo = $_POST['logoAtual'] ?? null;

        if (!$id || !$nome || !$site) {
            echo json_encode(['sucesso' => false, 'msg' => 'Dados incompletos']);
            exit;
        }

        if (!empty($_POST['logoURL'])) {
            $logo = $_POST['logoURL'];
        } elseif (!empty($_FILES['logoUpload']['name'])) {
            $arquivo = $_FILES['logoUpload'];
            $nomeArq = time() . "_" . basename($arquivo['name']);
            $destino = "../../assets/images/lojas/" . $nomeArq;
            move_uploaded_file($arquivo['tmp_name'], $destino);
            $logo = $nomeArq;
        }

        $stmt = $pdo->prepare("UPDATE lojas SET nomeLoja=?, siteLoja=?, logoLoja=? WHERE idLoja=?");
        $res = $stmt->execute([$nome, $site, $logo, $id]);
        echo json_encode(['sucesso' => $res]);
        exit;
    }

    // EXCLUIR
    if ($acao === 'excluir') {
        $id = $_POST['idLoja'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM lojas WHERE idLoja=?");
            $res = $stmt->execute([$id]);
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
            $stmt = $pdo->prepare("DELETE FROM lojas WHERE idLoja IN ($placeholders)");
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
// CARREGAR DADOS PARA TABELA E SIDEBAR
// ======================
$lojas = $pdo->query("SELECT * FROM lojas ORDER BY nomeLoja ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerenciar Lojas</title>
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
main { flex:1; padding:20px; margin-top:60px; overflow-x:auto; }
table { width:100%; border-collapse:collapse; }
table th, table td { border:1px solid #ccc; padding:5px; text-align:left; }
.checkbox-col { text-align:center; width:40px; }
button { cursor:pointer; margin:2px; }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal-content { background:#fff; padding:20px; border-radius:5px; width:400px; }
.alert { padding:10px; margin-bottom:10px; }
.success { background:#d4edda; color:#155724; }
</style>
</head>
<body>

<header>
    <h1>Gerenciar Lojas</h1>
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
        <h2>Geral – Lojas</h2>
        <?php foreach($lojas as $loja): ?>
            <button onclick="filtrarLoja(<?= $loja['idLoja'] ?>)">
                <?= htmlspecialchars($loja['nomeLoja']) ?>
            </button>
        <?php endforeach; ?>
        <button onclick="filtrarLoja(null)">Mostrar Todas</button>
    </div>

    <main>
        <button onclick="abrirModal('adicionar')">➕ Adicionar Loja</button>
        <button onclick="excluirSelecionados()">🗑️ Excluir Selecionadas</button>

        <table id="lojasTable">
            <thead>
                <tr>
                    <th class="checkbox-col"><input type="checkbox" id="checkAll"></th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Site</th>
                    <th>Logo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($lojas as $loja): ?>
                <tr data-id="<?= $loja['idLoja'] ?>">
                    <td class="checkbox-col"><input type="checkbox" class="checkItem" value="<?= $loja['idLoja'] ?>"></td>
                    <td><?= $loja['idLoja'] ?></td>
                    <td><?= htmlspecialchars($loja['nomeLoja']) ?></td>
                    <td><a href="<?= htmlspecialchars($loja['siteLoja']) ?>" target="_blank">Visitar</a></td>
                    <td>
                        <?php if($loja['logoLoja']): ?>
                            <img src="<?= str_starts_with($loja['logoLoja'],'http') ? $loja['logoLoja'] : '../../assets/images/lojas/'.$loja['logoLoja'] ?>" width="50">
                        <?php else: ?> — <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="abrirModal('editar', <?= $loja['idLoja'] ?>, '<?= htmlspecialchars($loja['nomeLoja'], ENT_QUOTES) ?>', '<?= htmlspecialchars($loja['siteLoja'], ENT_QUOTES) ?>', '<?= htmlspecialchars($loja['logoLoja'], ENT_QUOTES) ?>')">✏️ Editar</button>
                        <button onclick="excluirLoja(<?= $loja['idLoja'] ?>)">🗑️ Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <h3 id="modalTitle">Adicionar Loja</h3>
        <form id="formLoja" enctype="multipart/form-data">
            <input type="hidden" name="acao" id="acaoForm">
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

            <button type="submit">Salvar</button>
            <button type="button" onclick="fecharModal()">Cancelar</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('modal');
const formLoja = document.getElementById('formLoja');

// Abrir modal
function abrirModal(acao, id='', nome='', site='', logo='') {
    modal.style.display = 'flex';
    document.getElementById('acaoForm').value = acao;
    if (acao === 'editar') {
        document.getElementById('modalTitle').textContent = 'Editar Loja';
        document.getElementById('editIdLoja').value = id;
        document.getElementById('editNomeLoja').value = nome;
        document.getElementById('editSiteLoja').value = site;
        document.getElementById('editLogoAtual').value = logo;
        document.getElementById('editLogoPreview').src = logo.startsWith('http') ? logo : '../../assets/images/lojas/'+logo;
        document.getElementById('editLogoPreview').style.display = 'block';
    } else {
        document.getElementById('modalTitle').textContent = 'Adicionar Loja';
        formLoja.reset();
        document.getElementById('editLogoPreview').style.display = 'none';
    }
}

// Fechar modal
function fecharModal() {
    modal.style.display = 'none';
}

// Submit via AJAX
formLoja.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(formLoja);
    fetch('', {method:'POST', body:data})
        .then(res => res.json())
        .then(res => {
            if(res.sucesso) location.reload();
            else alert('Erro: '+(res.msg||'Não foi possível salvar'));
        });
});

// Excluir loja
function excluirLoja(id) {
    if(!confirm('Deseja realmente excluir esta loja?')) return;
    const data = new URLSearchParams({acao:'excluir', idLoja:id});
    fetch('', {method:'POST', body:data})
        .then(res=>res.json()).then(res=>{
            if(res.sucesso) location.reload();
            else alert('Erro ao excluir');
        });
}

// Excluir selecionadas
function excluirSelecionados() {
    const ids = Array.from(document.querySelectorAll('.checkItem:checked')).map(c=>c.value);
    if(ids.length===0) return alert('Selecione ao menos uma loja');
    if(!confirm('Deseja excluir as lojas selecionadas?')) return;
    const data = new URLSearchParams({acao:'excluir_multiplos', ids: ids.join(',')});
    fetch('', {method:'POST', body:data})
        .then(res=>res.json()).then(res=>{
            if(res.sucesso) location.reload();
            else alert('Erro ao excluir');
        });
}

// Marcar todos
document.getElementById('checkAll').addEventListener('change', function(){
    document.querySelectorAll('.checkItem').forEach(cb => cb.checked = this.checked);
});

// Filtrar tabela ao clicar na sidebar
function filtrarLoja(id) {
    const rows = document.querySelectorAll('#lojasTable tbody tr');
    rows.forEach(row => {
        if(id === null) { row.style.display = ''; return; }
        row.style.display = (row.dataset.id == id) ? '' : 'none';
    });
}
</script>

</body>
</html>

