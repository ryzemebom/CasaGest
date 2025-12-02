<?php
require_once "functions.php";
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$apartamentos = readData("apartamentos");
$inquilinos = readData("inquilinos");
$pagamentos = readData("pagamentos");
$contratos = readData("contratos");
$manutencoes = readData("manutencoes");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - RentMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div class="main-content">
    <div class="cards">
        <div class="card">
            <h2>Apartamentos</h2>
            <p><?php echo count($apartamentos); ?></p>
        </div>
        <div class="card">
            <h2>Inquilinos</h2>
            <p><?php echo count($inquilinos); ?></p>
        </div>
        <div class="card">
            <h2>Pagamentos</h2>
            <p><?php echo count($pagamentos); ?></p>
        </div>
        <div class="card">
            <h2>Contratos</h2>
            <p><?php echo count($contratos); ?></p>
        </div>
        <div class="card">
            <h2>Manutenções</h2>
            <p><?php echo count($manutencoes); ?></p>
        </div>
    </div>
</div>
</body>
</html>
