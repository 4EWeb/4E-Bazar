<?php
//require 'verificar_sesion.php';
require '../db.php';
include 'header.php';

// Consultas para las estadísticas
$ingresos_totales = $pdo->query("SELECT SUM(monto_total) FROM pedidos")->fetchColumn();
$total_pedidos = $pdo->query("SELECT COUNT(id_pedido) FROM pedidos")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(id) FROM usuarios")->fetchColumn();
$ticket_promedio = $pdo->query("SELECT AVG(monto_total) FROM pedidos")->fetchColumn();
$ultimo_usuario = $pdo->query("SELECT nombre_usuario FROM usuarios ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn();
?>

<h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>!</h1>
<p class="lead">Este es el resumen de actividad de tu tienda.</p>
<hr>
<div class="row">
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-success mb-3"><div class="card-header">Ingresos Totales</div><div class="card-body"><h3 class="card-title">$<?php echo number_format($ingresos_totales ?? 0, 0, ',', '.'); ?></h3></div></div></div>
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-info mb-3"><div class="card-header">Nº de Pedidos</div><div class="card-body"><h3 class="card-title"><?php echo $total_pedidos ?? 0; ?></h3></div></div></div>
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-warning mb-3"><div class="card-header">Ticket Promedio</div><div class="card-body"><h3 class="card-title">$<?php echo number_format($ticket_promedio ?? 0, 0, ',', '.'); ?></h3></div></div></div>
    <div class="col-lg-3 col-md-6"><div class="card text-white bg-danger mb-3"><div class="card-header">Usuarios Registrados</div><div class="card-body"><h3 class="card-title"><?php echo $total_usuarios ?? 0; ?></h3><p class="card-text mb-0">Último: <?php echo htmlspecialchars($ultimo_usuario ?? 'N/A'); ?></p></div></div></div>
</div>

<?php include 'footer.php'; ?>