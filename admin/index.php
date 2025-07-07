<?php
require 'admin_functions.php';
include 'header.php';

// --- OBTENER TODOS LOS DATOS PARA EL DASHBOARD ---
$stats = get_dashboard_stats($pdo);
$best_selling_products = get_best_selling_products($pdo);
$top_customer = get_top_customer($pdo);
$frequent_pairs = get_frequently_bought_together($pdo);

// ¡AQUÍ ESTÁ LA LÍNEA QUE FALTABA!
// Llamamos a la nueva función para obtener las categorías más rentables.
$top_earning_categories = get_top_earning_categories($pdo); 

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
            <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-info">
            <div class="card-content">
                <h3><?php echo $stats['total_pedidos'] ?? 0; ?></h3>
                <p>Nº de Pedidos</p>
            </div>
            <div class="card-icon"><i class="fas fa-shopping-bag"></i></div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-warning">
            <div class="card-content">
                <h3>$<?php echo number_format($stats['ticket_promedio'] ?? 0, 0, ',', '.'); ?></h3>
                <p>Ticket Promedio</p>
            </div>
            <div class="card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="dashboard-card-col">
        <div class="dashboard-card bg-danger">
            <div class="card-content">
                <h3><?php echo $stats['total_usuarios'] ?? 0; ?></h3>
                <p>Usuarios Registrados</p>
            </div>
            <div class="card-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
</div>

<div class="dashboard-list-grid">
    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-trophy"></i> Productos Más Vendidos</h4>
        </div>
        <div class="card-body">
            <?php if (empty($best_selling_products)): ?>
                <p class="text-muted">No hay datos suficientes.</p>
            <?php else: ?>
                <ul class="dashboard-list">
                    <?php foreach ($best_selling_products as $product): ?>
                        <li>
                            <span><?php echo htmlspecialchars($product['nombre']); ?></span>
                            <span class="badge"><?php echo $product['total_vendido']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-user-crown"></i> Cliente Más Frecuente</h4>
        </div>
        <div class="card-body">
            <?php if (empty($top_customer)): ?>
                <p class="text-muted">No hay datos suficientes.</p>
            <?php else: ?>
                <div class="top-item">
                    <i class="fas fa-medal"></i>
                    <p class="top-item-name"><?php echo htmlspecialchars($top_customer['nombre_usuario']); ?></p>
                    <p class="top-item-detail"><?php echo $top_customer['total_pedidos']; ?> pedidos</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-people-arrows"></i> Comprados Juntos Frecuentemente</h4>
        </div>
        <div class="card-body">
            <?php if (empty($frequent_pairs)): ?>
                <p class="text-muted">No hay datos suficientes.</p>
            <?php else: ?>
                <ul class="dashboard-list">
                    <?php foreach ($frequent_pairs as $pair): ?>
                        <li>
                            <span><?php echo htmlspecialchars($pair['producto1'] . ' + ' . $pair['producto2']); ?></span>
                            <span class="badge"><?php echo $pair['veces_juntos']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-chart-pie"></i> Top Categorías por Ingresos</h4>
        </div>
        <div class="card-body">
            <?php if (empty($top_earning_categories)): ?>
                <p class="text-muted">No hay datos suficientes.</p>
            <?php else: ?>
                <ul class="dashboard-list">
                    <?php foreach ($top_earning_categories as $category): ?>
                        <li>
                            <span><?php echo htmlspecialchars($category['nombre_categoria']); ?></span>
                            <span class="badge">$<?php echo number_format($category['total_ingresos'], 0, ',', '.'); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>