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
        "valor_aluguel" => floatval($_POST["valor_aluguel"]),
        "inicio" => $_POST["inicio"],
        "fim" => $_POST["fim"],
        "status" => $_POST["status"],
        "tipo_contrato" => $_POST["tipo_contrato"],
        "arquivo" => $_POST["arquivo"],
        "data_criacao" => date("Y-m-d")
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

if ($action == "edit" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $items = readData("contratos");
    
    foreach ($items as &$item) {
        if ($item["id"] == $id) {
            $item["inquilino"] = $_POST["inquilino"];
            $item["apartamento"] = $_POST["apartamento"];
            $item["valor_aluguel"] = floatval($_POST["valor_aluguel"]);
            $item["inicio"] = $_POST["inicio"];
            $item["fim"] = $_POST["fim"];
            $item["status"] = $_POST["status"];
            $item["tipo_contrato"] = $_POST["tipo_contrato"];
            $item["arquivo"] = $_POST["arquivo"];
            break;
        }
    }
    
    writeData("contratos", $items);
    header("Location: contratos.php");
    exit;
}

$contratos = readData("contratos");
$inquilinos = readData("inquilinos");
$apartamentos = readData("apartamentos");
// mapear ocupação por apartamento
$occupiedMap = [];
foreach ($contratos as $c) {
    $aptNum = $c['apartamento'] ?? null;
    $status = $c['status'] ?? 'ativo';
    if ($aptNum && $status === 'ativo') {
        $occupiedMap[$aptNum] = $c['inquilino'] ?? true;
    }
}

// Filtro de status
$filtro_status = $_GET["status"] ?? "";
$contratos_filtrados = $contratos;
if ($filtro_status) {
    $contratos_filtrados = array_filter($contratos, fn($c) => ($c["status"] ?? "ativo") == $filtro_status);
}

// classes para botões de filtro (corrige possíveis problemas de renderização)
$class_all = $filtro_status === "" ? 'filter-btn active' : 'filter-btn';
$class_ativo = $filtro_status === 'ativo' ? 'filter-btn active' : 'filter-btn';
$class_inativo = $filtro_status === 'inativo' ? 'filter-btn active' : 'filter-btn';

// Calcular estatísticas
$total_contratos = count($contratos);
$ativos = count(array_filter($contratos, fn($c) => ($c["status"] ?? "ativo") == "ativo"));
$inativos = count(array_filter($contratos, fn($c) => ($c["status"] ?? "ativo") == "inativo"));
$vencidos = count(array_filter($contratos, fn($c) => strtotime($c["fim"]) < time() && ($c["status"] ?? "ativo") == "ativo"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contratos - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-file-contract"></i> Contratos de Aluguel</h1>
        <p>Gerenciar e acompanhar contratos de aluguel de forma profissional</p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-file-contract"></i>
            <div class="stat-info">
                <h3><?= $total_contratos; ?></h3>
                <p>Total de Contratos</p>
            </div>
        </div>
        <div class="stat-card success">
            <i class="fas fa-check-circle"></i>
            <div class="stat-info">
                <h3><?= $ativos; ?></h3>
                <p>Contratos Ativos</p>
            </div>
        </div>
        <div class="stat-card warning">
            <i class="fas fa-clock"></i>
            <div class="stat-info">
                <h3><?= $vencidos; ?></h3>
                <p>Contratos Vencidos</p>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-ban"></i>
            <div class="stat-info">
                <h3><?= $inativos; ?></h3>
                <p>Contratos Inativos</p>
            </div>
        </div>
    </div>

    <!-- Abas -->
    <div class="tabs">
        <button class="tab-button active" data-tab="list"><i class="fas fa-list"></i> Lista de Contratos</button>
        <button class="tab-button" data-tab="add"><i class="fas fa-plus"></i> Cadastrar Contrato</button>
    </div>

    <!-- Aba Lista -->
    <div class="tab-content active" id="list">
        <div class="card">
            <?php if (empty($contratos_filtrados)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Nenhum contrato encontrado</p>
                    <button class="btn tab-button-link" data-tab="add" onclick="switchTab(this)">Cadastrar Primeiro Contrato</button>
                </div>
            <?php else: ?>
            <div class="filters">
                <a href="contratos.php?status=" class="<?= $class_all; ?>">Todos</a>
                <a href="contratos.php?status=ativo" class="<?= $class_ativo; ?>"><i class="fas fa-check"></i> Ativos</a>
                <a href="contratos.php?status=inativo" class="<?= $class_inativo; ?>"><i class="fas fa-ban"></i> Inativos</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Inquilino</th>
                            <th>Apartamento</th>
                            <th>Valor Aluguel</th>
                            <th>Tipo</th>
                            <th>Período</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contratos_filtrados as $c): ?>
                        <tr class="<?= ($c['status'] ?? 'ativo') == 'inativo' ? 'tr-inactive' : ''; ?>">
                            <td><strong>#<?= htmlspecialchars($c["id"]); ?></strong></td>
                            <td><?= htmlspecialchars($c["inquilino"]); ?></td>
                            <td><?= htmlspecialchars($c["apartamento"]); ?></td>
                            <td>R$ <?= number_format($c["valor_aluguel"] ?? 0, 2, ",", "."); ?></td>
                            <td><?= htmlspecialchars($c["tipo_contrato"] ?? "Padrão"); ?></td>
                            <td>
                                <small>
                                    <?= date("d/m/Y", strtotime($c["inicio"])); ?> até <?= date("d/m/Y", strtotime($c["fim"])); ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge <?= ($c['status'] ?? 'ativo') == 'ativo' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?= ucfirst($c["status"] ?? "ativo"); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="editarContrato(<?= htmlspecialchars(json_encode($c)); ?>)" title="Editar"><i class="fas fa-edit"></i></button>
                                    <a href="contratos.php?action=delete&id=<?= htmlspecialchars($c["id"]); ?>" class="btn-delete" onclick="return confirm('Deseja realmente excluir este contrato?')" title="Excluir"><i class="fas fa-trash"></i></a>
                                </div>
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
            <h2>Cadastrar Novo Contrato</h2>
            <form method="post" action="contratos.php?action=create" id="formContrato">
                <div class="form-row">
                    <div class="form-group">
                        <label>Inquilino <span class="required">*</span></label>
                        <select name="inquilino" required>
                            <option value="">Selecione um inquilino</option>
                            <?php foreach ($inquilinos as $i): ?>
                                <option value="<?= htmlspecialchars($i['nome']); ?>"><?= htmlspecialchars($i['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Apartamento <span class="required">*</span></label>
                        <select name="apartamento" required>
                            <option value="">Selecione um apartamento</option>
                            <?php foreach ($apartamentos as $a): 
                                $num = $a['numero'];
                                $isOccupied = !empty($occupiedMap[$num]);
                            ?>
                                <option value="<?= htmlspecialchars($num); ?>" <?= $isOccupied ? 'disabled' : ''; ?>><?= htmlspecialchars($num); ?> <?= $isOccupied ? ' (Ocupado)' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Valor do Aluguel (R$) <span class="required">*</span></label>
                        <input type="number" name="valor_aluguel" step="0.01" placeholder="Digite o valor do aluguel" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Contrato <span class="required">*</span></label>
                        <select name="tipo_contrato" required>
                            <option value="">Selecione</option>
                            <option value="residencial">Residencial</option>
                            <option value="comercial">Comercial</option>
                            <option value="misto">Misto</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Início <span class="required">*</span></label>
                        <input type="date" name="inicio" required>
                    </div>
                    <div class="form-group">
                        <label>Fim <span class="required">*</span></label>
                        <input type="date" name="fim" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Arquivo (nome/referência)</label>
                        <input type="text" name="arquivo" placeholder="Ex: contrato_2024_001.pdf">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Salvar Contrato</button>
                    <button type="button" class="btn-cancel" onclick="document.getElementById('formContrato').reset()"><i class="fas fa-times"></i> Limpar</button>
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

// Função para editar contrato
function editarContrato(contrato) {
    alert('Função de edição será implementada em breve!\n\nContrato ID: ' + contrato.id + '\nInquilino: ' + contrato.inquilino);
    // Aqui você pode implementar uma modal ou redirecionar para uma página de edição
}
</script>
</body>
</html>
