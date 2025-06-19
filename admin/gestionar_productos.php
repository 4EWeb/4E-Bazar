<?php
// descomentar si es necesario
// require 'verificar_sesion.php'; 
session_start();
require '../db.php';

// ==================================================================
// "CEREBRO" - PROCESAMIENTO DE FORMULARIOS
// ==================================================================
// (Esta sección no tenía errores y permanece sin cambios)

// --- LÓGICA PARA CAMBIAR ESTADO ACTIVO/INACTIVO DIRECTAMENTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $id_producto_toggle = $_POST['id_producto'];
    $estado_actual = $_POST['current_status'];
    $nuevo_estado = $estado_actual == 1 ? 0 : 1;
    try {
        $stmt = $pdo->prepare("UPDATE productos SET activo = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id_producto_toggle]);
        $_SESSION['message'] = "Estado del producto actualizado.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al cambiar el estado: " . $e->getMessage();
    }
    header("Location: gestionar_productos.php?id_producto=" . $id_producto_toggle . "#heading-" . $id_producto_toggle);
    exit();
}

// --- LÓGICA PARA GUARDAR/ACTUALIZAR PRODUCTO PADRE (CON SUBIDA DE IMAGEN) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_product') {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoriaID = $_POST['categoriaID'];
    $activo = $_POST['activo'];
    $imagen_actual = $_POST['imagen_actual'] ?? '';
    $nombre_archivo_imagen = $imagen_actual;
    if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == UPLOAD_ERR_OK) {
        $directorio_destino = '../imagenes/productos/';
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        $nombre_archivo_imagen = uniqid('prod_') . '_' . basename($_FILES['imagen_principal']['name']);
        $ruta_completa = $directorio_destino . $nombre_archivo_imagen;
        if (!move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $ruta_completa)) {
            $_SESSION['error_message'] = "Hubo un error al subir la imagen.";
            $nombre_archivo_imagen = $imagen_actual;
        }
    }
    try {
        if (empty($id_producto)) {
            $sql = "INSERT INTO productos (nombre, descripcion, categoriaID, activo, imagen_principal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $categoriaID, $activo, $nombre_archivo_imagen]);
            $id_producto = $pdo->lastInsertId();
            $_SESSION['message'] = "Producto base creado exitosamente.";
        } else {
            $sql = "UPDATE productos SET nombre = ?, descripcion = ?, categoriaID = ?, activo = ?, imagen_principal = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $categoriaID, $activo, $nombre_archivo_imagen, $id_producto]);
            $_SESSION['message'] = "Producto base actualizado exitosamente.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error en la base de datos al guardar el producto: " . $e->getMessage();
    }
    header("Location: gestionar_productos.php?edit_product_id=" . $id_producto . "#product-editor");
    exit();
}

// --- LÓGICA PARA GUARDAR/ACTUALIZAR VARIANTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_variant') {
    $id_variante = $_POST['id_variante'];
    $id_producto = $_POST['id_producto'];
    $opciones = $_POST['opciones'] ?? [];
    $pdo->beginTransaction();
    try {
        if (empty($id_variante)) {
            $sql = "INSERT INTO variantes_producto (id_producto, sku, precio, stock, descuento, destacado) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_producto, $_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['descuento'], $_POST['destacado']]);
            $id_variante = $pdo->lastInsertId();
            $_SESSION['message'] = 'Variante creada exitosamente.';
        } else {
            $sql = "UPDATE variantes_producto SET sku = ?, precio = ?, stock = ?, descuento = ?, destacado = ? WHERE id_variante = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['descuento'], $_POST['destacado'], $id_variante]);
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
    header("Location: gestionar_productos.php?id_producto=" . $id_producto . "#heading-" . $id_producto);
    exit();
}

// --- LÓGICA PARA ELIMINAR PRODUCTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_product') {
    $id_producto_del = $_POST['id_producto'];
    $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$id_producto_del]);
    $_SESSION['message'] = 'Producto y sus variantes eliminados.';
    header("Location: gestionar_productos.php");
    exit();
}

// ==================================================================
// OBTENCIÓN DE DATOS PARA MOSTRAR EN LA PÁGINA
// ==================================================================
// CORREGIDO: Se cambió c.nombre por c.nombre_categoria para que coincida con tu base de datos.
$productos = $pdo->query("SELECT p.*, c.nombre_categoria FROM productos p LEFT JOIN categorias c ON p.categoriaID = c.id_categoria ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);

// CORREGIDO: Se cambió el ordenamiento por 'nombre' a 'nombre_categoria'.
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);
$atributos_query = $pdo->query("SELECT a.id_atributo, a.nombre, o.id_opcion, o.valor FROM atributos a JOIN opciones o ON a.id_atributo = o.id_atributo ORDER BY a.nombre, o.valor");
$atributos_con_opciones = [];
foreach ($atributos_query as $row) {
    $atributos_con_opciones[$row['id_atributo']]['nombre'] = $row['nombre'];
    $atributos_con_opciones[$row['id_atributo']]['opciones'][] = ['id_opcion' => $row['id_opcion'], 'valor' => $row['valor']];
}
$producto_a_editar = null;
$es_edicion = false;
$producto_activo_id = null;
if (isset($_GET['id_producto'])) {
    $producto_activo_id = $_GET['id_producto'];
}
if (isset($_GET['edit_product_id'])) {
    $es_edicion = true;
    $producto_activo_id = $_GET['edit_product_id'];
    $stmt_edit = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt_edit->execute([$producto_activo_id]);
    $producto_a_editar = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

include 'header.php'; // Tu header HTML
?>

<div class="container-fluid mt-4">
    <h1>Gestión Integral de Productos</h1>
    <p>Gestiona tus productos y todas sus variantes desde una única pantalla.</p>
    
    <?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

    <hr>

    <div class="main-container">

        <div class="editor-column">
            <div class="card" id="product-editor">
                <div class="card-header">
                    <h4><?php echo $es_edicion ? 'Editando Producto' : '+ Agregar Nuevo Producto'; ?></h4>
                </div>
                <div class="card-body">
                    <form action="gestionar_productos.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="save_product">
                        <input type="hidden" name="id_producto" value="<?php echo $producto_a_editar['id'] ?? ''; ?>">
                        <input type="hidden" name="imagen_actual" value="<?php echo $producto_a_editar['imagen_principal'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto_a_editar['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($producto_a_editar['descripcion'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoriaID" class="form-label">Categoría</label>
                                <select id="categoriaID" name="categoriaID" class="form-select" required>
                                    <option value="">Elige...</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?php echo $c['id_categoria']; ?>" <?php echo (isset($producto_a_editar) && $producto_a_editar['categoriaID'] == $c['id_categoria']) ? 'selected' : ''; ?>>
                                            <?php // CORREGIDO: Se cambió $c['nombre'] por $c['nombre_categoria'] ?>
                                            <?php echo htmlspecialchars($c['nombre_categoria']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="activo" class="form-label">Estado</label>
                                <select id="activo" name="activo" class="form-select">
                                    <option value="1" <?php echo (!isset($producto_a_editar) || $producto_a_editar['activo'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo (isset($producto_a_editar) && $producto_a_editar['activo'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="imagen_principal" class="form-label">Imagen Principal</label>
                            <input type="file" id="imagen_principal" name="imagen_principal" class="form-control" accept="image/*">
                            <?php if ($es_edicion && !empty($producto_a_editar['imagen_principal'])): ?>
                                <div class="mt-2">
                                    <small>Imagen actual:</small><br>
                                    <img src="../imagenes/productos/<?php echo htmlspecialchars($producto_a_editar['imagen_principal']); ?>" alt="Imagen actual" style="max-width: 100px; border-radius: 5px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo $es_edicion ? 'Actualizar Producto' : 'Guardar Nuevo Producto'; ?></button>
                            <?php if ($es_edicion): ?>
                                <a href="gestionar_productos.php" class="btn btn-secondary">Limpiar Editor</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="products-column">
            <div class="accordion" id="accordion-productos">
                <?php if (empty($productos)): ?>
                    <div class="alert alert-info">Aún no has creado ningún producto. Usa el panel de la izquierda para empezar.</div>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <?php
                            $is_active_accordion = ($producto_activo_id == $producto['id']);
                            $button_class = $is_active_accordion ? '' : 'collapsed';
                            $collapse_class = $is_active_accordion ? 'show' : '';
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?php echo $producto['id']; ?>">
                                <button class="accordion-button <?php echo $button_class; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $producto['id']; ?>" aria-expanded="<?php echo $is_active_accordion ? 'true' : 'false'; ?>" aria-controls="collapse-<?php echo $producto['id']; ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="d-flex align-items-center" style="min-width: 0; flex-shrink: 1;">
                                            <img src="../imagenes/productos/<?php echo htmlspecialchars($producto['imagen_principal'] ?: 'default.png'); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                                            <strong class="text-truncate"><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                        </div>
                                        <form action="gestionar_productos.php" method="POST" class="d-flex align-items-center" onclick="event.stopPropagation();">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $producto['activo']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $producto['activo'] ? 'btn-success' : 'btn-secondary'; ?>" title="Click para cambiar estado">
                                                <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-<?php echo $producto['id']; ?>" class="accordion-collapse collapse <?php echo $collapse_class; ?>" aria-labelledby="heading-<?php echo $producto['id']; ?>" data-bs-parent="#accordion-productos">
                                <div class="accordion-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['nombre_categoria']); ?></p>
                                            <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                                        </div>
                                        <div class="ms-3 text-nowrap">
                                            <a href="?edit_product_id=<?php echo $producto['id']; ?>#product-editor" class="btn btn-primary mb-2 w-100">Editar Producto</a>
                                            <form action="gestionar_productos.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto y todas sus variantes?');">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                                <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Variantes de "<?php echo htmlspecialchars($producto['nombre']); ?>"</h5>
                                    <table class="table table-sm table-hover table-striped">
                                        <thead><tr><th>Variante (Atributos)</th><th>SKU</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                            <?php
                                            $stmt_variantes = $pdo->prepare("SELECT v.*, GROUP_CONCAT(o.valor ORDER BY a.nombre SEPARATOR ', ') as atributos FROM variantes_producto v LEFT JOIN variante_opcion vo ON v.id_variante = vo.id_variante LEFT JOIN opciones o ON vo.id_opcion = o.id_opcion LEFT JOIN atributos a ON o.id_atributo = a.id_atributo WHERE v.id_producto = ? GROUP BY v.id_variante");
                                            $stmt_variantes->execute([$producto['id']]);
                                            $variantes = $stmt_variantes->fetchAll();
                                            foreach ($variantes as $variante): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($variante['atributos'] ?? 'Base'); ?></strong></td>
                                                <td><?php echo htmlspecialchars($variante['sku']); ?></td>
                                                <td>$<?php echo number_format($variante['precio'], 0, ',', '.'); ?></td>
                                                <td><?php echo $variante['stock']; ?></td>
                                                <td><a href="?edit_variant_id=<?php echo $variante['id_variante']; ?>&id_producto=<?php echo $producto['id']; ?>#form-variant-<?php echo $producto['id']; ?>" class="btn btn-warning btn-sm">Editar</a></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($variantes)): ?>
                                                <tr><td colspan="5" class="text-center">Aún no hay variantes para este producto.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <hr class="my-4">
                                    <?php 
                                    $variant_to_edit = null;
                                    if (isset($_GET['edit_variant_id']) && isset($_GET['id_producto']) && $_GET['id_producto'] == $producto['id']) {
                                        $stmt_edit_var = $pdo->prepare("SELECT * FROM variantes_producto WHERE id_variante = ?");
                                        $stmt_edit_var->execute([$_GET['edit_variant_id']]);
                                        $variant_to_edit = $stmt_edit_var->fetch();
                                        if($variant_to_edit){
                                            $stmt_opts = $pdo->prepare("SELECT id_opcion FROM variante_opcion WHERE id_variante=?");
                                            $stmt_opts->execute([$_GET['edit_variant_id']]);
                                            $variant_to_edit['opciones'] = $stmt_opts->fetchAll(PDO::FETCH_COLUMN);
                                        }
                                    }
                                    ?>
                                    <h5 id="form-variant-<?php echo $producto['id']; ?>"><?php echo $variant_to_edit ? 'Editando Variante' : 'Agregar Nueva Variante'; ?></h5>
                                    <form action="gestionar_productos.php" method="POST">
                                        <input type="hidden" name="action" value="save_variant">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                        <input type="hidden" name="id_variante" value="<?php echo $variant_to_edit['id_variante'] ?? ''; ?>">
                                        <div class="row p-2 bg-light border rounded mb-3">
                                            <p class="mb-1 fw-bold">Atributos de la Variante</p>
                                            <small class="text-muted mb-2">Para cada atributo, selecciona una opción. Si un atributo no aplica, déjalo en "N/A".</small>
                                            <?php foreach ($atributos_con_opciones as $id_attr => $attr): ?>
                                            <div class="col-md-4 mb-2"><label class="form-label"><?php echo htmlspecialchars($attr['nombre']); ?></label>
                                                <select name="opciones[]" class="form-select form-select-sm">
                                                    <option value="">N/A</option>
                                                    <?php foreach ($attr['opciones'] as $opt): ?>
                                                        <option value="<?php echo $opt['id_opcion']; ?>" <?php echo (isset($variant_to_edit['opciones']) && in_array($opt['id_opcion'], $variant_to_edit['opciones'])) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($opt['valor']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4"><label>SKU</label><input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($variant_to_edit['sku'] ?? ''); ?>"></div>
                                            <div class="col-md-4"><label>Precio</label><input type="number" step="any" name="precio" class="form-control" value="<?php echo $variant_to_edit['precio'] ?? ''; ?>" required></div>
                                            <div class="col-md-4"><label>Stock</label><input type="number" name="stock" class="form-control" value="<?php echo $variant_to_edit['stock'] ?? '0'; ?>" required></div>
                                            <div class="col-md-6"><label>Descuento (%)</label><input type="number" name="descuento" class="form-control" value="<?php echo $variant_to_edit['descuento'] ?? '0'; ?>"></div>
                                            <div class="col-md-6"><label>Destacado</label><select name="destacado" class="form-select"><option value="0" <?php echo (!isset($variant_to_edit) || $variant_to_edit['destacado']==0) ? 'selected' : ''; ?>>No</option><option value="1" <?php echo (isset($variant_to_edit) && $variant_to_edit['destacado']==1) ? 'selected' : ''; ?>>Sí</option></select></div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary"><?php echo $variant_to_edit ? 'Actualizar Variante' : 'Agregar Variante'; ?></button>
                                            <?php if ($variant_to_edit): ?><a href="gestionar_productos.php?id_producto=<?php echo $producto['id']; ?>" class="btn btn-secondary">Cancelar Edición</a><?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; // Tu footer HTML ?>