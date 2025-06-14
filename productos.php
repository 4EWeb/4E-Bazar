<?php
require __DIR__ . '/db.php';

// Validar que el ID sea un número válido
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Si no es válido, podemos redirigir al catálogo
    header("Location: catalogo.php");
    exit;
}
$id = (int)$_GET['id'];

// Obtener la información del producto y su categoría
try {
    $stmt = $pdo->prepare('
        SELECT p.*, c.nombreCategoria 
        FROM productos p
        LEFT JOIN categorias c ON p.categoriaID = c.id
        WHERE p.id = ?
    ');
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
} catch (Exception $e) {
    // Es buena práctica manejar errores de base de datos
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}

// Si después de la consulta el producto no existe, mostramos un mensaje amigable
if (!$producto) {
    $producto_no_encontrado = true;
} else {
    // Calculamos el precio final para usarlo en el botón del carrito
    $precio_final_js = $producto['precio'];
    if (!empty($producto['descuento']) && $producto['descuento'] > 0) {
        $precio_final_js = $producto['precio'] * (1 - $producto['descuento'] / 100);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $producto ? htmlspecialchars($producto['nombre']) : 'Producto no encontrado' ?> - 4E Bazar</title>
    
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles2.css">
    <link rel="stylesheet" href="cart-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
      /* Estilos para la página de detalle del producto */
      .page-container { max-width: 1000px; margin: 50px auto; padding: 20px; }
      
      .product-detail-container {
        display: flex;
        flex-wrap: wrap; /* Para que se adapte en móviles */
        gap: 40px;
      }
      .product-image-column {
        flex: 1;
        min-width: 300px;
      }
      .product-image-column img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }
      .product-info-column {
        flex: 1.5;
        min-width: 300px;
      }
      .product-info-column h1 {
        font-size: 2.5rem;
        margin-top: 0;
        margin-bottom: 10px;
      }
      .product-info-column .descripcion {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
      }
      .price-box { margin: 20px 0; }
      .price-final { font-size: 2rem; font-weight: bold; color: #333; }
      .price-old { text-decoration: line-through; color: #888; margin-left: 15px; }
      .product-stock, .product-category { color: #777; margin: 10px 0; }
      
      .btn-add-to-cart {
        background-color: #e75480;
        color: white;
        border: none;
        padding: 15px 25px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
        font-size: 1.1rem;
        font-weight: bold;
        transition: background-color 0.2s;
      }
      .btn-add-to-cart:hover { background-color:rgb(238, 128, 161); }

      .back-link { display: inline-block; margin-top: 30px; color: #007bff; }
      .error-message { text-align: center; padding: 50px; }
    </style>
</head>
<body>
    <nav class="navbar-fijo">
      <div class="nav-content">
        <a href="index.php" class="logo-emprendimiento"><img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" /></a>
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
        <?php if (isset($producto_no_encontrado)): ?>
            <div class="error-message">
                <h1>Producto no encontrado</h1>
                <p>El producto que buscas no existe o fue eliminado.</p>
                <a href="catalogo.php" class="back-link">« Volver al catálogo</a>
            </div>
        <?php else: ?>
            <div class="product-detail-container">
                <div class="product-image-column">
                    <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                </div>
                <div class="product-info-column">
                    <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                    
                    <div class="price-box">
                        <span class="price-final">$<?= number_format($precio_final_js, 0, ',', '.') ?></span>
                        <?php if ($precio_final_js != $producto['precio']): ?>
                            <span class="price-old">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                    
                    <p class="product-stock"><strong>Disponibles:</strong> <?= (int)$producto['cantidad'] ?></p>
                    <p class="product-category"><strong>Categoría:</strong> <?= htmlspecialchars($producto['nombreCategoria']) ?></p>

                    <button 
                      class="btn-add-to-cart"
                      onclick="agregarAlCarrito({id: <?= $producto['id'] ?>, name: '<?= htmlspecialchars(addslashes($producto['nombre'])) ?>', price: <?= $precio_final_js ?>, image: '<?= htmlspecialchars($producto['imagen']) ?>'})">
                      Agregar al carrito
                    </button>
                </div>
            </div>
            <a href="catalogo.php" class="back-link">« Volver al catálogo</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> 4E Bazar. Todos los derechos reservados.</p>
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
</body>
</html>