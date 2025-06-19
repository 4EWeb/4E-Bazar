<?php
//require 'verificar_sesion.php';
require '../db.php';

// --- LÓGICA DE PROCESAMIENTO DE FORMULARIOS ---

// Acción para Guardar (Crear o Actualizar) un Box
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_box'])) {
    $id_promo = $_POST['id_promo'];
    $nombre = $_POST['nombre_promo'];
    $descripcion = $_POST['descripcion_promo'];
    $precio_promo = $_POST['precio_promo'];
    $fecha_termino = !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;
    $items = isset($_POST['items']) ? $_POST['items'] : [];

    $valor_total_items = 0.00;
    if (!empty($items)) {
        $sql_precio_item = "SELECT precio FROM variantes_producto WHERE id_variante = ?";
        $stmt_precio_item = $pdo->prepare($sql_precio_item);
        foreach ($items as $id_variante => $cantidad) {
            $stmt_precio_item->execute([$id_variante]);
            $precio_item = $stmt_precio_item->fetchColumn();
            if ($precio_item) {
                $valor_total_items += $precio_item * $cantidad;
            }
        }
    }

    $pdo->beginTransaction();
    try {
        if (empty($id_promo)) {
            $sql = "INSERT INTO promociones (nombre_promo, descripcion_promo, precio_promo, valor_total_items, fecha_termino) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $precio_promo, $valor_total_items, $fecha_termino]);
            $id_promo = $pdo->lastInsertId();
        } else {
            $sql = "UPDATE promociones SET nombre_promo = ?, descripcion_promo = ?, precio_promo = ?, valor_total_items = ?, fecha_termino = ? WHERE id_promo = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $precio_promo, $valor_total_items, $fecha_termino, $id_promo]);
            $pdo->prepare("DELETE FROM promocion_items WHERE id_promo = ?")->execute([$id_promo]);
        }
        $stmt_item = $pdo->prepare("INSERT INTO promocion_items (id_promo, id_variante, cantidad) VALUES (?, ?, ?)");
        foreach ($items as $id_variante => $cantidad) {
            $stmt_item->execute([$id_promo, $id_variante, $cantidad]);
        }
        $pdo->commit();
        $_SESSION['message'] = "Box de promoción guardado exitosamente.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al guardar el box: " . $e->getMessage();
    }
    header("Location: gestionar_box.php");
    exit();
}


// =============================================================
// --- INICIO DE LA CORRECCIÓN: Lógica para Eliminar un Box ---
// =============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_box'])) {
    $id_promo_a_eliminar = $_POST['id_promo'];

    if (!empty($id_promo_a_eliminar) && is_numeric($id_promo_a_eliminar)) {
        try {
            // Gracias a 'ON DELETE CASCADE' en la base de datos, 
            // al eliminar una promoción, se eliminarán sus items asociados.
            $sql = "DELETE FROM promociones WHERE id_promo = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_promo_a_eliminar]);
            $_SESSION['message'] = "Box de promoción #" . htmlspecialchars($id_promo_a_eliminar) . " ha sido eliminado.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al eliminar el box: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "ID de box no válido para eliminar.";
    }

    header("Location: gestionar_box.php");
    exit();
}
// =============================================================
// --- FIN DE LA CORRECCIÓN ---
// =============================================================


// --- OBTENCIÓN DE DATOS PARA MOSTRAR ---
$boxes_query = $pdo->query("SELECT p.id_promo, p.nombre_promo, p.precio_promo, p.fecha_termino, pi.cantidad, v.sku, v.precio as precio_variante, prod.nombre as nombre_producto FROM promociones p LEFT JOIN promocion_items pi ON p.id_promo = pi.id_promo LEFT JOIN variantes_producto v ON pi.id_variante = v.id_variante LEFT JOIN productos prod ON v.id_producto = prod.id ORDER BY p.id_promo, prod.nombre")->fetchAll(PDO::FETCH_GROUP);
$variantes_disponibles = $pdo->query("SELECT v.id_variante, v.sku, v.precio, p.nombre FROM variantes_producto v JOIN productos p ON v.id_producto = p.id WHERE v.stock > 0 ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
$box_a_editar = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM promociones WHERE id_promo = ?");
    $stmt->execute([$_GET['edit_id']]);
    $box_a_editar = $stmt->fetch();
    if ($box_a_editar) {
        $stmt_items = $pdo->prepare("SELECT pi.cantidad, v.id_variante, p.nombre, v.sku FROM promocion_items pi JOIN variantes_producto v ON pi.id_variante = v.id_variante JOIN productos p ON v.id_producto = p.id WHERE pi.id_promo = ?");
        $stmt_items->execute([$_GET['edit_id']]);
        $box_a_editar['items'] = $stmt_items->fetchAll();
    }
}
include 'header.php';
?>

<h1>Gestión de Boxes de Promoción</h1>
<p>Crea, visualiza y edita tus cajas promocionales. Selecciona productos existentes para armar tus combos.</p>
<hr>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <form action="gestionar_box.php" method="POST" class="sticky-top" style="top: 20px;">
            <div class="card">
                <div class="card-header"><h4><?php echo $box_a_editar ? 'Editando Box' : 'Crear Nuevo Box'; ?></h4></div>
                <div class="card-body">
                    <input type="hidden" name="id_promo" value="<?php echo $box_a_editar['id_promo'] ?? ''; ?>">
                    <div class="form-group mb-3"><label>Nombre del Box</label><input type="text" name="nombre_promo" class="form-control" value="<?php echo htmlspecialchars($box_a_editar['nombre_promo'] ?? ''); ?>" required></div>
                    <div class="form-group mb-3"><label>Descripción del Box</label><textarea name="descripcion_promo" class="form-control" rows="3"><?php echo htmlspecialchars($box_a_editar['descripcion_promo'] ?? ''); ?></textarea></div>
                    <div class="form-group mb-3"><label>Disponible hasta (Opcional)</label><input type="datetime-local" name="fecha_termino" class="form-control" value="<?php echo !empty($box_a_editar['fecha_termino']) ? date('Y-m-d\TH:i', strtotime($box_a_editar['fecha_termino'])) : ''; ?>"></div>
                    <hr>
                    <h5>Items del Box</h5>
                    <ul id="items-list" class="list-group mb-3">
                        <?php if ($box_a_editar && !empty($box_a_editar['items'])): foreach ($box_a_editar['items'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><span><?php echo htmlspecialchars($item['nombre'] . ' (' . $item['sku'] . ') x' . $item['cantidad']); ?></span><div><input type="hidden" class="item-data" name="items[<?php echo $item['id_variante']; ?>]" value="<?php echo $item['cantidad']; ?>" data-variant-id="<?php echo $item['id_variante']; ?>"><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></div></li>
                        <?php endforeach; endif; ?>
                    </ul>
                    <div class="input-group"><select id="variant-select" class="form-select"><option value="">Selecciona un producto...</option><?php foreach ($variantes_disponibles as $variante): ?><option value="<?php echo $variante['id_variante']; ?>" data-text="<?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?>"><?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?></option><?php endforeach; ?></select><input type="number" id="item-quantity" class="form-control" value="1" min="1" style="max-width: 80px;"><button type="button" id="add-item-btn" class="btn btn-success">Añadir</button></div>
                    <hr>
                    <div class="p-3 bg-light rounded mt-3"><h5>Cálculo de Precios</h5><p class="mb-1">Valor real de los items: <strong id="display-valor-real" class="fs-5">$0</strong></p><small class="text-muted">Esta es la suma del precio de cada producto individual.</small></div>
                    <div class="form-group mb-3 mt-3"><label class="fw-bold">Precio Final de Venta del Box</label><input type="number" step="0.01" name="precio_promo" class="form-control form-control-lg" value="<?php echo $box_a_editar['precio_promo'] ?? ''; ?>" required></div>
                </div>
                <div class="card-footer text-end"><a href="gestionar_box.php" class="btn btn-secondary">Cancelar</a><button type="submit" name="save_box" class="btn btn-primary"><?php echo $box_a_editar ? 'Actualizar Box' : 'Crear Box'; ?></button></div>
            </div>
        </form>
    </div>

    <div class="col-lg-7">
        <h4>Boxes Existentes</h4>
        <div class="kits-container">
            <?php foreach ($boxes_query as $id => $items): $box = $items[0]; ?>
            <div class="kit-box">
                <h3><?php echo htmlspecialchars($box['nombre_promo']); ?></h3>
                <?php if ($box['fecha_termino']): ?><small class="text-center text-danger mb-2">Válido hasta: <?php echo date('d/m/Y H:i', strtotime($box['fecha_termino'])); ?></small><?php endif; ?>
                <ul class="product-list">
                    <?php if ($box['sku']): foreach($items as $item): ?>
                        <li><?php echo htmlspecialchars($item['nombre_producto'] . ' (' . $item['sku'] . ')'); ?> (x<?php echo $item['cantidad']; ?>) - <strong>$<?php echo number_format($item['precio_variante'], 0); ?> c/u</strong></li>
                    <?php endforeach; else: ?>
                        <li>Este box aún no tiene productos.</li>
                    <?php endif; ?>
                </ul>
                <div class="kit-footer"><div class="kit-price-label">Precio de Venta</div><div class="kit-price">$<?php echo number_format($box['precio_promo'], 0, ',', '.'); ?></div><a href="?edit_id=<?php echo $id; ?>" class="btn btn-warning btn-sm">Editar</a><form action="gestionar_box.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que quieres eliminar este box?');"><input type="hidden" name="id_promo" value="<?php echo $id; ?>"><button type="submit" name="delete_box" class="btn btn-danger btn-sm">Eliminar</button></form></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const variantsData = <?php echo json_encode(array_column($variantes_disponibles, null, 'id_variante')); ?>;
    const addItemBtn = document.getElementById('add-item-btn');
    const variantSelect = document.getElementById('variant-select');
    const quantityInput = document.getElementById('item-quantity');
    const itemsList = document.getElementById('items-list');
    const displayValorReal = document.getElementById('display-valor-real');
    function updateTotals() {
        let total = 0;
        const currentItems = itemsList.querySelectorAll('.item-data');
        currentItems.forEach(item => {
            const variantId = item.getAttribute('data-variant-id');
            const quantity = parseInt(item.value, 10);
            const variantInfo = variantsData[variantId];
            if (variantInfo) { total += parseFloat(variantInfo.precio) * quantity; }
        });
        displayValorReal.textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(total);
    }
    addItemBtn.addEventListener('click', function() {
        const selectedOption = variantSelect.options[variantSelect.selectedIndex];
        if (!selectedOption.value) { alert('Por favor, selecciona un producto.'); return; }
        const variantId = selectedOption.value;
        if (document.querySelector(`input[name="items[${variantId}]"]`)) { alert('Este producto ya está en el box.'); return; }
        const variantText = selectedOption.getAttribute('data-text');
        const quantity = quantityInput.value;
        const variantInfo = variantsData[variantId];
        const formattedPrice = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(variantInfo.precio);
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `<span>${variantText} x${quantity} - <strong class="text-success">${formattedPrice} c/u</strong></span><div><input type="hidden" class="item-data" name="items[${variantId}]" value="${quantity}" data-variant-id="${variantId}"><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></div>`;
        itemsList.appendChild(li);
        updateTotals();
    });
    itemsList.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item-btn')) {
            e.target.closest('li').remove();
            updateTotals();
        }
    });
    updateTotals();
});
</script>

<?php include 'footer.php'; ?>