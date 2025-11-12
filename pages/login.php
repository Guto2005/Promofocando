<?php
require_once __DIR__ . '/../assets/includes/conexao.php';
require_once __DIR__ . '/../assets/includes/helpers.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo "<script>alert('Preencha todos os campos.');</script>";
    } else {
        try {
            // Busca pelo nomeUsuario (ajustado conforme sua tabela)
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nomeUsuario = :usuario LIMIT 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se achou o usuário e valida a senha (com ou sem hash)
            if ($user && ($senha === $user['senhaUsuario'] || password_verify($senha, $user['senhaUsuario']))) {
                $_SESSION['usuario'] = $user['nomeUsuario'];
                $_SESSION['tipoUsuario'] = $user['tipoUsuario'];
               header("Location: ../admin/dashboard/index.php");

                exit;
            } else {
                echo "<script>alert('Usuário ou senha incorretos.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Erro no login: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login - Promofocando</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

    <div class="login-container">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>

</body>

</html>