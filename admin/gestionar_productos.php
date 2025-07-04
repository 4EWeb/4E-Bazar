<?php
// admin/gestionar_productos.php (VERSIÓN FINAL Y FUNCIONAL)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require '../db.php';

// --- ACCIÓN: CAMBIAR ESTADO ACTIVO/INACTIVO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $id_producto_toggle = $_POST['id_producto'];
    $estado_actual = $_POST['current_status'];
    $nuevo_estado = $estado_actual == 1 ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE productos SET activo = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $id_producto_toggle]);
    $_SESSION['message'] = "Estado del producto actualizado.";
    header("Location: gestionar_productos.php");
    exit();
}

// --- ACCIÓN: GUARDAR/ACTUALIZAR PRODUCTO PRINCIPAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_product') {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoriaID = $_POST['categoriaID'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    if (empty($id_producto)) { // Crear nuevo producto
        $sql = "INSERT INTO productos (nombre, descripcion, categoriaID, activo) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $categoriaID, $activo]);
        $_SESSION['message'] = "Producto creado exitosamente.";
    } else { // Actualizar producto existente
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoriaID = ?, activo = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $categoriaID, $activo, $id_producto]);
        $_SESSION['message'] = "Producto actualizado exitosamente.";
    }
    header("Location: gestionar_productos.php");
    exit();
}

// --- ACCIÓN: GUARDAR/ACTUALIZAR VARIANTE DE PRODUCTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_variant') {
    $id_variante = $_POST['id_variante'];
    $id_producto_padre = $_POST['id_producto_padre'];
    $sku = $_POST['sku'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    if (empty($id_variante)) { // Crear nueva variante
        $sql = "INSERT INTO variantes_producto (id_producto, sku, precio, stock) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_producto_padre, $sku, $precio, $stock]);
        $_SESSION['message'] = 'Variante creada exitosamente.';
    } else { // Actualizar variante existente
        $sql = "UPDATE variantes_producto SET sku = ?, precio = ?, stock = ? WHERE id_variante = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sku, $precio, $stock, $id_variante]);
        $_SESSION['message'] = 'Variante actualizada exitosamente.';
    }
    header("Location: gestionar_productos.php");
    exit();
}

// =======================================================
// BLOQUE DE DATOS: OBTIENE LA INFORMACIÓN PARA MOSTRAR
// =======================================================
$productos = $pdo->query("SELECT p.id, p.nombre, p.descripcion, p.activo, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.categoriaID = c.id_categoria ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);

// Determinar qué formulario mostrar en el editor
$producto_a_editar = null;
$variante_a_editar = null;
$producto_para_nueva_variante = null;

if (isset($_GET['edit_product_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$_GET['edit_product_id']]);
    $producto_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_GET['edit_variant_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM variantes_producto WHERE id_variante = ?");
    $stmt->execute([$_GET['edit_variant_id']]);
    $variante_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_GET['add_variant_for'])) {
     $stmt = $pdo->prepare("SELECT id, nombre FROM productos WHERE id = ?");
    $stmt->execute([$_GET['add_variant_for']]);
    $producto_para_nueva_variante = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'header.php';
?>

<h1>Gestión Integral de Productos</h1>
<p>Gestiona tus productos y todas sus variantes desde una única pantalla.</p>
<hr>

<div class="main-container">

    <div class="editor-column">
        <div class="card">
            <div class="card-header">
                <h4>
                    <?php 
                        if ($producto_a_editar) { echo 'Editando Producto Principal'; }
                        elseif ($variante_a_editar) { echo 'Editando Variante'; }
                        elseif ($producto_para_nueva_variante) { echo 'Añadiendo Variante a "' . htmlspecialchars($producto_para_nueva_variante['nombre']) . '"'; }
                        else { echo 'Agregar Nuevo Producto'; }
                    ?>
                </h4>
            </div>
            <div class="card-body">
                
                <?php if ($variante_a_editar || $producto_para_nueva_variante): ?>
                <form action="gestionar_productos.php" method="POST">
                    <input type="hidden" name="action" value="save_variant">
                    <input type="hidden" name="id_variante" value="<?php echo $variante_a_editar['id_variante'] ?? ''; ?>">
                    <input type="hidden" name="id_producto_padre" value="<?php echo $variante_a_editar['id_producto'] ?? $producto_para_nueva_variante['id']; ?>">
                    
                    <div class="mb-3"><label class="form-label">SKU</label><input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($variante_a_editar['sku'] ?? ''); ?>"></div>
                    <div class="mb-3"><label class="form-label">Precio</label><input type="number" name="precio" step="1" class="form-control" value="<?php echo (int)($variante_a_editar['precio'] ?? 0); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?php echo $variante_a_editar['stock'] ?? 0; ?>" required></div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><?php echo $variante_a_editar ? 'Actualizar Variante' : 'Guardar Variante'; ?></button>
                        <a href="gestionar_productos.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>

                <?php else: ?>
                <form action="gestionar_productos.php" method="POST">
                    <input type="hidden" name="action" value="save_product">
                    <input type="hidden" name="id_producto" value="<?php echo $producto_a_editar['id'] ?? ''; ?>">
                    
                    <div class="mb-3"><label class="form-label">Nombre del Producto</label><input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto_a_editar['nombre'] ?? ''); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($producto_a_editar['descripcion'] ?? ''); ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Categoría</label>
                        <select name="categoriaID" class="form-select" required>
                            <option value="">Elige...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?php echo $c['id_categoria']; ?>" <?php if(isset($producto_a_editar) && $producto_a_editar['categoriaID'] == $c['id_categoria']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($c['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" <?php echo (!isset($producto_a_editar) || $producto_a_editar['activo'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Producto Activo</label>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><?php echo $producto_a_editar ? 'Actualizar Producto' : 'Guardar Nuevo Producto'; ?></button>
                        <?php if ($producto_a_editar): ?><a href="gestionar_productos.php" class="btn btn-secondary">Cancelar</a><?php endif; ?>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="products-column">
        <div class="accordion">
            <?php foreach ($productos as $producto): ?>
                <div class="accordion-item">
                    <button class="accordion-button">
                        <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                        <form action="gestionar_productos.php" method="POST" class="ms-auto" onclick="event.stopPropagation()">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $producto['activo']; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $producto['activo'] ? 'btn-success' : 'btn-secondary'; ?>">
                                <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </button>
                        </form>
                    </button>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p class="text-muted" style="margin-bottom: 1rem;">
                                <strong>Categoría:</strong> <?php echo htmlspecialchars($producto['nombre_categoria']); ?><br>
                                <?php echo htmlspecialchars($producto['descripcion']); ?>
                            </p>
                            <a href="?edit_product_id=<?php echo $producto['id']; ?>" class="btn btn-secondary btn-sm">Editar Datos Principales</a>
                            <hr>
                            <h6>Variantes:</h6>
                            <table class="table table-hover table-sm">
                                <thead><tr><th>Atributos</th><th>SKU</th><th>Precio</th><th>Stock</th><th></th></tr></thead>
                                <tbody>
                                    <?php
                                    $stmt_variantes = $pdo->prepare("SELECT * FROM variantes_producto WHERE id_producto = ?");
                                    $stmt_variantes->execute([$producto['id']]);
                                    $variantes = $stmt_variantes->fetchAll(PDO::FETCH_ASSOC);
                                    if (empty($variantes)) {
                                        echo '<tr><td colspan="5" class="text-center text-muted">Aún no hay variantes.</td></tr>';
                                    } else {
                                        foreach ($variantes as $variante): ?>
                                        <tr>
                                            <td>Base</td>
                                            <td><?php echo htmlspecialchars($variante['sku']); ?></td>
                                            <td>$<?php echo number_format($variante['precio'], 0); ?></td>
                                            <td><?php echo $variante['stock']; ?></td>
                                            <td class="text-end">
                                                <a href="?edit_variant_id=<?php echo $variante['id_variante']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; 
                                    } ?>
                                </tbody>
                            </table>
                            <a href="?add_variant_for=<?php echo $producto['id']; ?>" class="btn btn-success btn-sm mt-2">Añadir Nueva Variante</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>