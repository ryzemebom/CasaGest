<div class="sidebar">
    <div class="sidebar-header">
        <h2>Apartment Manager</h2>
    </div>
    <link rel="stylesheet" href="css/responsive.css">
    <style>/* Sidebar */
body {
    display: flex;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background: #1d2b53ff;
    color: #fff;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    transition: width 0.3s;
    z-index: 100;
}

.sidebar-header {
    padding: 25px 20px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-menu {
    list-style: none;
    padding: 20px 0;
    flex: 1;
}

.sidebar-menu li {
    margin: 10px 0;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    border-radius: 8px;
    transition: 0.3s;
}

.sidebar-menu li a i {
    margin-right: 12px;
    min-width: 20px;
    text-align: center;
}

.sidebar-menu li a:hover {
    background: #0c2d8aff;
    color: #fff;
}

/* Conteúdo principal */
.main-content {
    margin-left: 250px;
    padding: 30px;
    width: 100%;
    transition: margin-left 0.3s;
}

/* Responsivo: sidebar colapsa em telas menores */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    .sidebar-header h2 {
        display: none;
    }
    .sidebar-menu li a span {
        display: none;
    }
    .main-content {
        margin-left: 70px;
    }
}
<?php
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
?>
</style>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="apartamentos.php"><i class="fa fa-building"></i> <span>Apartamentos</span></a></li>
        <li><a href="inquilinos.php"><i class="fa fa-users"></i> <span>Inquilinos</span></a></li>
        <li><a href="pagamentos.php"><i class="fa fa-dollar-sign"></i> <span>Pagamentos</span></a></li>
        <li><a href="contratos.php"><i class="fa fa-file-contract"></i> <span>Contratos</span></a></li>
        <li><a href="manutencoes.php"><i class="fa fa-tools"></i> <span>Manutenção</span></a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span>Sair</span></a></li>
    </ul>
</div>