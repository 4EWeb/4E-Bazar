<?php
// admin/gestionar_productos.php (VERSIÓN FINAL CON SOFT DELETE Y BOTONES ALINEADOS)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require '../db.php';

// --- FUNCIÓN PARA SANITIZAR NOMBRES DE ARCHIVO ---
function sanitize_filename($filename) {
    // Reemplaza espacios con guiones bajos y elimina caracteres no permitidos
    return preg_replace('/[^a-zA-Z0-9-_\.]/', '', str_replace(' ', '_', $filename));
}

// --- FUNCIÓN PARA MANEJAR LA SUBIDA DE IMÁGENES ---
function handle_image_upload($file_input_name, $product_name, $variant_name = '') {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../imagenes/productos/';
        
        // Crea el directorio base si no existe
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Prepara el nombre y la ruta del archivo
        $sanitized_product_name = sanitize_filename($product_name);
        $file_extension = pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION);

        if (!empty($variant_name)) {
            // Es una imagen de variante
            $product_folder = $upload_dir . $sanitized_product_name . '/';
            if (!file_exists($product_folder)) {
                mkdir($product_folder, 0777, true);
            }
            $sanitized_variant_name = sanitize_filename($variant_name);
            $filename = $sanitized_variant_name . '.' . $file_extension;
            $db_path = 'imagenes/productos/' . $sanitized_product_name . '/' . $filename;
            $full_path = $product_folder . $filename;
        } else {
            // Es una imagen de producto principal
            $filename = $sanitized_product_name . '.' . $file_extension;
            $db_path = 'imagenes/productos/' . $filename;
            $full_path = $upload_dir . $filename;
        }

        if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $full_path)) {
            return $db_path; // Devuelve la ruta para la BD
        }
    }
    return null; // Devuelve null si no hay subida o hay error
}


// --- ACCIÓN: CAMBIAR ESTADO ACTIVO/INACTIVO DE PRODUCTO ---
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
    $id_producto = $_POST['id_producto'] ?? null;
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoriaID = $_POST['categoriaID'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Manejar subida de imagen
    $imagen_path = handle_image_upload('imagen', $nombre);

    if (empty($id_producto)) { // Crear nuevo producto
        $sql = "INSERT INTO productos (nombre, descripcion, categoriaID, activo, imagen_principal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $categoriaID, $activo, $imagen_path ?? '']);
        $_SESSION['message'] = "Producto creado exitosamente.";
    } else { // Actualizar producto existente
        $params = [$nombre, $descripcion, $categoriaID, $activo, $id_producto];
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoriaID = ?, activo = ? WHERE id = ?";
        
        // Solo actualiza la imagen si se subió una nueva
        if ($imagen_path) {
            $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoriaID = ?, activo = ?, imagen_principal = ? WHERE id = ?";
            array_splice($params, 4, 0, $imagen_path); // Inserta el path de la imagen en los parámetros
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['message'] = "Producto actualizado exitosamente.";
    }
    header("Location: gestionar_productos.php");
    exit();
}

// --- ACCIÓN: GUARDAR/ACTUALIZAR VARIANTE DE PRODUCTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_variant') {
    $id_variante = $_POST['id_variante'] ?? null;
    $id_producto_padre = $_POST['id_producto_padre'];
    $nombre_producto_padre = $_POST['nombre_producto_padre'];
    $sku = $_POST['sku'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $descuento = $_POST['descuento'] ?? 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $opciones = $_POST['opciones'] ?? [];

    // Manejar subida de imagen para la variante
    $imagen_variante_path = handle_image_upload('imagen_variante', $nombre_producto_padre, $sku);

    $pdo->beginTransaction();
    try {
        if (empty($id_variante)) { // Crear nueva variante
            $sql = "INSERT INTO variantes_producto (id_producto, sku, precio, stock, descuento, destacado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_producto_padre, $sku, $precio, $stock, $descuento, $destacado, $imagen_variante_path]);
            $id_variante = $pdo->lastInsertId();
            $_SESSION['message'] = 'Variante creada exitosamente.';
        } else { // Actualizar variante existente
            $sql_update = "UPDATE variantes_producto SET sku = ?, precio = ?, stock = ?, descuento = ?, destacado = ? WHERE id_variante = ?";
            $params_update = [$sku, $precio, $stock, $descuento, $destacado, $id_variante];

            if ($imagen_variante_path) {
                $sql_update = "UPDATE variantes_producto SET sku = ?, precio = ?, stock = ?, descuento = ?, destacado = ?, imagen = ? WHERE id_variante = ?";
                array_splice($params_update, 5, 0, $imagen_variante_path);
            }
            
            $stmt = $pdo->prepare($sql_update);
            $stmt->execute($params_update);
            $_SESSION['message'] = 'Variante actualizada exitosamente.';
        }
        
        $pdo->prepare("DELETE FROM variante_opcion WHERE id_variante = ?")->execute([$id_variante]);
        if (!empty($opciones)) {
            $stmt_opcion = $pdo->prepare("INSERT INTO variante_opcion (id_variante, id_opcion) VALUES (?, ?)");
            foreach ($opciones as $id_opcion) {
                if (!empty($id_opcion)) {
                    $stmt_opcion->execute([$id_variante, $id_opcion]);
                }
            }
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Error al guardar la variante: ' . $e->getMessage();
    }

    header("Location: gestionar_productos.php");
    exit();
}

// --- ACCIÓN: ELIMINAR PRODUCTO (AHORA ES SOFT DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_product') {
    $id_producto_a_eliminar = $_POST['id_producto'];
    try {
        $pdo->beginTransaction();
        
        // Marcar todas las variantes del producto como inactivas
        $stmt_update_variantes_estado = $pdo->prepare("UPDATE variantes_producto SET activo = 0 WHERE id_producto = ?");
        $stmt_update_variantes_estado->execute([$id_producto_a_eliminar]);

        // Marcar el producto principal como inactivo
        $stmt_update_producto_estado = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        $stmt_update_producto_estado->execute([$id_producto_a_eliminar]);
        
        $pdo->commit();
        $_SESSION['message'] = 'Producto y sus variantes marcados como inactivos exitosamente.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Error al marcar el producto como inactivo: ' . $e->getMessage();
    }
    header("Location: gestionar_productos.php");
    exit();
}

// --- ACCIÓN: ELIMINAR VARIANTE (AHORA ES SOFT DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_variant') {
    $id_variante_a_eliminar = $_POST['id_variante'];
    $id_producto_padre = $_POST['id_producto_padre']; // Para redirigir correctamente al producto padre

    try {
        // Marcar la variante como inactiva
        $stmt = $pdo->prepare("UPDATE variantes_producto SET activo = 0 WHERE id_variante = ?");
        $stmt->execute([$id_variante_a_eliminar]);
        
        $_SESSION['message'] = 'Variante eliminada exitosamente.';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error al marcar la variante como inactiva: ' . $e->getMessage();
    }
    // Redirigir de nuevo a la página del producto padre y abrir su editor
    header("Location: gestionar_productos.php?edit_product_id=" . $id_producto_padre . "#editor-panel");
    exit();
}


// =======================================================
// BLOQUE DE DATOS: OBTIENE LA INFORMACIÓN PARA MOSTRAR
// =======================================================
// Se agrega `WHERE p.activo = 1` para solo mostrar productos activos
$productos = $pdo->query("SELECT p.id, p.nombre, p.descripcion, p.activo, p.imagen_principal, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.categoriaID = c.id_categoria WHERE p.activo = 1 ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);

$atributos_query = $pdo->query("SELECT a.id_atributo, a.nombre, o.id_opcion, o.valor FROM atributos a JOIN opciones o ON a.id_atributo = o.id_atributo ORDER BY a.nombre, o.valor");
$atributos_con_opciones = [];
foreach ($atributos_query as $row) {
    $atributos_con_opciones[$row['nombre']][] = ['id' => $row['id_opcion'], 'valor' => $row['valor']];
}

$producto_a_editar = null;
$variante_a_editar = null;
$producto_para_nueva_variante = null;
$opciones_de_variante_a_editar = [];

if (isset($_GET['edit_product_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$_GET['edit_product_id']]);
    $producto_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_GET['edit_variant_id'])) {
    $stmt = $pdo->prepare("SELECT v.*, p.nombre as nombre_producto_padre FROM variantes_producto v JOIN productos p ON v.id_producto = p.id WHERE v.id_variante = ?");
    $stmt->execute([$_GET['edit_variant_id']]);
    $variante_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($variante_a_editar) {
        $stmt_opciones = $pdo->prepare("SELECT id_opcion FROM variante_opcion WHERE id_variante = ?");
        $stmt_opciones->execute([$_GET['edit_variant_id']]);
        $opciones_de_variante_a_editar = $stmt_opciones->fetchAll(PDO::FETCH_COLUMN);
    }
} elseif (isset($_GET['add_variant_for'])) {
     $stmt = $pdo->prepare("SELECT id, nombre FROM productos WHERE id = ?");
    $stmt->execute([$_GET['add_variant_for']]);
    $producto_para_nueva_variante = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'header.php';
?>
<div class="page-header">
    <h1>Gestión de Productos</h1>
    <p>Administra tus productos base, sus variantes, imágenes y stock.</p>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success" role="alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger" role="alert"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="product-manager-container">
    <div class="product-editor-column">
        <div class="card product-editor-card" id="editor-panel">
            <div class="card-header">
                <h4>
                    <?php 
                        if ($producto_a_editar) { echo 'Editando Producto'; }
                        elseif ($variante_a_editar) { echo 'Editando Variante de "' . htmlspecialchars($variante_a_editar['nombre_producto_padre']) . '"'; }
                        elseif ($producto_para_nueva_variante) { echo 'Añadiendo Variante a "' . htmlspecialchars($producto_para_nueva_variante['nombre']) . '"'; }
                        else { echo 'Agregar Nuevo Producto'; }
                    ?>
                </h4>
            </div>
            <div class="card-body">
                
                <?php if ($variante_a_editar || $producto_para_nueva_variante): // FORMULARIO DE VARIANTE ?>
                <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_variant">
                    <input type="hidden" name="id_variante" value="<?php echo $variante_a_editar['id_variante'] ?? ''; ?>">
                    <input type="hidden" name="id_producto_padre" value="<?php echo $variante_a_editar['id_producto'] ?? $producto_para_nueva_variante['id']; ?>">
                    <input type="hidden" name="nombre_producto_padre" value="<?php echo htmlspecialchars($variante_a_editar['nombre_producto_padre'] ?? $producto_para_nueva_variante['nombre']); ?>">
                    
                    <h5>Atributos de la Variante</h5>
                    <?php foreach ($atributos_con_opciones as $nombre_attr => $opciones_attr): ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo htmlspecialchars($nombre_attr); ?></label>
                            <select name="opciones[]" class="form-select">
                                <option value="">(Sin especificar)</option>
                                <?php foreach ($opciones_attr as $opcion): ?>
                                    <option value="<?php echo $opcion['id']; ?>" <?php if(in_array($opcion['id'], $opciones_de_variante_a_editar)) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($opcion['valor']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <h5>Datos de la Variante</h5>
                    <div class="mb-3"><label class="form-label">SKU (Nombre para archivo de imagen)</label><input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($variante_a_editar['sku'] ?? ''); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Precio</label><input type="number" name="precio" step="1" class="form-control" value="<?php echo (int)($variante_a_editar['precio'] ?? 0); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?php echo $variante_a_editar['stock'] ?? 0; ?>" required></div>
                    <div class="mb-3"><label class="form-label">Descuento (%)</label><input type="number" name="descuento" class="form-control" value="<?php echo $variante_a_editar['descuento'] ?? 0; ?>"></div>
                    
                    <div class="mb-3">
                        <label for="imagen_variante" class="form-label">Imagen de la Variante</label>
                        <input class="form-control" type="file" name="imagen_variante" id="imagen_variante">
                        <?php if (!empty($variante_a_editar['imagen'])): ?>
                            <div class="mt-2">
                                <small>Imagen actual:</small>
                                <img src="../<?php echo htmlspecialchars($variante_a_editar['imagen']); ?>" alt="Imagen actual" class="image-preview">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="destacado" id="destacado" value="1" <?php echo (isset($variante_a_editar) && $variante_a_editar['destacado'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="destacado">Variante Destacada</label>
                    </div>

                    <div class="d-flex justify-content-between mt-4 align-items-center"> <button type="submit" class="btn btn-primary btn-lg">
                            <?php echo $variante_a_editar ? 'Actualizar Variante' : 'Guardar Variante'; ?>
                        </button>
                        <div class="d-flex gap-2"> <a href="gestionar_productos.php?edit_product_id=<?php echo $variante_a_editar['id_producto'] ?? $producto_para_nueva_variante['id']; ?>#editor-panel" class="btn btn-secondary btn-lg">Cancelar</a>
                            <?php if ($variante_a_editar): // MOSTRAR BOTÓN DE ELIMINAR SOLO SI SE ESTÁ EDITANDO UNA VARIANTE EXISTENTE ?>
                                </form> 
                                <form action="gestionar_productos.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas ELIMINAR esta variante? Esta acción es irreversible.');" style="display: inline-block;">
                                    <input type="hidden" name="action" value="delete_variant">
                                    <input type="hidden" name="id_variante" value="<?php echo $variante_a_editar['id_variante']; ?>">
                                    <input type="hidden" name="id_producto_padre" value="<?php echo $variante_a_editar['id_producto']; ?>">
                                    <button type="submit" class="btn btn-danger btn-lg">Eliminar Variante</button>
                                </form>
                                <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="save_variant">
                                    <input type="hidden" name="id_variante" value="<?php echo $variante_a_editar['id_variante'] ?? ''; ?>">
                                    <input type="hidden" name="id_producto_padre" value="<?php echo $variante_a_editar['id_producto'] ?? $producto_para_nueva_variante['id']; ?>">
                                    <input type="hidden" name="nombre_producto_padre" value="<?php echo htmlspecialchars($variante_a_editar['nombre_producto_padre'] ?? $producto_para_nueva_variante['nombre']); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <?php else: // FORMULARIO DE PRODUCTO PRINCIPAL ?>
                <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
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
                     <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen Principal</label>
                        <input class="form-control" type="file" name="imagen" id="imagen">
                        <?php if (!empty($producto_a_editar['imagen_principal'])): ?>
                            <div class="mt-2">
                                <small>Imagen actual:</small>
                                <img src="../<?php echo htmlspecialchars($producto_a_editar['imagen_principal']); ?>" alt="Imagen actual" class="image-preview">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1" <?php echo (!isset($producto_a_editar) || $producto_a_editar['activo'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Producto Activo</label>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><?php echo $producto_a_editar ? 'Actualizar Producto' : 'Guardar Nuevo Producto'; ?></button>
                        <?php if ($producto_a_editar): ?>
                            <a href="gestionar_productos.php" class="btn btn-secondary">Cancelar</a>
                            <form action="gestionar_productos.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas ELIMINAR este producto y TODAS sus variantes? Esta acción es irreversible.');">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id_producto" value="<?php echo $producto_a_editar['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-lg mt-2">Eliminar Producto</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="product-list-column">
        <div class="search-bar-container">
            <input type="text" id="search-input" class="search-input" onkeyup="filtrarProductos()" placeholder="Escribe para buscar un producto...">
        </div>
        <div id="no-results-message">No se encontraron productos que coincidan.</div>
        
        <div class="product-accordion" id="product-accordion">
            <?php foreach ($productos as $producto): ?>
                <div class="product-accordion-item" data-nombre-producto="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                    <button class="product-accordion-header">
                        <img src="../<?php echo htmlspecialchars($producto['imagen_principal'] ?: 'imagenes/placeholder.png'); ?>" class="header-image" alt="">
                        <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                        <form action="gestionar_productos.php" method="POST" class="ms-auto" onclick="event.stopPropagation()">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $producto['activo']; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $producto['activo'] ? 'btn-success' : 'btn-danger'; ?>">
                                <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </button>
                        </form>
                    </button>
                    <div class="product-accordion-body">
                        <div class="body-content">
                            <p class="text-muted">
                                <strong>Categoría:</strong> <?php echo htmlspecialchars($producto['nombre_categoria']); ?><br>
                                <?php echo htmlspecialchars($producto['descripcion']); ?>
                            </p>
                            <a href="?edit_product_id=<?php echo $producto['id']; ?>#editor-panel" class="btn btn-secondary btn-sm">Editar Producto</a>
                            <hr>
                            <h6>Variantes:</h6>
                            <table class="table table-hover table-sm variantes-tabla">
                                <thead><tr><th>Imagen</th><th>Atributos</th><th>SKU</th><th>Precio</th><th>Stock</th><th></th></tr></thead>
                                <tbody>
                                    <?php
                                    // Se agrega `WHERE v.activo = 1` para solo mostrar variantes activas
                                    $stmt_variantes = $pdo->prepare("
                                        SELECT 
                                            v.*, 
                                            GROUP_CONCAT(o.valor ORDER BY a.nombre SEPARATOR ', ') as atributos_variante
                                        FROM variantes_producto v
                                        LEFT JOIN variante_opcion vo ON v.id_variante = vo.id_variante
                                        LEFT JOIN opciones o ON vo.id_opcion = o.id_opcion
                                        LEFT JOIN atributos a ON o.id_atributo = a.id_atributo
                                        WHERE v.id_producto = ? AND v.activo = 1
                                        GROUP BY v.id_variante
                                    ");
                                    $stmt_variantes->execute([$producto['id']]);
                                    $variantes = $stmt_variantes->fetchAll(PDO::FETCH_ASSOC);
                                    if (empty($variantes)) {
                                        echo '<tr><td colspan="6" class="text-center text-muted">No hay variantes para este producto.</td></tr>';
                                    } else {
                                        foreach ($variantes as $variante): ?>
                                        <tr>
                                            <td><img src="../<?php echo htmlspecialchars($variante['imagen'] ?: 'imagenes/placeholder.png'); ?>" class="variant-image" alt=""></td>
                                            <td><?php echo htmlspecialchars($variante['atributos_variante'] ?? 'Base'); ?></td>
                                            <td><?php echo htmlspecialchars($variante['sku']); ?></td>
                                            <td>$<?php echo number_format($variante['precio'], 0); ?></td>
                                            <td><?php echo $variante['stock']; ?></td>
                                            <td class="text-end">
                                                <a href="?edit_variant_id=<?php echo $variante['id_variante']; ?>#editor-panel" class="btn btn-warning btn-sm">Editar</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; 
                                    } ?>
                                </tbody>
                            </table>
                            <a href="?add_variant_for=<?php echo $producto['id']; ?>#editor-panel" class="btn btn-success btn-sm mt-2">
                                <i class="fas fa-plus"></i> Añadir Variante
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function filtrarProductos() {
    const input = document.getElementById('search-input');
    const filtro = input.value.toLowerCase();
    const acordeon = document.getElementById('product-accordion');
    const items = acordeon.getElementsByClassName('product-accordion-item');
    const mensajeVacio = document.getElementById('no-results-message');
    let resultadosVisibles = 0;

    for (let i = 0; i < items.length; i++) {
        const nombreProducto = items[i].dataset.nombreProducto;
        if (nombreProducto.includes(filtro)) {
            items[i].style.display = "";
            resultadosVisibles++;
        } else {
            items[i].style.display = "none";
        }
    }
    mensajeVacio.style.display = resultadosVisibles === 0 ? "block" : "none";
}

document.addEventListener('DOMContentLoaded', function () {
    const accordionButtons = document.querySelectorAll('.product-accordion-header');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            if (event.target.closest('form')) {
                return;
            }
            const accordionItem = this.closest('.product-accordion-item');
            const content = accordionItem.querySelector('.product-accordion-body');
            
            this.classList.toggle('active');
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>