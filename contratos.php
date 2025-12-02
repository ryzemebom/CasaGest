<?php
require_once "functions.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$action = $_GET["action"] ?? "";

if ($action == "create" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $items = readData("contratos");

    $items[] = [
        "id" => nextId("contratos"),
        "inquilino" => $_POST["inquilino"],
        "apartamento" => $_POST["apartamento"],
        "inicio" => $_POST["inicio"],
        "fim" => $_POST["fim"],
        "arquivo" => $_POST["arquivo"] // pode futuramente usar upload
    ];

    writeData("contratos", $items);
    header("Location: contratos.php");
    exit;
}

if ($action == "delete") {
    $id = intval($_GET["id"]);
    $items = readData("contratos");
    $items = array_filter($items, fn($i) => $i["id"] != $id);
    writeData("contratos", array_values($items));
    header("Location: contratos.php");
    exit;
}

$contratos = readData("contratos");
$inquilinos = readData("inquilinos");
$apartamentos = readData("apartamentos");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contratos - RentMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div class="main-content">
    <div class="card">
        <h2>Cadastrar Contrato</h2>
        <form method="post" action="contratos.php?action=create">
            <label>Inquilino</label>
            <select name="inquilino" required>
                <option value="">Selecione</option>
                <?php foreach ($inquilinos as $i): ?>
                    <option value="<?php echo $i['nome']; ?>"><?php echo $i['nome']; ?></option>
                <?php endforeach; ?>
            </select>
            <label>Apartamento</label>
            <select name="apartamento" required>
                <option value="">Selecione</option>
                <?php foreach ($apartamentos as $a): ?>
                    <option value="<?php echo $a['numero']; ?>"><?php echo $a['numero']; ?></option>
                <?php endforeach; ?>
            </select>
            <label>Início</label>
            <input type="date" name="inicio" required>
            <label>Fim</label>
            <input type="date" name="fim" required>
            <label>Arquivo (nome/futuro upload)</label>
            <input type="text" name="arquivo">
            <button type="submit">Salvar</button>
        </form>
    </div>

    <div class="card">
        <h2>Lista de Contratos</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Inquilino</th>
                    <th>Apartamento</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Arquivo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contratos as $c): ?>
                <tr>
                    <td><?php echo $c["id"]; ?></td>
                    <td><?php echo $c["inquilino"]; ?></td>
                    <td><?php echo $c["apartamento"]; ?></td>
                    <td><?php echo $c["inicio"]; ?></td>
                    <td><?php echo $c["fim"]; ?></td>
                    <td><?php echo $c["arquivo"]; ?></td>
                    <td>
                        <a href="contratos.php?action=delete&id=<?php echo $c["id"]; ?>" onclick="return confirm('Excluir?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
