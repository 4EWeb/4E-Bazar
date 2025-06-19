<?php
// Iniciamos la sesión para poder cerrarla después.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Esto detecta en qué página estamos para resaltar el enlace correcto.
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../layout.css">
    <link rel="stylesheet" href="../components.css">
    <link rel="stylesheet" href="../kits.css">
    <link rel="stylesheet" href="css/admin_styles.css">
    <style>
        body { background: #f8f9fa; } /* Un fondo neutro para el panel */
        .card { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Panel Admin</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="index.php">Estadísticas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'gestionar_productos.php' || $currentPage == 'editar_producto.php') ? 'active' : ''; ?>" href="gestionar_productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'gestionar_catalogos.php') ? 'active' : ''; ?>" href="gestionar_catalogos.php">Catálogos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'gestionar_pedidos.php') ? 'active' : ''; ?>" href="gestionar_pedidos.php">Pedidos</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'gestionar_box.php') ? 'active' : ''; ?>" href="gestionar_box.php">Promo Boxes</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">