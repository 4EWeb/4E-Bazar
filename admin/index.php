<?php
require 'admin_functions.php';
include 'header.php';

// --- LÓGICA CORREGIDA PARA MANEJAR PROVEEDORES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Añadir/Actualizar nuevo proveedor
    if (isset($_POST['add_supplier'])) {
        // Recoger datos del formulario de forma segura
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $correo = $_POST['correo'] ?? '';

        if (!empty($nombre)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, telefono, correo) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $telefono, $correo]);
                $_SESSION['message'] = 'Proveedor añadido correctamente.';
            } catch (PDOException $e) {
                // Si hay un error de base de datos, lo mostramos
                $_SESSION['error_message'] = 'Error al añadir el proveedor: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = 'El nombre del proveedor no puede estar vacío.';
        }
        header("Location: index.php");
        exit();
    }

    // Eliminar proveedor (esta parte ya estaba bien, pero la incluyo por completitud)
    if (isset($_POST['delete_supplier'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = ?");
            $stmt->execute([$_POST['id_proveedor']]);
            $_SESSION['message'] = 'Proveedor eliminado.';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error al eliminar el proveedor: ' . $e->getMessage();
        }
        header("Location: index.php");
        exit();
    }
}


// --- OBTENER TODOS LOS DATOS PARA EL DASHBOARD ---
$stats = get_dashboard_stats($pdo);
$best_selling_products = get_best_selling_products($pdo);
$top_customer = get_top_customer($pdo);
$frequent_pairs = get_frequently_bought_together($pdo);
$top_earning_categories = get_top_earning_categories($pdo); 
$low_stock_products = get_low_stock_products($pdo, 10);
$suppliers = get_all_suppliers($pdo);
?>

<div class="page-header">
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin'); ?>!</h1>
    <p>Este es el resumen de actividad de tu tienda.</p>
</div>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>


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

    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-exclamation-triangle text-warning"></i> Productos con Poco Stock</h4>
        </div>
        <div class="card-body">
            <?php if (empty($low_stock_products)): ?>
                <p class="text-muted">¡Genial! No hay productos con bajo stock.</p>
            <?php else: ?>
                <ul class="dashboard-list">
                    <?php foreach ($low_stock_products as $product): ?>
                        <li>
                            <span><?php echo htmlspecialchars($product['nombre'] . ' (' . $product['sku'] . ')'); ?></span>
                            <span class="badge bg-warning text-dark">Quedan: <?php echo $product['stock']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-list-card">
        <div class="card-header">
            <h4><i class="fas fa-truck-loading"></i> Gestionar Proveedores</h4>
        </div>
        <div class="card-body">
            <?php if (empty($suppliers)): ?>
                <p class="text-muted">Aún no has añadido proveedores.</p>
            <?php else: ?>
                <ul class="dashboard-list supplier-list">
                    <?php foreach ($suppliers as $supplier): ?>
                        <li>
                            <div class="supplier-info">
                                <strong><?php echo htmlspecialchars($supplier['nombre']); ?></strong>
                                <small><?php echo htmlspecialchars($supplier['telefono'] . ' | ' . $supplier['correo']); ?></small>
                            </div>
                            <form action="index.php" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este proveedor?');">
                                <input type="hidden" name="id_proveedor" value="<?php echo $supplier['id']; ?>">
                                <button type="submit" name="delete_supplier" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <form action="index.php" method="POST" class="supplier-form">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre Proveedor" required>
                <input type="text" name="telefono" class="form-control" placeholder="Teléfono">
                <input type="email" name="correo" class="form-control" placeholder="Correo">
                <button type="submit" name="add_supplier" class="btn btn-primary">Añadir</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>