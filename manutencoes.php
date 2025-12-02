<?php
require_once "functions.php";

// Verifica se o usuário está logado
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Obtém a ação da URL
$action = $_GET["action"] ?? "";

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit;
}

// Criar manutenção
if ($action === "create" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $manutencoes = readData("manutencoes");

    $manutencoes[] = [
        "id" => nextId("manutencoes"),
        "apartamento" => $_POST["apartamento"],
        "descricao" => $_POST["descricao"],
        "data" => $_POST["data"],
        "status" => $_POST["status"]
    ];

    writeData("manutencoes", $manutencoes);
    redirect("manutencoes.php");
}

// Excluir manutenção
if ($action === "delete") {
    $id = intval($_GET["id"]);
    $manutencoes = readData("manutencoes");
    $manutencoes = array_filter($manutencoes, fn($m) => $m["id"] !== $id);
    writeData("manutencoes", array_values($manutencoes));
    redirect("manutencoes.php");
}

// Carrega dados
$manutencoes = readData("manutencoes");
$apartamentos = readData("apartamentos");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Manutenções - RentMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<main class="main-content">
    <!-- Formulário de Registro -->
    <section class="card">
        <h2>Registrar Manutenção</h2>
        <form method="post" action="manutencoes.php?action=create">
            <label for="apartamento">Apartamento</label>
            <select name="apartamento" id="apartamento" required>
                <option value="">Selecione</option>
                <?php foreach ($apartamentos as $a): ?>
                    <option value="<?= htmlspecialchars($a['numero']); ?>"><?= htmlspecialchars($a['numero']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" required></textarea>

            <label for="data">Data</label>
            <input type="date" name="data" id="data" required>

            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="pendente">Pendente</option>
                <option value="concluido">Concluído</option>
            </select>

            <button type="submit">Salvar</button>
        </form>
    </section>

    <!-- Lista de Manutenções -->
    <section class="card">
        <h2>Lista de Manutenções</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Apartamento</th>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($manutencoes)): ?>
                    <?php foreach ($manutencoes as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m["id"]); ?></td>
                            <td><?= htmlspecialchars($m["apartamento"]); ?></td>
                            <td><?= htmlspecialchars($m["descricao"]); ?></td>
                            <td><?= htmlspecialchars($m["data"]); ?></td>
                            <td><?= htmlspecialchars($m["status"]); ?></td>
                            <td>
                                <a href="manutencoes.php?action=delete&id=<?= $m["id"]; ?>" onclick="return confirm('Deseja realmente excluir?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhuma manutenção registrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
