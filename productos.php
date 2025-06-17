<?php
session_start();
require __DIR__ . '/db.php';

// Validar que el ID sea un número válido
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: catalogo.php");
    exit;
}
$id = (int)$_GET['id'];

// Obtener la información del producto
try {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}

if (!$producto) {
    $producto_no_encontrado = true;
} else {
    $variaciones = !empty($producto['variaciones']) ? json_decode($producto['variaciones'], true) : [];
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
    <title><?= isset($producto_no_encontrado) ? 'Producto no encontrado' : htmlspecialchars($producto['nombre']) ?> - 4E Bazar</title>
    
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

    <style>
      .page-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 20px;
        padding-top: 120px; /* Espacio para la navbar fija */
      }
      .product-detail-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border-radius: 25px;
        padding: 40px;
      }
      .product-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 50px;
        align-items: center;
      }
      .product-image-container img {
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
      }
      .product-image-container img:hover {
        transform: scale(1.03);
      }
      .product-info h1 {
        font-size: 2.8rem;
        font-weight: 800;
        margin-top: 0;
        margin-bottom: 10px;
        color: #3d3d3d;
        line-height: 1.2;
        text-shadow: 0 2px 5px rgba(0,0,0,0.05);
      }
      .product-info .descripcion {
        font-size: 1.1rem;
        line-height: 1.7;
        color: #555;
        margin: 20px 0;
      }
      .price-box {
        margin: 25px 0;
        border-top: 1px solid rgba(0,0,0,0.08);
        padding-top: 25px;
      }
      .price-final {
        font-size: 2.5rem;
        font-weight: 700;
        color: #e75480;
      }
      .price-old {
        text-decoration: line-through;
        color: #aaa;
        margin-left: 15px;
        font-size: 1.5rem;
      }
      .stock-info {
        display: inline-block;
        font-weight: 600;
        color: #fff;
        background-color: #28a745;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin: 15px 0;
      }
      .form-group { margin-bottom: 15px; }
      .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; }
      .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        background-color: #fff;
      }
      .btn-add-to-cart {
        width: 100%;
        padding: 15px;
        font-size: 1.2rem;
        font-weight: bold;
        cursor: pointer;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #e75480, #ff6b9d);
        color: white;
        transition: all 0.3s ease;
        margin-top: 20px;
        box-shadow: 0 5px 20px rgba(231, 84, 128, 0.3);
      }
      .btn-add-to-cart:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(231, 84, 128, 0.4);
      }
      .back-link {
        display: inline-block;
        margin-top: 30px;
        padding: 12px 25px;
        background-color: #fff;
        color: #e75480;
        border: 2px solid #e75480;
        font-weight: bold;
        text-decoration: none;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
      }
      .back-link:hover {
        background-color: #e75480;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(231, 84, 128, 0.3);
      }
      .error-message { text-align: center; padding: 50px; }
      @media (max-width: 768px) {
        .product-detail-grid { grid-template-columns: 1fr; }
        .product-info h1 { font-size: 2rem; }
      }
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>

    <main class="page-container">
        <?php if (isset($producto_no_encontrado)): ?>
            <div class="error-message">
                <h1>Producto no Encontrado</h1>
                <p>Lo sentimos, el producto que buscas no existe o ya no está disponible.</p>
                <a href="catalogo.php" class="back-link">« Volver al catálogo</a>
            </div>
        <?php else: ?>
            <div class="product-detail-card">
                <div class="product-detail-grid">
                    <div class="product-image-container">
                        <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>
                    <div class="product-info">
                        <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                        
                        <div class="price-box">
                            <span class="price-final">$<?= number_format($precio_final_js, 0, ',', '.') ?></span>
                            <?php if ($precio_final_js != $producto['precio']): ?>
                                <span class="price-old">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                        <p class="stock-info">Disponibles: <?= (int)$producto['cantidad'] ?></p>

                        <form id="product-form">
                            <?php if (!empty($variaciones)): ?>
                                <div class="variations-container">
                                    <?php foreach ($variaciones as $nombre_variacion => $opciones): ?>
                                        <div class="form-group">
                                            <label for="variacion-<?= strtolower($nombre_variacion) ?>"><?= htmlspecialchars($nombre_variacion) ?>:</label>
                                            <select id="variacion-<?= strtolower($nombre_variacion) ?>" class="form-control">
                                                <?php foreach ($opciones as $opcion): ?>
                                                    <option value="<?= htmlspecialchars($opcion) ?>"><?= htmlspecialchars($opcion) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="quantity-container">
                                 <div class="form-group">
                                    <label for="cantidad">Cantidad:</label>
                                    <input type="number" id="cantidad" class="form-control" value="1" min="1" max="<?= (int)$producto['cantidad'] ?>">
                                 </div>
                            </div>

                            <button type="button" id="add-to-cart-btn" class="btn-add-to-cart">Agregar al carrito</button>
                        </form>
                    </div>
                </div>
            </div>
            <a href="catalogo.php" class="back-link">« Volver al catálogo</a>
        <?php endif; ?>
    </main>

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
    <script src="nav-responsive.js"></script>
    <script>
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            const productoBase = {
                id: <?= $producto['id'] ?? 'null' ?>,
                name: '<?= isset($producto) ? htmlspecialchars(addslashes($producto['nombre'])) : '' ?>',
                price: <?= $precio_final_js ?? 0 ?>,
                image: '<?= isset($producto) ? htmlspecialchars($producto['imagen']) : '' ?>'
            };

            const cantidadSeleccionada = parseInt(document.getElementById('cantidad').value);
            const stockDisponible = <?= (int)($producto['cantidad'] ?? 0) ?>;

            if (cantidadSeleccionada > stockDisponible) {
                alert(`No hay suficiente stock. Solo quedan ${stockDisponible} unidades disponibles.`);
                return;
            }

            let nombreFinal = productoBase.name;
            const detalles = [];
            
            const variacionesSelects = document.querySelectorAll('.variations-container select');
            variacionesSelects.forEach(select => {
                const nombreVariacion = select.previousElementSibling.textContent.replace(':', '');
                detalles.push(`${select.value}`); // Solo el valor, ej: "Rojo"
            });

            if (detalles.length > 0) {
                nombreFinal += ` (${detalles.join(', ')})`;
            }

            const productoParaCarrito = {
                id: `${productoBase.id}-${detalles.join('-')}`, // ID único por combinación de variaciones
                name: nombreFinal,
                price: productoBase.price,
                image: productoBase.image,
                quantity: cantidadSeleccionada
            };

            if (typeof agregarAlCarrito === "function") {
                agregarAlCarrito(productoParaCarrito);
            } else {
                alert('Error al agregar al carrito.');
            }
        });
    }
    </script>
</body>
</html>