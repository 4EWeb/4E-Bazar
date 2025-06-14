<?php
require __DIR__ . '/db.php'; // Incluimos la conexión a la base de datos

try {
    // Consulta para obtener 3 productos marcados como destacados.
    // RECOMENDACIÓN: Añade una columna 'es_destacado' (TINYINT) a tu tabla 'productos'.
    $stmt = $pdo->query("
        SELECT p.*, c.nombreCategoria 
        FROM productos p
        LEFT JOIN categorias c ON p.categoriaID = c.id
        WHERE p.es_destacado = 1 
        LIMIT 3
    ");

    /* --- ALTERNATIVA SI NO QUIERES USAR 'es_destacado' ---
       Descomenta la siguiente línea y comenta la de arriba para mostrar los 3 productos más nuevos.
    
    $stmt = $pdo->query("
        SELECT p.*, c.nombreCategoria 
        FROM productos p
        LEFT JOIN categorias c ON p.categoriaID = c.id
        ORDER BY p.id DESC 
        LIMIT 3
    ");
    */

    $productos_destacados = $stmt->fetchAll();

} catch (Exception $e) {
    // Manejo de error si la consulta falla
    echo "Error al cargar productos destacados: " . $e->getMessage();
    $productos_destacados = []; // Asegurarse de que la variable exista para evitar errores en el HTML
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>4E Bazar - Inicio</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="cart-styles.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    />
    <style>
      .price-old { text-decoration: line-through; color: #888; margin-left: 5px; }
      .btn-add-to-cart {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        width: 100%;
        font-size: 1rem;
        transition: background-color 0.2s;
      }
      .btn-add-to-cart:hover {
        background-color: #0056b3;
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
        <div class="login-box">
          <button class="login-menu">
            <img src="Imagenes/icono-login.png" alt="Login" width="32" height="32" />
          </button>
          <ul class="submenu">
            <li><a href="#">Mi cuenta</a></li>
            <li><a href="#">Pedidos</a></li>
            <li><a href="#">Cerrar sesión</a></li>
          </ul>
        </div>
        <div class="carrito-box">
          <button
            class="carrito-menu"
            id="cart-icon-btn"
            title="Ver carrito de compras"
          >
            <svg viewBox="0 0 576 512" width="32" height="32" fill="currentColor">
              <path
                d="M528.12 301.319l47.273-208A16 16 0 0 0 560 80H120l-9.4-44.5A24 24 0 0 0 87 16H24A24 24 0 0 0 24 64h47.2l70.4 332.8A56 56 0 1 0 216 464h256a56 56 0 1 0 56-56H159.2l-7.2-32H528a16 16 0 0 0 15.12-12.681zM504 464a24 24 0 1 1-24-24 24 24 0 0 1 24 24zm-288 0a24 24 0 1 1-24-24 24 24 0 0 1 24 24z"
              />
            </svg>
            <span id="contador-carrito" class="contador-carrito">0</span>
          </button>
        </div>
      </div>
    </nav>

    <div class="contenido-scroll">
       <section class="ofertas-box">
        <h1 class="ofertas-title">Ofertas Destacadas</h1>
        <div class="galeria-fotos">
          <div style="background-image: url('Imagenes/oferta1.png')"></div>
          <div style="background-image: url('Imagenes/oferta2.png')"></div>
          <div style="background-image: url('Imagenes/oferta3.png')"></div>
          <div style="background-image: url('Imagenes/oferta4.png')"></div>
        </div>
      </section>
      
      <section class="productos-destacados">
        <h2 class="productos-title">Productos Destacados</h2>
        <div class="productos-container">
            <?php if (count($productos_destacados) > 0): ?>
                <?php foreach ($productos_destacados as $producto): ?>
                    <div class="producto-box">
                        <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" />
                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>

                        <?php
                            // Lógica de precios para mostrar y para el carrito
                            $precio_final_js = $producto['precio'];
                            if (!empty($producto['descuento']) && $producto['descuento'] > 0) {
                                $precio_final_js = $producto['precio'] * (1 - $producto['descuento'] / 100);
                            }
                        ?>
                        
                        <div class="price-info">
                            <?php if ($precio_final_js != $producto['precio']): ?>
                                <p>
                                    $<?= number_format($precio_final_js, 0, ',', '.') ?>
                                    <span class="price-old">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                                </p>
                            <?php else: ?>
                                <p>$<?= number_format($producto['precio'], 0, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <button
                          class="btn-add-to-cart"
                          onclick="agregarAlCarrito({id: <?= $producto['id'] ?>, name: '<?= htmlspecialchars(addslashes($producto['nombre'])) ?>', price: <?= $precio_final_js ?>, image: '<?= htmlspecialchars($producto['imagen']) ?>'})"
                        >
                          Agregar al carrito
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay productos destacados disponibles en este momento.</p>
            <?php endif; ?>
        </div>
      </section>
      
      <section class="servicios-graficos-index">
        <h1 class="ofertas-title">Servicios gráficos</h1>
        <div class="Servicios-box-index">
          <p class="Servicios-Title-index">
            Todas las impresiones, anillados y plastificados lo encuentras aquí
          </p>
          <a href="servicios.html" class="btn-servicios-index">Ver servicios</a>
        </div>
      </section>

      <footer>
        <div class="social-panel">
          <h2 class="texto-blanco">Síguenos en redes sociales</h2>
          <div class="social-icons-container">
            <div class="iconos-horizontales">
              <a href="https://www.instagram.com/bazarycomercial.4e/" class="social-icon instagram" target="_blank" title="Instagram">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
              </a>
              <a href="https://api.whatsapp.com/send/?phone=56976509490&text&type=phone_number&app_absent=0" class="social-icon whatsapp" target="_blank" title="WhatsApp">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>

    <aside class="cart-sidebar">
      <div class="cart-header">
        <h3>Tu Carrito</h3>
        <button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button>
      </div>
      <div class="cart-body">
        <p class="cart-empty-msg">Tu carrito está vacío.</p>
      </div>
      <div class="cart-footer">
        <div class="cart-total">
          <strong>Total:</strong>
          <span id="cart-total-price">$0</span>
        </div>
        <button class="btn-checkout" id="btn-finalize-purchase">
            <i class="fab fa-whatsapp"></i> Pedir por WhatsApp
        </button>
      </div>
    </aside>
    <div class="cart-overlay"></div>

    <script src="transiciones.js"></script>
    <script src="carrito.js"></script>
  </body>
</html>