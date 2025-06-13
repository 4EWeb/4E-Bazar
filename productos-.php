<?php
require 'db.php';

// Validar parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de producto inválido');
}
$id = (int) $_GET['id'];

// Obtener producto
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) die('Producto no encontrado');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($p['nombre']) ?></title>
    <link rel="stylesheet" href="styles2.css">
    <style>
        .destacado-banner { background: #ff9800; color: #fff; padding: 5px; display: inline-block; margin-bottom: 10px; }
        .price-old { text-decoration: line-through; color: #888; }
    </style>
</head>
<body>
    <nav>
      <div class="nav-content">
        <a href="index.html" class="logo-emprendimiento">
          <img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" />
        </a>
        <ul class="menu-horizontal">
          <li><a href="index.html">Inicio</a></li>
          <li><a href="catalogo.html">Catálogo</a></li>
          <li><a href="nosotros.html">Sobre Nosotros</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
        <div class="carrito-box">
                <a href="carrito.html" class="carrito-menu" title="Carrito de compras">
                    <svg viewBox="0 0 576 512" width="32" height="32" fill="currentColor">
                        <path d="M528.12 301.319l47.273-208A16 16 0 0 0 560 80H120l-9.4-44.5A24 24 0 0 0 87 16H24A24 24 0 0 0 24 64h47.2l70.4 332.8A56 56 0 1 0 216 464h256a56 56 0 1 0 56-56H159.2l-7.2-32H528a16 16 0 0 0 15.12-12.681zM504 464a24 24 0 1 1-24-24 24 24 0 0 1 24 24zm-288 0a24 24 0 1 1-24-24 24 24 0 0 1 24 24z"/>
                    </svg>
                    <span id="contador-carrito" class="contador-carrito">0</span>
                </a>
            </div>
      </div>
    </nav>
    <div class="productos-container">
        <?php if ($p['destacado'] === 'si'): ?>
            <div class="destacado-banner">Producto Destacado</div>
        <?php endif; ?>
        <a class="producto-box">
            <h1><?= htmlspecialchars($p['nombre']) ?></h1>
            <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
            <?php if ($p['descuento'] > 0): ?>
                <?php $precio_final = $p['precio'] * (1 - $p['descuento'] / 100); ?>
                <p><span class="price-old">$<?= number_format($p['precio'],2) ?></span> $<?= number_format($precio_final,2) ?> (<?= $p['descuento'] ?>% off)</p>
            <?php else: ?>
                <p>Precio: $<?= number_format($p['precio'],2) ?></p>
            <?php endif; ?>
            <p>Disponibles: <?= $p['cantidad'] ?></p>
            <a href="add_to_cart.php?id=<?= $p['id'] ?>">Agregar al carrito</a>
            <br><br>
            <a href="catalogo.php">« Volver al catálogo</a>
        </a>    
    </div>
    <footer>
        <p>&copy; 2023 4E Bazar. Todos los derechos reservados.</p>
    
</body>
</html>