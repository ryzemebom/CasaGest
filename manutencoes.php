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
    <title>Manutenções - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tools"></i> Manutenções</h1>
        <p>Gerenciar manutenções dos apartamentos</p>
    </div>

    <!-- Abas -->
    <div class="tabs">
        <button class="tab-button active" data-tab="list"><i class="fas fa-list"></i> Lista de Manutenções</button>
        <button class="tab-button" data-tab="add"><i class="fas fa-plus"></i> Registrar Manutenção</button>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content active" id="list">
        <section class="card">
            <?php if (empty($manutencoes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhuma manutenção registrada ainda</p>
                    <button class="btn tab-button-link" data-tab="add" onclick="switchTab(this)">Registrar Primeira Manutenção</button>
                </div>
            <?php else: ?>
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
                    <?php foreach ($manutencoes as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m["id"]); ?></td>
                            <td><?= htmlspecialchars($m["apartamento"]); ?></td>
                            <td><?= htmlspecialchars($m["descricao"]); ?></td>
                            <td><?= htmlspecialchars($m["data"]); ?></td>
                            <td><?= ucfirst(htmlspecialchars($m["status"])); ?></td>
                            <td>
                                <a href="manutencoes.php?action=delete&id=<?= htmlspecialchars($m["id"]); ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir?')"><i class="fas fa-trash"></i> Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </div>

    <!-- Aba Adicionar -->
    <div class="tab-content" id="add">
        <section class="card">
            <h2>Registrar Nova Manutenção</h2>
            <form method="post" action="manutencoes.php?action=create">
                <div class="form-group">
                    <label for="apartamento">Apartamento</label>
                    <select name="apartamento" id="apartamento" required>
                        <option value="">Selecione um apartamento</option>
                        <?php foreach ($apartamentos as $a): ?>
                            <option value="<?= htmlspecialchars($a['numero']); ?>"><?= htmlspecialchars($a['numero']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" placeholder="Descreva o problema ou manutenção necessária" required></textarea>
                </div>

                <div class="form-group">
                    <label for="data">Data</label>
                    <input type="date" name="data" id="data" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="pendente">Pendente</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Manutenção</button>
                    <button type="button" class="btn-cancel" onclick="document.querySelector('form').reset()"><i class="fas fa-times"></i> Limpar</button>
                </div>
            </form>
        </section>
    </div>
</main>

<script>
// JS para alternar abas
const tabs = document.querySelectorAll('.tab-button');
const contents = document.querySelectorAll('.tab-content');

function switchTab(element) {
    const tabId = element.dataset.tab;
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.classList.remove('active'));
    element.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        switchTab(tab);
    });
});
</script>
</body>
</html>
