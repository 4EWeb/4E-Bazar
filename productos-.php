<?php
require 'db.php';

// Validar parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de producto inválido');
}
$id = (int) $_GET['id'];

// Obtener producto
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) die('Producto no encontrado');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($p['nombre']) ?></title>
    <style>
        .destacado-banner { background: #ff9800; color: #fff; padding: 5px; display: inline-block; margin-bottom: 10px; }
        .price-old { text-decoration: line-through; color: #888; }
    </style>
</head>
<body>
    <?php if ($p['destacado'] === 'si'): ?>
        <div class="destacado-banner">Producto Destacado</div>
    <?php endif; ?>
    <h1><?= htmlspecialchars($p['nombre']) ?></h1>
    <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
    <p><?= nl2br(htmlspecialchars($p['descripción'])) ?></p>
    <?php if ($p['descuento'] > 0): ?>
        <?php $precio_final = $p['precio'] * (1 - $p['descuento'] / 100); ?>
        <p><span class="price-old">$<?= number_format($p['precio'],2) ?></span> $<?= number_format($precio_final,2) ?> (<?= $p['descuento'] ?>% off)</p>
    <?php else: ?>
        <p>Precio: $<?= number_format($p['precio'],2) ?></p>
    <?php endif; ?>
    <p>Disponibles: <?= $p['cantidad'] ?></p>
    <a href="add_to_cart.php?id=<?= $p['id'] ?>">Agregar al carrito</a>
    <br><br>
    <a href="catalog.php">« Volver al catálogo</a>
</body>
</html>