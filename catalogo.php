<?php
// Iniciar la sesión es importante para que la navbar modular funcione correctamente
session_start();
require __DIR__ . '/db.php';

// Cargar todos los productos desde la base de datos
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

// Crear una lista de categorías disponibles para el filtro, convirtiendo los nombres a "slugs"
$categorias_disponibles = [];
foreach ($productos as $producto) {
    // Convierte 'Hogar y Decoración' a 'hogar-y-decoracion' para usarlo en CSS y JS
    $cat_slug = str_replace(' ', '-', strtolower($producto['nombreCategoria']));
    
    // Almacena el slug y el nombre real para mostrarlos correctamente
    if (!isset($categorias_disponibles[$cat_slug])) {
        $categorias_disponibles[$cat_slug] = $producto['nombreCategoria'];
    }
}
ksort($categorias_disponibles); // Ordena las categorías alfabéticamente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - 4E Bazar</title>
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
      /* Estilos específicos para la página de catálogo */
      .page-container {
        max-width: 1200px;
        margin: 50px auto 20px auto; 
        padding: 0 20px;
      }
      
      /* Estilos para la Barra de Filtro de Categorías */
      .filtro-barra {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin: 2rem auto;
        padding: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        width: fit-content;
      }
      .filtro-barra label {
        font-weight: 600;
        font-size: 1.1rem;
        color: #fff;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
      }
      .filtro-select-wrapper {
        position: relative;
      }
      .filtro-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px 40px 10px 15px;
        font-size: 1rem;
        color: #e75480;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;
      }
      .filtro-select:hover {
        border-color: #e75480;
      }
      .filtro-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.3);
      }
      .filtro-select-wrapper::after {
        content: '▼';
        font-size: 12px;
        color: #e75480;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
      }
      
      /* Estilos para las tarjetas de producto */
      .price-old { text-decoration: line-through; color: #888; margin-left: 5px; }
      .producto-box {
        display: flex;
        flex-direction: column;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      .producto-box img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
      }
       .producto-box a:hover img {
        transform: scale(1.03);
      }
      .producto-descripcion {
        font-size: 0.9rem;
        color: #666;
        flex-grow: 1;
        margin: 10px 0;
      }
      .producto-categoria {
        font-size: 0.8rem;
        color: #999;
        margin-top: 10px;
        text-align: left;
        font-style: italic;
      }
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
    </style>  
</head>
<body>
    
    <?php include 'nav.php'; ?>
    
    <main class="page-container">
        <h1>Catálogo</h1>

        <div class="filtro-barra">
            <label for="filtro-categorias">Filtrar por categoría:</label>
            <div class="filtro-select-wrapper">
                <select id="filtro-categorias" class="filtro-select" onchange="filtrarCategoria(this.value)">
                    <option value="todos">Todos</option>
                    <?php foreach ($categorias_disponibles as $slug => $nombre_real): ?>
                      <option value="<?= $slug ?>"><?= ucfirst($nombre_real) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
            <div class="producto-box-wrapper <?= htmlspecialchars($categoria_clase) ?>">
                <div class="producto-box">
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
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay productos disponibles.</p>
        <?php endif; ?>
      </div>
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

    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
    <script>
      function filtrarCategoria(categoriaSeleccionada) {
        document.querySelectorAll('.producto-box-wrapper').forEach(wrapper => {
            if (categoriaSeleccionada === 'todos' || wrapper.classList.contains(categoriaSeleccionada)) {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
            }
        });
      }
    </script>
</body>
</html>