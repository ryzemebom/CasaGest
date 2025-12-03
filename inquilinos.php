<?php
require_once __DIR__ . '/functions.php';

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

if ($action == "edit" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"] ?? 0);
    $items = readData("inquilinos");
    foreach ($items as &$it) {
        if ((int)($it['id'] ?? 0) === $id) {
            $it['nome'] = $_POST['nome'] ?? $it['nome'];
            $it['email'] = $_POST['email'] ?? $it['email'];
            $it['telefone'] = $_POST['telefone'] ?? $it['telefone'];
            $it['apartamento'] = $_POST['apartamento'] ?? $it['apartamento'];
            break;
        }
    }
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
<?php include __DIR__ . '/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inquilinos - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Inquilinos</h1>
        <p>Gerenciar inquilinos e seus dados de contato</p>
    </div>

    <div class="tabs">
        <button class="tab-button active" data-tab="list"><i class="fas fa-list"></i> Lista de Inquilinos</button>
        <button class="tab-button" data-tab="add"><i class="fas fa-plus"></i> Adicionar Inquilino</button>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content active" id="list">
        <div class="card">
            <?php if (empty($inquilinos)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum inquilino cadastrado ainda</p>
                    <button class="btn tab-button-link" data-tab="add" onclick="switchTab(this)">Adicionar Primeiro Inquilino</button>
                </div>
            <?php else: ?>
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
                                <button class="btn-view" onclick='editarInquilino(<?= htmlspecialchars(json_encode($i)); ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                <a href="inquilinos.php?action=delete&id=<?= $i["id"]; ?>" class="btn-delete" onclick="return confirm('Excluir?')"><i class="fas fa-trash"></i> Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Aba Adicionar -->
    <div class="tab-content" id="add">
        <div class="card">
            <h2>Adicionar Novo Inquilino</h2>
            <form method="post" action="inquilinos.php?action=create" id="formInquilino">
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label>Nome</label>
                    <input name="nome" placeholder="Digite o nome completo" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Digite o email" required>
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input name="telefone" placeholder="Digite o telefone" required>
                </div>
                <div class="form-group">
                    <label>Apartamento</label>
                    <select name="apartamento" required>
                        <option value="">Selecione um apartamento</option>
                        <?php foreach ($apartamentos as $a): ?>
                            <option value="<?= $a['numero']; ?>"><?= $a['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Inquilino</button>
                    <button type="button" class="btn-cancel" onclick="document.querySelector('form').reset()"><i class="fas fa-times"></i> Limpar</button>
                </div>
            </form>
        </div>
    </div>

</div>

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
// Função para editar inquilino (preenche o formulário)
function editarInquilino(inquilino) {
    const form = document.getElementById('formInquilino');
    form.action = 'inquilinos.php?action=edit';
    let idField = form.querySelector('input[name="id"]');
    if (!idField) {
        idField = document.createElement('input');
        idField.type = 'hidden';
        idField.name = 'id';
        form.appendChild(idField);
    }
    idField.value = inquilino.id;
    form.querySelector('input[name="nome"]').value = inquilino.nome || '';
    form.querySelector('input[name="email"]').value = inquilino.email || '';
    form.querySelector('input[name="telefone"]').value = inquilino.telefone || '';
    const aptSel = form.querySelector('select[name="apartamento"]');
    if (aptSel) aptSel.value = inquilino.apartamento || '';
    const addTab = Array.from(tabs).find(t => t.dataset.tab === 'add');
    if (addTab) switchTab(addTab);
}
</script>

</body>
</html>
