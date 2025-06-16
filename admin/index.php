<?php
// admin/index.php
// require 'verificar_sesion.php'; // Agregaremos esto después
require '../db.php';
include 'header.php';

// --- Consultas para las estadísticas ---

// 1. Ingresos totales
$ingresos_totales = $pdo->query("SELECT SUM(monto_total) FROM pedidos")->fetchColumn();

// 2. Número total de pedidos
$total_pedidos = $pdo->query("SELECT COUNT(id) FROM pedidos")->fetchColumn();

// 3. Ítems más vendidos
$items_mas_vendidos = $pdo->query("
    SELECT producto_nombre, SUM(cantidad) as total_vendido
    FROM pedidos_items
    GROUP BY producto_nombre
    ORDER BY total_vendido DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 4. (NUEVO) Total de usuarios registrados
$total_usuarios = $pdo->query("SELECT COUNT(id) FROM usuarios")->fetchColumn();

// 5. (NUEVO) Ticket promedio
$ticket_promedio = $pdo->query("SELECT AVG(monto_total) FROM pedidos")->fetchColumn();

// 6. (NUEVO) Último usuario registrado
$ultimo_usuario = $pdo->query("SELECT nombre_usuario FROM usuarios ORDER BY fecha_registro DESC LIMIT 1")->fetchColumn();

?>

<h1>Dashboard de Estadísticas</h1>
<hr>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Ingresos Totales</div>
            <div class="card-body">
                <h3 class="card-title">$<?php echo number_format($ingresos_totales ?? 0, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Nº de Pedidos</div>
            <div class="card-body">
                <h3 class="card-title"><?php echo $total_pedidos ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Ticket Promedio</div>
            <div class="card-body">
                <h3 class="card-title">$<?php echo number_format($ticket_promedio ?? 0, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Usuarios Registrados</div>
            <div class="card-body">
                <h3 class="card-title"><?php echo $total_usuarios ?? 0; ?></h3>
                <p class="card-text mb-0">Último: <?php echo htmlspecialchars($ultimo_usuario ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<h3 class="mt-4">Top 5 Ítems Vendidos</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre del Ítem</th>
            <th>Unidades Vendidas</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items_mas_vendidos as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
            <td><?php echo $item['total_vendido']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>