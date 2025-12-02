<?php
require_once "functions.php";

// Redireciona se não estiver logado
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$action = $_GET["action"] ?? "";

// Função de redirecionamento
function redirect($url) {
    header("Location: $url");
    exit;
}

// Criar pagamento
if ($action === "create" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $pagamentos = readData("pagamentos");

    $pagamentos[] = [
        "id" => nextId("pagamentos"),
        "inquilino" => $_POST["inquilino"],
        "apartamento" => $_POST["apartamento"],
        "valor" => floatval($_POST["valor"]),
        "data" => $_POST["data"],
        "status" => $_POST["status"]
    ];

    writeData("pagamentos", $pagamentos);
    redirect("pagamentos.php");
}

// Deletar pagamento
if ($action === "delete") {
    $id = intval($_GET["id"]);
    $pagamentos = readData("pagamentos");
    $pagamentos = array_filter($pagamentos, fn($p) => $p["id"] !== $id);
    writeData("pagamentos", array_values($pagamentos));
    redirect("pagamentos.php");
}

// Dados
$pagamentos = readData("pagamentos");
$inquilinos = readData("inquilinos");
$apartamentos = readData("apartamentos");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Pagamentos - RentMaster</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<main class="main-content">

    <!-- Formulário de Pagamento -->
    <section class="card">
        <h2><i class="fa fa-dollar-sign"></i> Registrar Pagamento</h2>
        <form method="post" action="pagamentos.php?action=create">
            <div class="form-group">
                <label for="inquilino">Inquilino</label>
                <select name="inquilino" id="inquilino" required>
                    <option value="">Selecione</option>
                    <?php foreach ($inquilinos as $i): ?>
                        <option value="<?= htmlspecialchars($i['nome']); ?>"><?= htmlspecialchars($i['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="apartamento">Apartamento</label>
                <select name="apartamento" id="apartamento" required>
                    <option value="">Selecione</option>
                    <?php foreach ($apartamentos as $a): ?>
                        <option value="<?= htmlspecialchars($a['numero']); ?>"><?= htmlspecialchars($a['numero']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="valor">Valor</label>
                <input type="number" name="valor" id="valor" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="data">Data</label>
                <input type="date" name="data" id="data" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="pago">Pago</option>
                    <option value="pendente">Pendente</option>
                </select>
            </div>

            <button type="submit" class="btn-submit"><i class="fa fa-plus"></i> Salvar</button>
        </form>
    </section>

    <!-- Lista de Pagamentos -->
    <section class="card">
        <h2><i class="fa fa-list"></i> Lista de Pagamentos</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Inquilino</th>
                        <th>Apartamento</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pagamentos)): ?>
                        <?php foreach ($pagamentos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p["id"]); ?></td>
                                <td><?= htmlspecialchars($p["inquilino"]); ?></td>
                                <td><?= htmlspecialchars($p["apartamento"]); ?></td>
                                <td>R$ <?= number_format($p["valor"], 2, ",", "."); ?></td>
                                <td><?= htmlspecialchars($p["data"]); ?></td>
                                <td><?= ucfirst(htmlspecialchars($p["status"])); ?></td>
                                <td>
                                    <a href="pagamentos.php?action=delete&id=<?= $p["id"]; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este pagamento?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Nenhum pagamento registrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>
