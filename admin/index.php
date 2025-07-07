<?php
require 'admin_functions.php';
include 'header.php';

// Obtener datos para el dashboard
$stats = get_dashboard_stats($pdo);
?>

<div class="page-header">
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin'); ?>!</h1>
    <p>Este es el resumen de actividad de tu tienda.</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-success">
            <div class="card-content">
                <h3>$<?php echo number_format($stats['ingresos_totales'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Ingresos Totales</p>
            </div>
            <div class="card-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-info">
            <div class="card-content">
                <h3><?php echo $stats['total_pedidos'] ?? 0; ?></h3>
                <p>Nº de Pedidos</p>
            </div>
            <div class="card-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-warning">
            <div class="card-content">
                <h3>$<?php echo number_format($stats['ticket_promedio'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Ticket Promedio</p>
            </div>
            <div class="card-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-danger">
            <div class="card-content">
                <h3><?php echo $stats['total_usuarios'] ?? 0; ?></h3>
                <p>Usuarios Registrados</p>
            </div>
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>