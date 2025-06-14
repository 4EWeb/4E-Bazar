<?php
require __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("
        SELECT p.*, c.nombreCategoria 
        FROM productos p 
        INNER JOIN categorias c ON p.categoriaID = c.id
        ORDER BY p.nombre ASC
    ");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Error al cargar productos: " . $e->getMessage();
    exit;
}

$categorias_disponibles = [];
foreach ($productos as $producto) {
    $cat_slug = str_replace(' ', '-', strtolower($producto['nombreCategoria']));
    if (!isset($categorias_disponibles[$cat_slug])) {
        $categorias_disponibles[$cat_slug] = $producto['nombreCategoria'];
    }
}
ksort($categorias_disponibles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="cart-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
      .page-container {
        max-width: 1200px;
        margin: 50px auto 20px auto; 
        padding: 0 20px;
      }
      
      /* MODIFICADO: Se elimina .producto-link-wrapper y se aplican los estilos de layout a .producto-box */
      .producto-box {
        display: flex;
        flex-direction: column;
        flex-basis: 300px; /* Ancho base de la tarjeta */
        flex-grow: 1;      /* Permite que la tarjeta crezca si hay espacio */
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      .producto-box a {
          display: block; /* Asegura que el enlace de la imagen ocupe su espacio */
          margin-bottom: 15px;
      }
      .producto-box img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        transition: transform 0.3s ease; /* Efecto suave al pasar el mouse */
      }
       .producto-box a:hover img {
        transform: scale(1.03); /* Agranda la imagen ligeramente */
      }
      
      .price-old { text-decoration: line-through; color: #888; margin-left: 5px; }
      .btn-add-to-cart {
        background-color: #e75480;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 15px;
        width: 100%;
        font-size: 1rem;
        transition: background-color 0.2s;
      }
      .btn-add-to-cart:hover {
        background-color:rgb(238, 128, 161);
      }
      .productos-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
      }
      .producto-descripcion {
        font-size: 0.9rem;
        color: #666;
        flex-grow: 1; /* Esto empuja el contenido de abajo (precio, categoría, botón) hacia el final */
        margin: 10px 0;
      }
      .producto-categoria {
        font-size: 0.8rem;
        color: #999;
        margin-top: 10px;
        text-align: left;
        font-style: italic;
      }
    </style>  
</head>
<body>
    <nav class="navbar-fijo">
      <div class="nav-content">
        <a href="index.php" class="logo-emprendimiento">
          <img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" />
        </a>
        <ul class="menu-horizontal">
          <li><a href="index.php">Inicio</a></li>
          <li><a href="catalogo.php">Catálogo</a></li>
          <li><a href="nosotros.html">Sobre Nosotros</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
        <div class="carrito-box">
          <button class="carrito-menu" id="cart-icon-btn" title="Ver carrito de compras">
            <svg viewBox="0 0 576 512" width="32" height="32" fill="currentColor"><path d="M528.12 301.319l47.273-208A16 16 0 0 0 560 80H120l-9.4-44.5A24 24 0 0 0 87 16H24A24 24 0 0 0 24 64h47.2l70.4 332.8A56 56 0 1 0 216 464h256a56 56 0 1 0 56-56H159.2l-7.2-32H528a16 16 0 0 0 15.12-12.681zM504 464a24 24 0 1 1-24-24 24 24 0 0 1 24 24zm-288 0a24 24 0 1 1-24-24 24 24 0 0 1 24 24z"/></svg>
            <span id="contador-carrito" class="contador-carrito">0</span>
          </button>
        </div>
      </div>
    </nav>
    
    <main class="page-container">

        <h1>Catálogo</h1>

        <div class="filtro-barra">
            <label for="filtro-categorias" style="font-weight:bold; margin-right:8px;">Filtrar por categoría:</label>
            <select id="filtro-categorias" class="filtro-select" onchange="filtrarCategoria(this.value)">
                <option value="todos">Todos</option>
                <?php foreach ($categorias_disponibles as $slug => $nombre_real): ?>
                  <option value="<?= $slug ?>"><?= ucfirst($nombre_real) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

      <div class="productos-container">
        <?php if (count($productos) > 0): ?>
          <?php foreach ($productos as $producto): ?>
            <?php
              $categoria_clase = str_replace(' ', '-', strtolower($producto['nombreCategoria']));
              $precio_final_js = $producto['precio'];
              if (!empty($producto['descuento']) && $producto['descuento'] > 0) {
                  $precio_final_js = $producto['precio'] * (1 - $producto['descuento'] / 100);
              }
            ?>
            
            <div class="producto-box <?= htmlspecialchars($categoria_clase) ?>">
                <a href="productos.php?id=<?= $producto['id'] ?>">
                    <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                </a>

                <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                <p class="producto-descripcion"><?= htmlspecialchars($producto['descripcion']) ?></p>
                
                <p class="producto-precio">$<?= number_format($precio_final_js, 0, ',', '.') ?>
                    <?php if ($precio_final_js != $producto['precio']): ?>
                        <span class="price-old">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                    <?php endif; ?>
                </p>

                <p class="producto-categoria">Categoría: <?= htmlspecialchars(ucfirst($producto['nombreCategoria'])) ?></p>
                
                <button 
                  class="btn-add-to-cart"
                  onclick="agregarAlCarrito({id: <?= $producto['id'] ?>, name: '<?= htmlspecialchars(addslashes($producto['nombre'])) ?>', price: <?= $precio_final_js ?>, image: '<?= htmlspecialchars($producto['imagen']) ?>'})">
                  Agregar al carrito
                </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay productos disponibles.</p>
        <?php endif; ?>
      </div>

    </main>

    <footer>
        <p>&copy; 2025 4E Bazar. Todos los derechos reservados.</p>
    </footer>

    <aside class="cart-sidebar">
      <div class="cart-header"><h3>Tu Carrito</h3><button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button></div>
      <div class="cart-body"><p class="cart-empty-msg">Tu carrito está vacío.</p></div>
      <div class="cart-footer">
        <div class="cart-total"><strong>Total:</strong><span id="cart-total-price">$0</span></div>
        <button class="btn-checkout" id="btn-finalize-purchase"><i class="fab fa-whatsapp"></i> Pedir por WhatsApp</button>
      </div>
    </aside>
    <div class="cart-overlay"></div>

    <script src="carrito.js"></script>
    <script>
      function filtrarCategoria(categoriaSeleccionada) {
        document.querySelectorAll('.producto-box').forEach(producto => {
            if (categoriaSeleccionada === 'todos' || producto.classList.contains(categoriaSeleccionada)) {
                producto.style.display = 'flex';
            } else {
                producto.style.display = 'none';
            }
        });
      }
    </script>
</body>
</html>