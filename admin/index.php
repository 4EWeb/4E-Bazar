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
<hr>
<div class="row">
    <div class="col-3"><div class="card text-white bg-success"><div class="card-header">Ingresos Totales</div><div class="card-body" style="color: #fff;"><h3 class="card-title">$<?php echo number_format($stats['ingresos_totales'] ?? 0, 0, ',', '.'); ?></h3></div></div></div>
    <div class="col-3"><div class="card text-white bg-info"><div class="card-header">Nº de Pedidos</div><div class="card-body" style="color: #000;"><h3 class="card-title"><?php echo $stats['total_pedidos'] ?? 0; ?></h3></div></div></div>
    <div class="col-3"><div class="card text-white bg-warning"><div class="card-header">Ticket Promedio</div><div class="card-body" style="color: #000;"><h3 class="card-title">$<?php echo number_format($stats['ticket_promedio'] ?? 0, 0, ',', '.'); ?></h3></div></div></div>
    <div class="col-3"><div class="card text-white bg-danger"><div class="card-header">Usuarios Registrados</div><div class="card-body" style="color: #fff;"><h3 class="card-title"><?php echo $stats['total_usuarios'] ?? 0; ?></h3><p class="card-text mb-0">Último: <?php echo htmlspecialchars($stats['ultimo_usuario'] ?? 'N/A'); ?></p></div></div></div>
</div>

<?php include 'footer.php'; ?>