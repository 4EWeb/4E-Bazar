<?php
require 'admin_functions.php';

// Procesar cualquier formulario enviado a esta página
handle_product_requests($pdo);

// Obtener datos para mostrar
$productos = get_all_products_with_category($pdo);
$categorias = get_all_categories($pdo);
$atributos_con_opciones = get_attributes_with_options($pdo);

// Determinar si estamos editando y qué producto
$producto_activo_id = $_GET['id_producto'] ?? $_GET['edit_product_id'] ?? null;
$es_edicion = isset($_GET['edit_product_id']);
$producto_a_editar = get_product_to_edit($pdo, $_GET['edit_product_id'] ?? null);

include 'header.php';
?>

<div class="page-header">
    <h1>Gestión Integral de Productos</h1>
    <p>Gestiona tus productos y todas sus variantes desde una única pantalla.</p>
</div>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<hr>

<div class="main-layout">
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
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo htmlspecialchars($producto_a_editar['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-textarea" rows="3"><?php echo htmlspecialchars($producto_a_editar['descripcion'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                             <label for="categoriaID" class="form-label">Categoría</label>
                            <select id="categoriaID" name="categoriaID" class="form-select" required>
                                <option value="">Elige...</option>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?php echo $c['id_categoria']; ?>" <?php echo (isset($producto_a_editar) && $producto_a_editar['categoriaID'] == $c['id_categoria']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nombre_categoria']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="activo" class="form-label">Estado</label>
                            <select id="activo" name="activo" class="form-select">
                                <option value="1" <?php echo (!isset($producto_a_editar) || $producto_a_editar['activo'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo (isset($producto_a_editar) && $producto_a_editar['activo'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="imagen_principal" class="form-label">Imagen Principal</label>
                        <input type="file" id="imagen_principal" name="imagen_principal" class="form-input" accept="image/*">
                        <?php if ($es_edicion && !empty($producto_a_editar['imagen_principal'])): ?>
                            <div style="margin-top: 10px;">
                                <small>Imagen actual:</small><br>
                                <img src="../imagenes/productos/<?php echo htmlspecialchars($producto_a_editar['imagen_principal']); ?>" alt="Imagen actual" style="max-width: 100px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?php echo $es_edicion ? 'Actualizar Producto' : 'Guardar Nuevo Producto'; ?></button>
                        <?php if ($es_edicion): ?>
                            <a href="gestionar_productos.php" class="btn btn-secondary">Limpiar Editor</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="content-column">
        <div class="accordion">
            <?php if (empty($productos)): ?>
                <div class="alert alert-info">Aún no has creado ningún producto. Usa el panel de la izquierda para empezar.</div>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <?php
                        $is_active_accordion = ($producto_activo_id == $producto['id']);
                        $button_class = $is_active_accordion ? '' : 'collapsed';
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo $producto['id']; ?>">
                            <button class="accordion-button <?php echo $button_class; ?>" type="button" data-target="#collapse-<?php echo $producto['id']; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center" style="min-width: 0; flex-shrink: 1;">
                                        <img src="../imagenes/productos/<?php echo htmlspecialchars($producto['imagen_principal'] ?: 'default.png'); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                                        <strong style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    </div>
                                    <form action="gestionar_productos.php" method="POST" class="d-flex align-items-center" onclick="event.stopPropagation();">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $producto['activo']; ?>">
                                        <button type="submit" class="btn <?php echo $producto['activo'] ? 'btn-success' : 'btn-secondary'; ?>" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Click para cambiar estado">
                                            <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </button>
                                    </form>
                                </div>
                            </button>
                        </h2>
                        
                        <div id="collapse-<?php echo $producto['id']; ?>" class="accordion-collapse" <?php if ($is_active_accordion) echo 'data-show="true"'; ?>>
                            <div class="accordion-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['nombre_categoria']); ?></p>
                                        <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                                    </div>
                                    <div class="ms-3" style="text-align: right; min-width: 120px;">
                                        <a href="?edit_product_id=<?php echo $producto['id']; ?>#product-editor" class="btn btn-warning w-100 mb-2">Editar</a>
                                        <form action="gestionar_productos.php" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este producto?');">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                                <hr>
                                <h5>Variantes de "<?php echo htmlspecialchars($producto['nombre']); ?>"</h5>
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark"><tr><th>Variante</th><th>SKU</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
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
                                            <td><a href="?edit_variant_id=<?php echo $variante['id_variante']; ?>&id_producto=<?php echo $producto['id']; ?>#form-variant-<?php echo $producto['id']; ?>" class="btn btn-warning" style="padding: 0.2rem 0.4rem; font-size: 0.8rem;">Editar</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($variantes)): ?>
                                            <tr><td colspan="5" class="text-center">Aún no hay variantes.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <hr class="my-4">
                                <?php 
                                $variant_to_edit = null;
                                if (isset($_GET['edit_variant_id']) && $_GET['id_producto'] == $producto['id']) {
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
                                    <div class="row" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 1rem 0.5rem; margin-bottom: 1rem;">
                                        <p class="mb-1 fw-bold">Atributos de la Variante</p>
                                        <?php foreach ($atributos_con_opciones as $id_attr => $attr): ?>
                                        <div class="col-4"><label class="form-label"><?php echo htmlspecialchars($attr['nombre']); ?></label>
                                            <select name="opciones[]" class="form-select" style="font-size: 0.9rem; padding: 0.3rem 0.5rem;">
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
                                    <div class="row">
                                        <div class="col-4"><label class="form-label">SKU</label><input type="text" name="sku" class="form-input" value="<?php echo htmlspecialchars($variant_to_edit['sku'] ?? ''); ?>"></div>
                                        <div class="col-4"><label class="form-label">Precio</label><input type="number" step="any" name="precio" class="form-input" value="<?php echo $variant_to_edit['precio'] ?? ''; ?>" required></div>
                                        <div class="col-4"><label class="form-label">Stock</label><input type="number" name="stock" class="form-input" value="<?php echo $variant_to_edit['stock'] ?? '0'; ?>" required></div>
                                        <div class="col-6"><label class="form-label">Descuento (%)</label><input type="number" name="descuento" class="form-input" value="<?php echo $variant_to_edit['descuento'] ?? '0'; ?>"></div>
                                        <div class="col-6"><label class="form-label">Destacado</label><select name="destacado" class="form-select"><option value="0" <?php echo (!isset($variant_to_edit) || $variant_to_edit['destacado']==0) ? 'selected' : ''; ?>>No</option><option value="1" <?php echo (isset($variant_to_edit) && $variant_to_edit['destacado']==1) ? 'selected' : ''; ?>>Sí</option></select></div>
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

<?php include 'footer.php'; ?>