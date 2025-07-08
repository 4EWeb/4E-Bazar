<?php
session_start();
require __DIR__ . '/db.php';

// --- LÓGICA PARA OFERTAS DESTACADAS (PRODUCTOS CON DESCUENTO) ---
$ofertas_destacadas = [];
try {
    // Esta consulta busca variantes con el mayor descuento que tengan stock.
    $stmt_ofertas = $pdo->query("
        SELECT
            p.id AS id_producto_base,
            p.nombre,
            v.id_variante,
            v.precio,
            v.descuento,
            COALESCE(v.imagen, p.imagen_principal) AS imagen_a_mostrar
        FROM variantes_producto v
        JOIN productos p ON v.id_producto = p.id
        WHERE v.descuento > 0 AND v.stock > 0 AND p.activo = 1
        ORDER BY v.descuento DESC
        LIMIT 4
    ");
    $ofertas_destacadas = $stmt_ofertas->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "Error al cargar ofertas destacadas: " . $e->getMessage();
}


// --- LÓGICA PARA PRODUCTOS DESTACADOS (LOS QUE MARCAS COMO DESTACADO) ---
$productos_destacados = [];
try {
    $stmt = $pdo->query("
        SELECT
            p.id AS id_producto_base, p.nombre, v.id_variante, v.precio, v.descuento,
            COALESCE(v.imagen, p.imagen_principal) AS imagen_a_mostrar
        FROM variantes_producto v
        JOIN productos p ON v.id_producto = p.id
        WHERE v.destacado = 1
    ");
    $productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "Error al cargar productos destacados: " . $e->getMessage();
}
$kits_procesados = [];
require __DIR__ . '/kits.php'; // Incluimos la lógica de kits después de cargar los productos

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>4E Bazar - Inicio</title>
    
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="css/kits.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
        .price-old { text-decoration: line-through; color: #888; margin-left: 5px; }
        .producto-box .btn-add-to-cart {
            background-color: #e75480;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: auto;
            width: 100%;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .producto-box .btn-add-to-cart:hover {
            background-color:rgb(238, 128, 161);
        }
    </style>
  </head>
  <body>
    
    <?php include 'nav.php';?>

    <div class="contenido-scroll">
      <section class="ofertas-box">
        <h1 class="ofertas-title">Ofertas Destacadas</h1>
        <div class="productos-container">
            <?php if (count($ofertas_destacadas) > 0): ?>
                <?php foreach ($ofertas_destacadas as $producto): ?>
                    <div class="producto-box">
                        <a href="productos.php?id=<?= $producto['id_producto_base'] ?>">
                            <img src="<?= htmlspecialchars($producto['imagen_a_mostrar'] ?: 'Imagenes/placeholder.png') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" />
                        </a>
                        
                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                        
                        <?php
                            $precio_final = $producto['precio'];
                            if (!empty($producto['descuento']) && $producto['descuento'] > 0) {
                                $precio_final = $producto['precio'] * (1 - $producto['descuento'] / 100);
                            }
                        ?>
                        <div class="price-info">
                            <p>$<?= number_format($precio_final, 0, ',', '.') ?>
                                <?php if ($precio_final != $producto['precio']): ?>
                                    <span class="price-old">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <button class="btn-add-to-cart" 
                                onclick="agregarAlCarrito({
                                    id: <?= $producto['id_variante'] ?>, 
                                    name: '<?= htmlspecialchars(addslashes($producto['nombre'])) ?>', 
                                    price: <?= $precio_final ?>, 
                                    image: '<?= htmlspecialchars($producto['imagen_a_mostrar'] ?: 'Imagenes/placeholder.png') ?>'
                                })">
                          Agregar al carrito
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay ofertas destacadas disponibles en este momento.</p>
            <?php endif; ?>
        </div>
      </section>

        <section class="ofertas-box">
        <h2 class="ofertas-title">Productos Destacados</h2>
        
        <div class="carousel-container">
            <button class="carousel-btn prev" aria-label="Anterior">&lt;</button>
            <div class="carousel-viewport">
                <div class="carousel-track">
                    <?php if (count($productos_destacados) > 0): ?>
                        <?php foreach ($productos_destacados as $producto): ?>
                            <div class="producto-box">
                                <a href="productos.php?id=<?= $producto['id_producto_base'] ?>">
                                    <img src="<?= htmlspecialchars($producto['imagen_a_mostrar'] ?: 'Imagenes/placeholder.png') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" />
                                </a>
                                <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                <?php
                                    $precio_final = $producto['precio'] * (1 - ($producto['descuento'] ?? 0) / 100);
                                ?>
                                <p class="producto-precio">$<?= number_format($precio_final, 0, ',', '.') ?></p>
                                <button class="btn-add-to-cart" onclick="agregarAlCarrito({id: <?= $producto['id_variante'] ?>, name: '<?= htmlspecialchars(addslashes($producto['nombre'])) ?>', price: <?= $precio_final ?>, image: '<?= htmlspecialchars($producto['imagen_a_mostrar'] ?: 'Imagenes/placeholder.png') ?>'})">
                                  Agregar al carrito
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay productos destacados para mostrar.</p>
                    <?php endif; ?>
                </div>
            </div>
            <button class="carousel-btn next" aria-label="Siguiente">&gt;</button>
        </div>
    </section>

      <section class="kits-section">
            <h2 class="productos-title">Nuestros Kits Ahorro</h2>
            <div class="kits-container">
                <?php if (count($kits_procesados) > 0): ?>
                    <?php foreach ($kits_procesados as $kit): ?>
                        <div class="kit-box">
                            <h3><?= htmlspecialchars($kit['nombre']) ?></h3>
                            <ul class="product-list">
                                <?php foreach ($kit['nombres_productos'] as $item): ?>
                                    <li>
                                        <?= htmlspecialchars($item['nombre_producto']) ?> (x<?= htmlspecialchars($item['cantidad']) ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="kit-footer">
                                <div class="kit-price">
                                    <span class="kit-price-label">Precio Total del Kit</span>
                                    $<?= number_format($kit['precio_total'], 0, ',', '.') ?>
                                </div>
                                <div class="kit-price">
                                    <span class="price-old" style="font-size: 1rem; color: #888;">
                                        Valor real: $<?= number_format($kit['valor_real'], 0, ',', '.') ?>
                                    </span>
                                    $<?= number_format($kit['precio_total'], 0, ',', '.') ?>
                                </div>
                                <button class="btn-add-kit-to-cart" 
                                        onclick="agregarAlCarrito({
                                            id: 'promo-<?= $kit['id_promo'] ?>',
                                            name: '<?= htmlspecialchars(addslashes($kit['nombre'])) ?>',
                                            price: <?= $kit['precio_total'] ?>,
                                            image: 'Imagenes/4e logo actualizado.png',
                                            tipo: 'promo'
                                        })">
                                    Agregar Kit al Carrito
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay kits disponibles en este momento.</p>
                <?php endif; ?>
            </div>
      </section>

      
      <section class="servicios-graficos-index">
        <h1 class="ofertas-title">Servicios gráficos</h1>
        <div class="Servicios-box-index">
          <p class="Servicios-Title-index">
            Todas las impresiones, anillados y plastificados lo encuentras aquí
          </p>
          <a href="servicios.php" class="btn-servicios-index">Ver servicios</a>
        </div>
      </section>
      <section class="features-section">
            <h2 class="productos-title">¿Por Qué Comprar con Nosotros?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-box-open"></i>
                    <h3>Variedad de Productos</h3>
                    <p>Todo lo que necesitas para tu hogar, estudio y oficina en un solo lugar.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-star"></i>
                    <h3>Calidad Garantizada</h3>
                    <p>Seleccionamos los mejores productos para asegurar tu satisfacción.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Atención Personalizada</h3>
                    <p>Te atendemos por WhatsApp para una experiencia de compra cercana y rápida.</p>
                </div>
            </div>
      </section>

      <footer class="social-panel">
        <h2 class="texto-blanco">Síguenos en redes sociales</h2>
        <div class="iconos-horizontales">
            <a href="https://www.instagram.com/bazarycomercial.4e/" class="social-icon instagram" target="_blank" title="Instagram">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
            </a>
            <a href="https://api.whatsapp.com/send/?phone=56976509490&text&type=phone_number&app_absent=0" class="social-icon whatsapp" target="_blank" title="WhatsApp">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            </a>
        </div>
      </footer>
    </div>

    <aside class="cart-sidebar">
      <div class="cart-header"><h3>Tu Carrito</h3><button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button></div>
      <div class="cart-body"><p class="cart-empty-msg">Tu carrito está vacío.</p></div>
      <div class="cart-footer">
        <div class="cart-total"><strong>Total:</strong><span id="cart-total-price">$0</span></div>
        
        <div class="shipping-options" id="shipping-options" style="display: none;">
            <h4>Selecciona un método de entrega</h4>
            <div class="shipping-option">
                <input type="radio" id="shipping-pickup" name="shipping" value="Retiro en tienda física">
                <label for="shipping-pickup">Retiro en tienda física</label>
            </div>
            <div class="shipping-option">
                <input type="radio" id="shipping-delivery" name="shipping" value="Envío a domicilio">
                <label for="shipping-delivery">Envío a domicilio</label>
            </div>
        </div>
        
        <button class="btn-checkout" id="btn-finalize-purchase" disabled><i class="fab fa-whatsapp"></i> Pedir por WhatsApp</button>
        </div>
    </aside>
    <div class="cart-overlay"></div>

    <script src="js/carrito.js"></script>
    <script src="js/transiciones.js"></script>
    <script src="js/nav-responsive.js"></script>
    <script src="js/carousel.js"></script>
  </body>
</html>