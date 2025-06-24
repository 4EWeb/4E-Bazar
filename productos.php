<?php
session_start();
require __DIR__ . '/db.php';

// Validar que el ID sea un número válido
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: catalogo.php");
    exit;
}
$id_producto_base = (int)$_GET['id'];

try {
    // Consulta para el producto principal (usando el nombre de columna 'id')
    $stmt_producto = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $stmt_producto->execute([$id_producto_base]);
    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new Exception("Producto no encontrado.");
    }
    
    // Consulta para obtener todas las variantes de este producto que tengan stock
    $stmt_variantes = $pdo->prepare('SELECT * FROM variantes_producto WHERE id_producto = ? AND stock > 0');
    $stmt_variantes->execute([$id_producto_base]);
    $variantes = $stmt_variantes->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay variantes con stock, podríamos manejarlo aquí si quisiéramos
    if (empty($variantes)) {
        // Opcional: podrías mostrar un mensaje de "agotado"
    }
    
    $variantes_json = json_encode($variantes);

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($producto) ? htmlspecialchars($producto['nombre']) : 'Error' ?> - 4E Bazar</title>
    
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
      /* Estilos para la página de detalle del producto */
      .page-container { max-width: 1100px; margin: 0 auto; padding: 40px 20px; padding-top: 120px; }
      .product-detail-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); border-radius: 25px; padding: 40px; }
      .product-detail-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 50px; align-items: flex-start; }
      .product-image-container img { width: 100%; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
      .product-info h1 { font-size: 2.8rem; margin-top: 0; color: #333; }
      .product-info .descripcion { font-size: 1.1rem; line-height: 1.7; color: #555; margin: 15px 0; }
      .price-box { margin: 20px 0; border-top: 1px solid #eee; padding-top: 20px; }
      .price-final { font-size: 2.5rem; font-weight: bold; color: #e75480; }
      .stock-info { font-weight: 600; color: #28a745; margin: 15px 0; }
      .form-group { margin-bottom: 15px; }
      .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; }
      .form-control { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; background-color: #fff; }
      .btn-add-to-cart { width: 100%; padding: 15px; font-size: 1.2rem; font-weight: bold; cursor: pointer; border: none; border-radius: 12px; background: linear-gradient(135deg, #e75480, #ff6b9d); color: white; transition: all 0.3s ease; margin-top: 20px; }
      .btn-add-to-cart:disabled { background: #ccc; cursor: not-allowed; }
      .btn-add-to-cart:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(231, 84, 128, 0.4); }
      .back-link { display: inline-block; margin-top: 30px; color: #e75480; font-weight: bold; }
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>

    <main class="page-container">
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <h1>Error</h1>
                <p><?= htmlspecialchars($error_message) ?></p>
                <a href="catalogo.php" class="back-link">« Volver al catálogo</a>
            </div>
        <?php else: ?>
            <div class="product-detail-card">
                <div class="product-detail-grid">
                    <div class="product-image-container">
                        <img src="<?= htmlspecialchars($producto['imagen_principal'] ?: 'Imagenes/placeholder.png') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" id="main-product-image">
                    </div>
                    <div class="product-info">
                        <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                        
                        <div class="price-box">
                            <span id="product-price" class="price-final">$<?= number_format($variantes[0]['precio'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                        
                        <p class="descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                        
                        <form id="product-form">
                            <div class="form-group">
                                <label for="variante_select">Selecciona una opción:</label>
                                <select id="variante_select" class="form-control">
                                    <?php foreach ($variantes as $variante): ?>
                                        <option value="<?= $variante['id_variante'] ?>">
                                            <?= htmlspecialchars($variante['sku']) ?> - $<?= number_format($variante['precio'], 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <p id="stock-info" class="stock-info">Disponibles: <?= (int)($variantes[0]['stock'] ?? 0) ?></p>

                            <div class="form-group">
                                <label for="cantidad">Cantidad:</label>
                                <input type="number" id="cantidad" class="form-control" value="1" min="1">
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
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
    <script>
        // Pasamos los datos de las variantes de PHP a JavaScript
        const variantesData = <?= $variantes_json ?? '[]' ?>;

        // Seleccionamos los elementos de la página que vamos a actualizar
        const varianteSelect = document.getElementById('variante_select');
        const mainImage = document.getElementById('main-product-image');
        const priceDisplay = document.getElementById('product-price');
        const stockDisplay = document.getElementById('stock-info');
        const cantidadInput = document.getElementById('cantidad');
        const addToCartBtn = document.getElementById('add-to-cart-btn');

        function actualizarInfoVariante() {
            const selectedVarianteId = parseInt(varianteSelect.value);
            const varianteSeleccionada = variantesData.find(v => v.id_variante === selectedVarianteId);

            if (varianteSeleccionada) {
                // Actualizar precio
                priceDisplay.textContent = `$${parseInt(varianteSeleccionada.precio).toLocaleString('es-CL')}`;
                
                // Actualizar stock y el máximo del input de cantidad
                stockDisplay.textContent = `Disponibles: ${varianteSeleccionada.stock}`;
                cantidadInput.max = varianteSeleccionada.stock;

                // Actualizar imagen si la variante tiene una específica
                if (varianteSeleccionada.imagen) {
                    mainImage.src = varianteSeleccionada.imagen;
                }
            }
        }
        
        // Listener para cuando cambia la selección de variante
        if(varianteSelect) {
            varianteSelect.addEventListener('change', actualizarInfoVariante);
        }

        // Listener para el botón de agregar al carrito
        if(addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                const selectedVarianteId = parseInt(varianteSelect.value);
                const varianteSeleccionada = variantesData.find(v => v.id_variante === selectedVarianteId);
                
                if (!varianteSeleccionada) {
                    alert('Por favor, selecciona una opción válida.');
                    return;
                }
                
                const cantidadSeleccionada = parseInt(cantidadInput.value);
                if (cantidadSeleccionada > varianteSeleccionada.stock) {
                    alert(`No hay suficiente stock. Solo quedan ${varianteSeleccionada.stock} unidades.`);
                    return;
                }

                const productoParaCarrito = {
                    id: varianteSeleccionada.id_variante, // Usamos el ID de la variante
                    name: `<?= htmlspecialchars(addslashes($producto['nombre'])) ?> (${varianteSeleccionada.sku})`,
                    price: varianteSeleccionada.precio,
                    image: varianteSeleccionada.imagen || '<?= htmlspecialchars($producto['imagen_principal']) ?>',
                    quantity: cantidadSeleccionada
                };
                
                agregarAlCarrito(productoParaCarrito);
            });
        }
    </script>
</body>
</html>