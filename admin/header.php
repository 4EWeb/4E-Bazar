<?php
// admin/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="css_admin/admin_styles.css">
</head>
<body>

<nav class="main-nav">
    <div class="container">
        <a class="nav-brand" href="index.php">Panel Admin</a>
        <button class="nav-toggler" id="nav-toggler-btn">
            <span class="nav-toggler-icon"></span>
        </button>
        <div class="nav-collapse" id="navbarAdmin">
            <ul class="nav-menu nav-menu-main">
                <li><a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="index.php">Estadísticas</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'gestionar_productos.php') ? 'active' : ''; ?>" href="gestionar_productos.php">Productos</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'gestionar_catalogos.php') ? 'active' : ''; ?>" href="gestionar_catalogos.php">Catálogos</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'gestionar_pedidos.php') ? 'active' : ''; ?>" href="gestionar_pedidos.php">Pedidos</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'gestionar_box.php') ? 'active' : ''; ?>" href="gestionar_box.php">Promo Boxes</a></li>
            </ul>
            <ul class="nav-menu">
                <li><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</nav>