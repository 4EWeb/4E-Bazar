<?php
require __DIR__ . '/db.php';


$categorias_fijas = [
  'papeleria', 'higiene', 'utiles', 'hogar', 'jugueteria', 'ropa', 'juegos', 'accesorios', 'fiesta'
];

$categoriaSeleccionada = $_GET['categoriaID'] ?? '';

try {
    if ($categoriaSeleccionada && in_array($categoriaSeleccionada, $categorias_fijas)) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombreCategoria 
            FROM productos p 
            INNER JOIN categorias c ON p.categoriaID = c.id 
            WHERE LOWER(c.nombreCategoria) = ?
        ");
        $stmt->execute([strtolower($categoriaSeleccionada)]);
    } else {
        $stmt = $pdo->query("
            SELECT p.*, c.nombreCategoria 
            FROM productos p 
            INNER JOIN categorias c ON p.categoriaID = c.id
        ");
    }

    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Error al cargar productos: " . $e->getMessage();
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
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
    <h1>Catálogo</h1>
    <div class="filtro">
      <form method="GET" action="catalog.php">
      <label for="categoriaID">Filtrar por categoría:</label>
      <select name="categoriaID" id="categoriaID" onchange="this.form.submit()">
        <option class="filtro-barra" value="">Todos</option>
        <?php foreach ($categorias_fijas as $cat): ?>
          <option class="filtro-select" value="<?= $cat ?>" <?= ($categoriaSeleccionada === $cat) ? 'selected' : '' ?>>
            <?= ucfirst($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>
      </form>
    </div>
  <div class="productos-container">
    <?php if (count($productos) > 0): ?>
      <?php foreach ($productos as $producto): ?>
        <a href="productos-.php?id=<?= $producto['id'] ?>" class="producto-box">
          <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" style="width:100%;">
          <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
          <p><?= htmlspecialchars($producto['descripcion']) ?></p>
          <?php if (!empty($producto['descuento']) && $producto['descuento'] > 0): ?>
            <?php $precio_final = $producto['precio'] * (1 - $producto['descuento'] / 100); ?>
            <p>
              <span class="price-old">$<?= number_format($producto['precio'], 2) ?></span>
              $<?= number_format($precio_final, 2) ?> (<?= $producto['descuento'] ?>% off)
            </p>
          <?php else: ?>
            <p>Precio: $<?= number_format($producto['precio'], 2) ?></p>
          <?php endif; ?>
          <p>Categoría: <?= ucfirst($producto['nombreCategoria']) ?></p>
          <p href="add_to_cart.php?id=<?= $p['id'] ?>">Agregar al carrito</p>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos disponibles en esta categoría.</p>
    <?php endif; ?>
  </div>
    <footer>
        <p>&copy; 2025 4E Bazar. Todos los derechos reservados.</p>
    </footer>
</body>
</html>