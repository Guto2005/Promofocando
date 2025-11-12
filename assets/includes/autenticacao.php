<?php
session_start();

function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /pages/login.php");
        exit();
    }
}

function logout() {
    session_destroy();
    header("Location: /index.php");
    exit();
}
?>
