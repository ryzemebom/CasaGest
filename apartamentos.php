<?php
require_once "functions.php";

// Redireciona se não estiver logado
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$action = $_GET["action"] ?? "";

function redirect($url) {
    header("Location: $url");
    exit;
}

// Criar apartamento
if ($action === "create" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $apartamentos = readData("apartamentos");

    $apartamentos[] = [
        "id" => nextId("apartamentos"),
        "numero" => $_POST["numero"],
        "endereco" => $_POST["endereco"],
        "descricao" => $_POST["descricao"],
        "valor" => floatval($_POST["valor"]),
        "status" => $_POST["status"]
    ];

    writeData("apartamentos", $apartamentos);
    redirect("apartamentos.php");
}

// Deletar apartamento
if ($action === "delete") {
    $id = intval($_GET["id"]);
    $apartamentos = readData("apartamentos");
    $apartamentos = array_filter($apartamentos, fn($a) => $a["id"] !== $id);
    writeData("apartamentos", array_values($apartamentos));
    redirect("apartamentos.php");
}

// Dados
$apartamentos = readData("apartamentos");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Apartamentos - CasaGest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<main class="container">

    <!-- Abas -->
    <div class="tabs">
        <button class="tab-button active" data-tab="add">Adicionar Apartamento</button>
        <button class="tab-button" data-tab="list">Lista de Apartamentos</button>
    </div>

    <!-- Aba Adicionar -->
    <div class="tab-content active" id="add">
        <section class="card">
            <form method="post" action="apartamentos.php?action=create">
                <div class="form-group">
                    <label for="numero">Número</label>
                    <input type="text" name="numero" id="numero" required>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" name="endereco" id="endereco">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao"></textarea>
                </div>

                <div class="form-group">
                    <label for="valor">Valor</label>
                    <input type="number" name="valor" id="valor" step="0.01">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="disponivel">Disponível</option>
                        <option value="ocupado">Ocupado</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Salvar</button>
            </form>
        </section>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content" id="list">
        <section class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Endereço</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($apartamentos)): ?>
                            <?php foreach ($apartamentos as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a["id"]); ?></td>
                                    <td><?= htmlspecialchars($a["numero"]); ?></td>
                                    <td><?= htmlspecialchars($a["endereco"]); ?></td>
                                    <td>R$ <?= number_format($a["valor"], 2, ",", "."); ?></td>
                                    <td><?= ucfirst(htmlspecialchars($a["status"])); ?></td>
                                    <td>
                                        <a href="apartamentos.php?action=delete&id=<?= $a["id"]; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este apartamento?')">
                                            Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">Nenhum apartamento registrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</main>

<script>
// JS para alternar abas
const tabs = document.querySelectorAll('.tab-button');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>
