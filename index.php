<?php
session_start();

// Verifica se usuário está logado
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

// Caso não esteja logado, redireciona para login
header("Location: login.php");
exit;
?>
