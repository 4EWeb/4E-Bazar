<?php
// Iniciar sesión si no está ya iniciada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - 4e-Bazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <strong>Admin 4e-Bazar</strong>
            </a>
            
            <a href="index.php" class="navbar-brand">Estadísticas</a>
            <a href="gestionar_productos.php" class="navbar-brand"">Gestionar Variantes</a>
            <a href="agregar_producto.php" class="navbar-brand">Agregar Producto</a>
            <a href="gestionar_pedidos.php" class="navbar-brand"">Pedidos</a>
            <a href="gestionar_box.php"class="navbar-brand" >Promo Boxes</a>
            
            
            <?php if (isset($_SESSION['admin_id'])): // Muestra el botón de logout solo si hay sesión ?>
            <div class="d-flex">
                 <span class="navbar-text me-3 text-white">
                    Hola, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>
                </span>
                <a class="btn btn-outline-light" href="logout.php">Cerrar Sesión</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container mt-4"></main>