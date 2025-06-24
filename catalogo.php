<?php
// Iniciar la sesión es importante para que la navbar modular funcione correctamente
session_start();
require __DIR__ . '/db.php';

// Cargar todos los productos desde la base de datos
$productos_catalogo = [];
$categorias_disponibles = [];

try {
    // CONSULTA FINAL Y CORRECTA: Usa los nombres de columna definitivos de tu base de datos actual.
    $stmt = $pdo->query("
        SELECT
            p.id,
            p.nombre,
            p.imagen_principal,
            c.nombre_categoria,
            MIN(v.precio) AS precio_desde
        FROM productos p
        JOIN categorias c ON p.categoriaID = c.id_categoria
        LEFT JOIN variantes_producto v ON p.id = v.id_producto
        WHERE v.stock > 0
        GROUP BY p.id, p.nombre, p.imagen_principal, c.nombre_categoria
        ORDER BY p.nombre ASC
    ");
    $productos_catalogo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lógica para el filtro (funciona gracias al alias 'nombre_categoria')
    foreach ($productos_catalogo as $producto) {
        $cat_slug = str_replace(' ', '-', strtolower($producto['nombre_categoria']));
        if (!isset($categorias_disponibles[$cat_slug])) {
            $categorias_disponibles[$cat_slug] = $producto['nombre_categoria'];
        }
    }
    ksort($categorias_disponibles);

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
    <title>Catálogo de Productos - 4E Bazar</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
        /* =============================================
           ESTILOS MEJORADOS PARA LA PÁGINA DE CATÁLOGO
           ============================================= */
        
        /* Aseguramos que el fondo degradado del body se vea siempre */
        body {
            background: linear-gradient(135deg, #ffc0cb, #ffb6c1, #ffa0b4);
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            padding-top: 120px; /* Espacio para la navbar fija */
        }
        
        /* Nuevo estilo para el encabezado del catálogo */
        .catalog-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .catalog-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Estilos para la Barra de Filtro */
        .filtro-barra {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin: 2rem auto;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px; /* Forma de píldora */
            width: fit-content;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .filtro-barra label {
            font-weight: 600;
            font-size: 1rem;
            color: #444;
        }
        .filtro-select-wrapper { position: relative; }
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
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>
    
    <main class="page-container">
        
        <div class="catalog-header">
            <h1>Catálogo</h1>
        </div>

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
        <?php if (count($productos_catalogo) > 0): ?>
          <?php foreach ($productos_catalogo as $producto): ?>
            <?php $categoria_clase = str_replace(' ', '-', strtolower($producto['nombre_categoria'])); ?>
            <div class="producto-box-wrapper <?= htmlspecialchars($categoria_clase) ?>">
                <div class="producto-box">
                    <a href="productos.php?id=<?= $producto['id'] ?>">
                        <img src="<?= htmlspecialchars($producto['imagen_principal'] ?: 'Imagenes/placeholder.png') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    </a>
                    <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <p class="producto-precio">Desde $<?= number_format($producto['precio_desde'], 0, ',', '.') ?></p>
                    <a href="productos.php?id=<?= $producto['id'] ?>" class="btn-add-to-cart">Elegir Opciones</a>
                </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align:center; width:100%;">No hay productos disponibles para mostrar.</p>
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