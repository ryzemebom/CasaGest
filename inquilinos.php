<?php
require_once "functions.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$action = $_GET["action"] ?? "";

if ($action == "create" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $items = readData("inquilinos");

    $items[] = [
        "id" => nextId("inquilinos"),
        "nome" => $_POST["nome"],
        "email" => $_POST["email"],
        "telefone" => $_POST["telefone"],
        "apartamento" => $_POST["apartamento"]
    ];

    writeData("inquilinos", $items);
    header("Location: inquilinos.php");
    exit;
}

if ($action == "delete") {
    $id = intval($_GET["id"]);
    $items = readData("inquilinos");
    $items = array_filter($items, fn($i) => $i["id"] != $id);
    writeData("inquilinos", array_values($items));
    header("Location: inquilinos.php");
    exit;
}

$inquilinos = readData("inquilinos");
$apartamentos = readData("apartamentos");
?>
<?php include "header.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inquilinos - RentMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-content">

    <div class="tabs">
        <button class="tab-button active" data-tab="add">Adicionar Inquilino</button>
        <button class="tab-button" data-tab="list">Lista de Inquilinos</button>
    </div>

    <!-- Aba Adicionar -->
    <div class="tab-content active" id="add">
        <div class="card">
            <form method="post" action="inquilinos.php?action=create">
                <div class="form-group">
                    <label>Nome</label>
                    <input name="nome" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input name="telefone">
                </div>
                <div class="form-group">
                    <label>Apartamento</label>
                    <select name="apartamento" required>
                        <option value="">Selecione</option>
                        <?php foreach ($apartamentos as $a): ?>
                            <option value="<?= $a['numero']; ?>"><?= $a['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Salvar</button>
            </form>
        </div>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content" id="list">
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Apartamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquilinos as $i): ?>
                        <tr>
                            <td><?= $i["id"]; ?></td>
                            <td><?= $i["nome"]; ?></td>
                            <td><?= $i["email"]; ?></td>
                            <td><?= $i["telefone"]; ?></td>
                            <td><?= $i["apartamento"]; ?></td>
                            <td>
                                <a href="inquilinos.php?action=delete&id=<?= $i["id"]; ?>" class="btn-delete" onclick="return confirm('Excluir?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
// JS para alternar abas
const tabs = document.querySelectorAll('.tab-button');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        // Remove active das abas e conteúdos
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));

        // Ativa aba e conteúdo clicado
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>
