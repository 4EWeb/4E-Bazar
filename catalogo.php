<?php
require __DIR__ . '/db.php';


$categorias_fijas = [
  'papeleria', 'higiene', 'utiles', 'hogar', 'jugueteria', 'ropa', 'juegos', 'accesorios', 'fiesta'
];

$categoriaSeleccionada = $_GET['categoria'] ?? '';

try {
    if ($categoriaSeleccionada && in_array($categoriaSeleccionada, $categorias_fijas)) {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria = ?");
        $stmt->execute([$categoriaSeleccionada]);
    } else {
        $stmt = $pdo->query("SELECT * FROM productos");
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
</head>
<body>
    <nav>
      <div class="nav-content">
        <a href="index.html" class="logo-emprendimiento">
          <img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" />
        </a>
        <ul class="menu-horizontal">
          <li><a href="index.html">Inicio</a></li>
          <li>
            <a href="catalogo.html">Catálogo</a>
            
          </li>
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
    <form method="GET" action="catalog.php">
    <label for="categoria">Filtrar por categoría:</label>
    <select name="categoria" id="categoria" onchange="this.form.submit()">
      <option value="">Todos</option>
      <?php foreach ($categorias_fijas as $cat): ?>
        <option value="<?= $cat ?>" <?= ($categoriaSeleccionada === $cat) ? 'selected' : '' ?>>
          <?= ucfirst($cat) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <div class="productos-container">
    <?php if (count($productos) > 0): ?>
      <?php foreach ($productos as $producto): ?>
        <div class="producto-box">
          <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" style="width:100%;">
          <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
          <p><?= htmlspecialchars($producto['descripcion']) ?></p>
          <p>Precio: $<?= number_format($producto['precio'], 2) ?></p>
          <p>Categoría: <?= ucfirst($producto['categoria']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos disponibles en esta categoría.</p>
    <?php endif; ?>
  </div>
</body>
</html>