<?php
require_once __DIR__ . '/functions.php';

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
    $contratos = readData("contratos");
    // verificar se existem contratos vinculados a este apartamento
    $apt = null;
    foreach ($apartamentos as $a) { if ($a['id'] == $id) { $apt = $a; break; } }
    $numero = $apt['numero'] ?? null;
    if ($numero) {
        $linked = array_filter($contratos, fn($c) => (($c['apartamento'] ?? '') == $numero));
        if (!empty($linked)) {
            // não permite exclusão se houver contratos vinculados
            redirect("apartamentos.php?error=linked");
        }
    }
    $apartamentos = array_filter($apartamentos, fn($a) => $a["id"] !== $id);
    writeData("apartamentos", array_values($apartamentos));
    redirect("apartamentos.php");
}

// Editar apartamento
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $apartamentos = readData('apartamentos');
    foreach ($apartamentos as &$ap) {
        if ((int)($ap['id'] ?? 0) === $id) {
            $ap['numero'] = $_POST['numero'] ?? $ap['numero'];
            $ap['endereco'] = $_POST['endereco'] ?? $ap['endereco'];
            $ap['descricao'] = $_POST['descricao'] ?? $ap['descricao'];
            $ap['valor'] = floatval($_POST['valor'] ?? $ap['valor']);
            $ap['status'] = $_POST['status'] ?? $ap['status'];
            break;
        }
    }
    writeData('apartamentos', $apartamentos);
    redirect('apartamentos.php');
}

// Dados
$apartamentos = readData("apartamentos");
$contratos = readData("contratos");

// calcular ocupação atual baseada em contratos ativos
$occupiedMap = [];
foreach ($contratos as $c) {
    $aptNum = $c['apartamento'] ?? null;
    $status = $c['status'] ?? 'ativo';
    if ($aptNum && $status === 'ativo') {
        $occupiedMap[$aptNum] = $c['inquilino'] ?? true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Apartamentos - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-building"></i> Apartamentos</h1>
        <p>Gerenciar apartamentos e seus dados</p>
    </div>

    <!-- Abas -->
    <div class="tabs">
        <button class="tab-button active" data-tab="list"><i class="fas fa-list"></i> Lista de Apartamentos</button>
        <button class="tab-button" data-tab="add"><i class="fas fa-plus"></i> Adicionar Apartamento</button>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content active" id="list">
        <section class="card">
            <?php if (empty($apartamentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum apartamento cadastrado ainda</p>
                    <button class="btn tab-button-link" data-tab="add" onclick="switchTab(this)">Adicionar Primeiro Apartamento</button>
                </div>
            <?php else: ?>
            <?php if (!empty($_GET['error']) && $_GET['error'] === 'linked'): ?>
                <div class="card-alert">Não é possível excluir este apartamento pois existem contratos vinculados.</div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número</th>
                            <th>Inquilino (Atual)</th>
                            <th>Endereço</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($apartamentos as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a["id"]); ?></td>
                                <td><?= htmlspecialchars($a["numero"]); ?></td>
                                <td><?= htmlspecialchars($occupiedMap[$a['numero']] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($a["endereco"]); ?></td>
                                <td>R$ <?= number_format($a["valor"], 2, ",", "."); ?></td>
                                <td><?= ucfirst(htmlspecialchars($a["status"])); ?></td>
                                <td>
                                        <button class="btn-view" onclick='editarApartamento(<?= htmlspecialchars(json_encode($a)); ?>)' title="Editar"><i class="fas fa-edit"></i></button>
                                        <a href="apartamentos.php?action=delete&id=<?= $a["id"]; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este apartamento?')"><i class="fas fa-trash"></i> Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Aba Adicionar -->
    <div class="tab-content" id="add">
        <section class="card">
            <h2>Adicionar Novo Apartamento</h2>
            <form method="post" action="apartamentos.php?action=create">
                <input type="hidden" name="id" value="">
                <div class="form-group">
                    <label for="numero">Número</label>
                    <input type="text" name="numero" id="numero" placeholder="Digite o número do apartamento" required>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" name="endereco" id="endereco" placeholder="Digite o endereço">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" placeholder="Digite a descrição"></textarea>
                </div>

                <div class="form-group">
                    <label for="valor">Valor</label>
                    <input type="number" name="valor" id="valor" step="0.01" placeholder="Digite o valor">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="disponivel">Disponível</option>
                        <option value="ocupado">Ocupado</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Apartamento</button>
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

// Função para editar apartamento (prefill form e muda ação para edit)
function editarApartamento(ap) {
    const form = document.querySelector('form[action="apartamentos.php?action=create"]');
    form.action = 'apartamentos.php?action=edit';
    let idField = form.querySelector('input[name="id"]');
    if (!idField) {
        idField = document.createElement('input');
        idField.type = 'hidden';
        idField.name = 'id';
        form.appendChild(idField);
    }
    idField.value = ap.id;
    form.querySelector('input[name="numero"]').value = ap.numero || '';
    form.querySelector('input[name="endereco"]').value = ap.endereco || '';
    form.querySelector('textarea[name="descricao"]').value = ap.descricao || '';
    form.querySelector('input[name="valor"]').value = parseFloat(ap.valor || 0).toFixed(2);
    form.querySelector('select[name="status"]').value = ap.status || 'disponivel';
    const addTab = Array.from(tabs).find(t => t.dataset.tab === 'add');
    if (addTab) switchTab(addTab);
}
</script>

</body>
</html>
