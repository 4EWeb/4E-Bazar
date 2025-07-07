<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - 4E Bazar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="css_admin/admin_styles.css">
</head>
<body>
<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>4E Bazar</h3>
            <span>Panel Admin</span>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line fa-fw"></i> Estadísticas</a></li>
                <li><a href="gestionar_productos.php" class="<?= ($currentPage == 'gestionar_productos.php') ? 'active' : ''; ?>"><i class="fas fa-box-open fa-fw"></i> Productos</a></li>
                <li><a href="gestionar_catalogos.php" class="<?= ($currentPage == 'gestionar_catalogos.php') ? 'active' : ''; ?>"><i class="fas fa-book-open fa-fw"></i> Catálogos</a></li>
                <li><a href="gestionar_pedidos.php" class="<?= ($currentPage == 'gestionar_pedidos.php') ? 'active' : ''; ?>"><i class="fas fa-dolly fa-fw"></i> Pedidos</a></li>
                <li><a href="gestionar_box.php" class="<?= ($currentPage == 'gestionar_box.php') ? 'active' : ''; ?>"><i class="fas fa-gift fa-fw"></i> Promo Boxes</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Cerrar Sesión</a>
        </div>
    </aside>
    <main class="main-content">