<?php
// admin/header.php
// Este es el archivo final del header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="index.php">Estadísticas</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestionar_productos.php">Gestionar Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="agregar_producto.php">Agregar Producto</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestionar_pedidos.php">Pedidos</a></li>
                </ul>
                <ul class="navbar-nav">
                     <li class="nav-item"><a class="nav-link" href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mt-4">