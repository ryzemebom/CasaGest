<?php
require_once "functions.php";
$config = include "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = $_POST["username"];
    $p = $_POST["password"];

    if ($u == $config["username"] && $p == $config["password"]) {
        $_SESSION["user"] = $u;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Apartment Manager</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="login-box">
    <!-- Logo -->
    <img src="https://images.vexels.com/media/users/3/136535/isolated/lists/393a7d8e436bccc3aedfd43865b48890-icone-de-cadeado.png" alt="Logo Apartment Manager">

    <!-- Mensagem de erro   <h2>Apartment Manager</h2>-->

    <!-- Mensagem de erro -->
    <?php if ($error): ?>
        <div class="alert"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <!-- Campo usuário -->
        <div class="input-group">
            <label for="username">Usuário</label>
            <div class="input-icon">
                <i class="fa fa-user"></i>
                <input type="text" name="username" id="username" placeholder="Digite seu usuário" required>
            </div>
        </div>

        <!-- Campo senha -->
        <div class="input-group">
            <label for="password">Senha</label>
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Digite sua senha" required>
            </div>
        </div>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
