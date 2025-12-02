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

    // Se foi selecionado um contrato, prefira os dados do contrato
    $contratoId = $_POST["contrato_id"] ?? "";
    $inquilino = $_POST["inquilino"] ?? "";
    $apartamentoSel = $_POST["apartamento"] ?? "";
    $valor = floatval($_POST["valor"] ?? 0);

    if (!empty($contratoId)) {
        $contratosList = readData("contratos");
        foreach ($contratosList as $c) {
            if ((string)$c["id"] === (string)$contratoId) {
                $inquilino = $c["inquilino"] ?? $inquilino;
                $apartamentoSel = $c["apartamento"] ?? $apartamentoSel;
                // use valor_aluguel do contrato somente se valor não foi sobrescrito (0 ou vazio)
                if (empty($_POST["override_valor"])) {
                    $valor = floatval($c["valor_aluguel"] ?? $valor);
                }
                break;
            }
        }
    }

    $pagamentos[] = [
        "id" => nextId("pagamentos"),
        "contrato_id" => $contratoId,
        "inquilino" => $inquilino,
        "apartamento" => $apartamentoSel,
        "valor" => $valor,
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
$contratos = readData("contratos");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Pagamentos - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-dollar-sign"></i> Pagamentos</h1>
        <p>Gerenciar pagamentos de aluguel</p>
    </div>

    <!-- Abas -->
    <div class="tabs">
        <button class="tab-button active" data-tab="list"><i class="fas fa-list"></i> Lista de Pagamentos</button>
        <button class="tab-button" data-tab="add"><i class="fas fa-plus"></i> Registrar Pagamento</button>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content active" id="list">
        <section class="card">
            <?php if (empty($pagamentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum pagamento registrado ainda</p>
                    <button class="btn tab-button-link" data-tab="add" onclick="switchTab(this)">Registrar Primeiro Pagamento</button>
                </div>
            <?php else: ?>
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
                        <?php foreach ($pagamentos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p["id"]); ?></td>
                                <td><?= htmlspecialchars($p["inquilino"]); ?></td>
                                <td><?= htmlspecialchars($p["apartamento"]); ?></td>
                                <td>R$ <?= number_format($p["valor"], 2, ",", "."); ?></td>
                                <td><?= htmlspecialchars($p["data"]); ?></td>
                                <td><?= ucfirst(htmlspecialchars($p["status"])); ?></td>
                                <td>
                                    <a href="pagamentos.php?action=delete&id=<?= $p["id"]; ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este pagamento?')"><i class="fas fa-trash"></i> Excluir</a>
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
            <h2>Registrar Novo Pagamento</h2>
            <form method="post" action="pagamentos.php?action=create">
            <div class="form-group">
                        <label for="contrato_id">Contrato (opcional)</label>
                        <select name="contrato_id" id="contrato_id">
                            <option value="">Selecionar contrato ou escolha manualmente</option>
                            <?php foreach ($contratos as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']); ?>">#<?= htmlspecialchars($c['id']); ?> - <?= htmlspecialchars($c['inquilino']); ?> - <?= htmlspecialchars($c['apartamento']); ?> - R$ <?= number_format($c['valor_aluguel'] ?? 0,2,',','.'); ?></option>
                            <?php endforeach; ?>
                        </select>
            </div>

                    <div class="form-group">
                        <label for="inquilino">Inquilino</label>
                        <select name="inquilino" id="inquilino" required>
                            <option value="">Selecione um inquilino</option>
                            <?php foreach ($inquilinos as $i): ?>
                                <option value="<?= htmlspecialchars($i['nome']); ?>"><?= htmlspecialchars($i['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

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
                        <label for="valor">Valor</label>
                        <input type="number" name="valor" id="valor" step="0.01" placeholder="Valor será preenchido pelo contrato" required>
                        <div style="margin-top:8px; font-size:0.9rem; color:#6b7280;">
                            <label><input type="checkbox" id="override_valor" name="override_valor" value="1"> Permitir editar valor manualmente</label>
                        </div>
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

            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Pagamento</button>
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

// JS para auto preencher valor/inquilino/apartamento conforme contrato selecionado
const contratos = <?= json_encode($contratos); ?>;
const contratoSelect = document.getElementById('contrato_id');
const inquilinoSelect = document.getElementById('inquilino');
const aptSelect = document.getElementById('apartamento');
const valorInput = document.getElementById('valor');
const overrideCheckbox = document.getElementById('override_valor');

function findContratoById(id) {
    if (!id) return null;
    return contratos.find(c => String(c.id) === String(id)) || null;
}

contratoSelect && contratoSelect.addEventListener('change', function() {
    const c = findContratoById(this.value);
    if (c) {
        // preencher inquilino e apartamento se existirem nas options
        for (let i=0;i<inquilinoSelect.options.length;i++) {
            if (inquilinoSelect.options[i].value === c.inquilino) { inquilinoSelect.selectedIndex = i; break; }
        }
        for (let i=0;i<aptSelect.options.length;i++) {
            if (aptSelect.options[i].value === c.apartamento) { aptSelect.selectedIndex = i; break; }
        }
        // preencher valor do contrato se override não estiver marcado
        if (!overrideCheckbox.checked) {
            valorInput.value = parseFloat(c.valor_aluguel || 0).toFixed(2);
            valorInput.readOnly = true;
        }
    } else {
        // limpar preenchimento automático
        if (!overrideCheckbox.checked) {
            valorInput.value = '';
            valorInput.readOnly = false;
        }
    }
});

overrideCheckbox && overrideCheckbox.addEventListener('change', function() {
    if (this.checked) {
        valorInput.readOnly = false;
    } else {
        // reaplicar valor do contrato se houver um contrato selecionado
        const c = findContratoById(contratoSelect.value);
        if (c) {
            valorInput.value = parseFloat(c.valor_aluguel || 0).toFixed(2);
            valorInput.readOnly = true;
        }
    }
});
</script>
</body>
</html>
