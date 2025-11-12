<?php
function formatarPreco($preco) {
    return "R$ " . number_format($preco, 2, ',', '.');
}

function limparInput($dado) {
    return htmlspecialchars(trim($dado));
}
?>
