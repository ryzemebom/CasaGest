<?php
require_once "functions.php";
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$apartamentos = readData("apartamentos");
$inquilinos = readData("inquilinos");
$pagamentos = readData("pagamentos");
$contratos = readData("contratos");
$manutencoes = readData("manutencoes");

// Cálculos de estatísticas
$total_apartamentos = count($apartamentos);
$apartamentos_ocupados = count(array_filter($apartamentos, fn($a) => $a["status"] == "ocupado"));
$apartamentos_disponiveis = $total_apartamentos - $apartamentos_ocupados;

$total_inquilinos = count($inquilinos);

$total_contratos = count($contratos);
$contratos_ativos = count(array_filter($contratos, fn($c) => ($c["status"] ?? "ativo") == "ativo"));
$contratos_vencidos = count(array_filter($contratos, fn($c) => strtotime($c["fim"]) < time() && ($c["status"] ?? "ativo") == "ativo"));

$total_pagamentos = count($pagamentos);
$pagamentos_pendentes = count(array_filter($pagamentos, fn($p) => $p["status"] == "pendente"));
$pagamentos_realizados = count(array_filter($pagamentos, fn($p) => $p["status"] == "pago"));

// Somar valor dos pagamentos realizados (receita)
$valor_pagamentos = 0;
foreach ($pagamentos as $p) {
    if ($p["status"] == "pago") {
        $valor_pagamentos += floatval($p["valor"] ?? 0);
    }
}

// Somar valor de aluguel total dos contratos
$valor_total_aluguel = 0;
foreach ($contratos as $c) {
    $valor_total_aluguel += floatval($c["valor_aluguel"] ?? 0);
}

// Manutenções
$total_manutencoes = count($manutencoes);
$manutencoes_pendentes = count(array_filter($manutencoes, fn($m) => $m["status"] == "pendente"));
$manutencoes_concluidas = count(array_filter($manutencoes, fn($m) => $m["status"] == "concluido"));

// Últimos pagamentos
$ultimos_pagamentos = array_slice(array_reverse($pagamentos), 0, 5);

// Últimas manutenções
$ultimas_manutencoes = array_slice(array_reverse($manutencoes), 0, 5);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Apartment Manager</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
        <p>Visão geral do seu sistema de gerenciamento de apartamentos</p>
    </div>

    <!-- Seção de Cards de Estatísticas Principais -->
    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-building"></i>
            <div class="stat-info">
                <h3><?= $total_apartamentos; ?></h3>
                <p>Total de Apartamentos</p>
                <small><?= $apartamentos_ocupados; ?> ocupados | <?= $apartamentos_disponiveis; ?> disponíveis</small>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-info">
                <h3><?= $total_inquilinos; ?></h3>
                <p>Inquilinos Ativos</p>
                <small>Cadastrados no sistema</small>
            </div>
        </div>
        <div class="stat-card success">
            <i class="fas fa-file-contract"></i>
            <div class="stat-info">
                <h3><?= $contratos_ativos; ?></h3>
                <p>Contratos Ativos</p>
                <small><?= $contratos_vencidos; ?> vencidos | Total: <?= $total_contratos; ?></small>
            </div>
        </div>
        <div class="stat-card warning">
            <i class="fas fa-money-bill"></i>
            <div class="stat-info">
                <h3>R$ <?= number_format($valor_pagamentos, 2, ",", "."); ?></h3>
                <p>Receita Total Recebida</p>
                <small><?= $pagamentos_realizados; ?> pagamentos | <?= $pagamentos_pendentes; ?> pendentes</small>
            </div>
        </div>
    </div>

    <!-- Seção de Pagamentos -->
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-receipt"></i> Pagamentos</h2>
                <a href="pagamentos.php" class="link-mais">Ver mais →</a>
            </div>
            <div class="stat-boxes">
                <div class="stat-box">
                    <div class="stat-number"><?= $pagamentos_realizados; ?></div>
                    <div class="stat-label">Pagamentos Realizados</div>
                    <div class="stat-value">R$ <?= number_format($valor_pagamentos, 2, ",", "."); ?></div>
                </div>
                <div class="stat-box alert">
                    <div class="stat-number"><?= $pagamentos_pendentes; ?></div>
                    <div class="stat-label">Pagamentos Pendentes</div>
                    <div class="stat-value">Atenção!</div>
                </div>
            </div>

            <!-- Últimos Pagamentos -->
            <?php if (!empty($ultimos_pagamentos)): ?>
            <div class="recent-list">
                <h3>Últimos Pagamentos</h3>
                <ul>
                    <?php foreach ($ultimos_pagamentos as $p): ?>
                    <li>
                        <div class="list-item">
                            <div class="list-info">
                                <strong><?= htmlspecialchars($p["inquilino"]); ?></strong>
                                <small>Apt <?= htmlspecialchars($p["apartamento"]); ?> - <?= date("d/m/Y", strtotime($p["data"])); ?></small>
                            </div>
                            <div class="list-value <?= $p["status"] == "pago" ? "text-success" : "text-warning"; ?>">
                                R$ <?= number_format($p["valor"] ?? 0, 2, ",", "."); ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Seção de Manutenções -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-tools"></i> Manutenções</h2>
                <a href="manutencoes.php" class="link-mais">Ver mais →</a>
            </div>
            <div class="stat-boxes">
                <div class="stat-box success">
                    <div class="stat-number"><?= $manutencoes_concluidas; ?></div>
                    <div class="stat-label">Concluídas</div>
                    <div class="stat-value">Finalizadas</div>
                </div>
                <div class="stat-box alert">
                    <div class="stat-number"><?= $manutencoes_pendentes; ?></div>
                    <div class="stat-label">Pendentes</div>
                    <div class="stat-value">A fazer</div>
                </div>
            </div>

            <!-- Últimas Manutenções -->
            <?php if (!empty($ultimas_manutencoes)): ?>
            <div class="recent-list">
                <h3>Últimas Manutenções</h3>
                <ul>
                    <?php foreach ($ultimas_manutencoes as $m): ?>
                    <li>
                        <div class="list-item">
                            <div class="list-info">
                                <strong>Apt <?= htmlspecialchars($m["apartamento"]); ?></strong>
                                <small><?= htmlspecialchars($m["descricao"]); ?></small>
                                <small class="date"><?= date("d/m/Y", strtotime($m["data"])); ?></small>
                            </div>
                            <div class="list-status <?= $m["status"] == "concluido" ? "status-success" : "status-warning"; ?>">
                                <?= ucfirst($m["status"]); ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Seção de Ações Rápidas 
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
        <div class="action-grid">
            <a href="apartamentos.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Novo Apartamento</span>
            </a>
            <a href="inquilinos.php" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Novo Inquilino</span>
            </a>
            <a href="contratos.php" class="action-btn">
                <i class="fas fa-file-signature"></i>
                <span>Novo Contrato</span>
            </a>
            <a href="pagamentos.php" class="action-btn">
                <i class="fas fa-coins"></i>
                <span>Registrar Pagamento</span>
            </a>
            <a href="manutencoes.php" class="action-btn">
                <i class="fas fa-wrench"></i>
                <span>Registrar Manutenção</span>
            </a>
            <a href="logout.php" class="action-btn logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </div>-->
    </div>
</body>
</html>
