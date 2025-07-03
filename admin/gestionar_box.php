<?php
// admin/gestionar_box.php (Código completo y mejorado)

//require 'verificar_sesion.php';
require '../db.php';

// --- LÓGICA DE PROCESAMIENTO DE FORMULARIOS ---

// Acción para Guardar (Crear o Actualizar) un Box
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_box'])) {
    $id_promo = $_POST['id_promo'];
    $nombre = $_POST['nombre_promo'];
    $descripcion = $_POST['descripcion_promo'];
    $precio_promo = $_POST['precio_promo'];
    $activa = $_POST['activa']; // <-- NUEVO CAMPO
    $fecha_termino = !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;
    $items = isset($_POST['items']) ? $_POST['items'] : [];

    $valor_total_items = 0.00;
    if (!empty($items)) {
        $placeholders = implode(',', array_fill(0, count(array_keys($items)), '?'));
        $sql_precios = "SELECT id_variante, precio FROM variantes_producto WHERE id_variante IN ($placeholders)";
        $stmt_precios = $pdo->prepare($sql_precios);
        $stmt_precios->execute(array_keys($items));
        $precios_variantes = $stmt_precios->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($items as $id_variante => $cantidad) {
            if (isset($precios_variantes[$id_variante])) {
                $valor_total_items += $precios_variantes[$id_variante] * $cantidad;
            }
        }
    }

    $pdo->beginTransaction();
    try {
        if (empty($id_promo)) {
            // Se incluye `activa` en la inserción
            $sql = "INSERT INTO promociones (nombre_promo, descripcion_promo, precio_promo, valor_total_items, fecha_termino, activa) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $precio_promo, $valor_total_items, $fecha_termino, $activa]);
            $id_promo = $pdo->lastInsertId();
        } else {
            // Se incluye `activa` en la actualización
            $sql = "UPDATE promociones SET nombre_promo = ?, descripcion_promo = ?, precio_promo = ?, valor_total_items = ?, fecha_termino = ?, activa = ? WHERE id_promo = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $precio_promo, $valor_total_items, $fecha_termino, $activa, $id_promo]);
            $pdo->prepare("DELETE FROM promocion_items WHERE id_promo = ?")->execute([$id_promo]);
        }

        if (!empty($items)) {
            $stmt_item = $pdo->prepare("INSERT INTO promocion_items (id_promo, id_variante, cantidad) VALUES (?, ?, ?)");
            foreach ($items as $id_variante => $cantidad) {
                $stmt_item->execute([$id_promo, $id_variante, (int)$cantidad]);
            }
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
// Lógica para Eliminar un Box (ya la tenías, la mantenemos)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_box'])) {
    $id_promo_a_eliminar = $_POST['id_promo'];
    if (!empty($id_promo_a_eliminar) && is_numeric($id_promo_a_eliminar)) {
        $stmt = $pdo->prepare("DELETE FROM promociones WHERE id_promo = ?");
        $stmt->execute([$id_promo_a_eliminar]);
        $_SESSION['message'] = "Box de promoción #" . htmlspecialchars($id_promo_a_eliminar) . " ha sido eliminado.";
    }
    header("Location: gestionar_box.php");
    exit();
}

// --- OBTENCIÓN DE DATOS PARA MOSTRAR ---
$boxes = $pdo->query("SELECT * FROM promociones ORDER BY id_promo DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($boxes as $key => $box) {
    $stmt_items = $pdo->prepare("SELECT pi.cantidad, p.nombre, v.sku FROM promocion_items pi JOIN variantes_producto v ON pi.id_variante = v.id_variante JOIN productos p ON v.id_producto = p.id WHERE pi.id_promo = ?");
    $stmt_items->execute([$box['id_promo']]);
    $boxes[$key]['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
}

$variantes_disponibles = $pdo->query("SELECT v.id_variante, v.precio, p.nombre, v.sku FROM variantes_producto v JOIN productos p ON v.id_producto = p.id WHERE v.stock > 0 ORDER BY p.nombre")->fetchAll(PDO::FETCH_ASSOC);
$box_a_editar = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM promociones WHERE id_promo = ?");
    $stmt->execute([$_GET['edit_id']]);
    $box_a_editar = $stmt->fetch();
    if ($box_a_editar) {
        $stmt_items = $pdo->prepare("SELECT pi.cantidad, v.id_variante, p.nombre, v.sku FROM promocion_items pi JOIN variantes_producto v ON pi.id_variante = v.id_variante JOIN productos p ON v.id_producto = p.id WHERE pi.id_promo = ?");
        $stmt_items->execute([$_GET['edit_id']]);
        $box_a_editar['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
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
                    <div class="mb-3"><label class="form-label">Nombre del Box</label><input type="text" name="nombre_promo" class="form-control" value="<?php echo htmlspecialchars($box_a_editar['nombre_promo'] ?? ''); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion_promo" class="form-control" rows="2"><?php echo htmlspecialchars($box_a_editar['descripcion_promo'] ?? ''); ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Disponible hasta (Opcional)</label><input type="datetime-local" name="fecha_termino" class="form-control" value="<?php echo !empty($box_a_editar['fecha_termino']) ? date('Y-m-d\TH:i', strtotime($box_a_editar['fecha_termino'])) : ''; ?>"></div>
                    <hr>
                    <h5>Items del Box</h5>
                    <div class="input-group mb-3"><select id="variant-select" class="form-select"><option value="">Selecciona un producto...</option><?php foreach ($variantes_disponibles as $variante): ?><option value="<?php echo $variante['id_variante']; ?>" data-text="<?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?>"><?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?></option><?php endforeach; ?></select><input type="number" id="item-quantity" class="form-control" value="1" min="1" style="flex-grow: 0.2;"><button type="button" id="add-item-btn" class="btn btn-success">Añadir</button></div>
                    <ul id="items-list" class="list-group mb-3">
                        <?php if ($box_a_editar && !empty($box_a_editar['items'])): foreach ($box_a_editar['items'] as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-variant-id="<?php echo $item['id_variante']; ?>" data-quantity="<?php echo $item['cantidad']; ?>"><span><?php echo htmlspecialchars($item['nombre'] . ' (' . $item['sku'] . ')'); ?> x <?php echo $item['cantidad']; ?></span><div><input type="hidden" name="items[<?php echo $item['id_variante']; ?>]" value="<?php echo $item['cantidad']; ?>"><button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button></div></li>
                        <?php endforeach; endif; ?>
                    </ul>
                    <hr>
                    <div class="p-3 bg-light rounded mt-3">
                        <div class="d-flex justify-content-between">
                            <span>Valor real de los items:</span>
                            <strong id="display-valor-real" class="fs-5">$0</strong>
                        </div>
                        <small class="text-muted">Suma del precio normal de cada producto.</small>
                    </div>
                    <div class="mt-3"><label class="form-label fw-bold">Precio Final de Venta del Box (con descuento):</label><input type="number" step="0.01" name="precio_promo" class="form-control form-control-lg" value="<?php echo $box_a_editar['precio_promo'] ?? ''; ?>" required></div>
                </div>
                <div class="card-footer text-end"><a href="gestionar_box.php" class="btn btn-secondary">Cancelar</a><button type="submit" name="save_box" class="btn btn-primary"><?php echo $box_a_editar ? 'Actualizar Box' : 'Guardar Box'; ?></button></div>
            </div>
        </form>
    </div>

    <div class="col-lg-7">
        <h4>Boxes Existentes</h4>
        <?php foreach ($boxes as $box): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title"><?php echo htmlspecialchars($box['nombre_promo']); ?></h5>
                    <div>
                        <a href="?edit_id=<?php echo $box['id_promo']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <form action="gestionar_box.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que quieres eliminar este box?');"><input type="hidden" name="id_promo" value="<?php echo $box['id_promo']; ?>"><button type="submit" name="delete_box" class="btn btn-danger btn-sm">Eliminar</button></form>
                    </div>
                </div>
                <p><?php echo htmlspecialchars($box['descripcion_promo']); ?></p>
                <ul><?php foreach ($box['items'] as $item): ?><li><?php echo htmlspecialchars($item['nombre'] . ' (' . $item['sku'] . ')'); ?> x <?php echo $item['cantidad']; ?></li><?php endforeach; ?></ul>
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                    <div>
                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($box['valor_total_items'], 0, ',', '.'); ?></span>
                        <strong class="text-success fs-4 ms-2">$<?php echo number_format($box['precio_promo'], 0, ',', '.'); ?></strong>
                    </div>
                    <?php if ($box['fecha_termino']): ?><small class="text-danger">Válido hasta: <?php echo date('d/m/Y', strtotime($box['fecha_termino'])); ?></small><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convertimos los datos de PHP a un objeto JS para un acceso rápido
    const variantsData = <?php echo json_encode(array_column($variantes_disponibles, null, 'id_variante')); ?>;
    
    const addItemBtn = document.getElementById('add-item-btn');
    const variantSelect = document.getElementById('variant-select');
    const quantityInput = document.getElementById('item-quantity');
    const itemsList = document.getElementById('items-list');
    const displayValorReal = document.getElementById('display-valor-real');

    function updateTotals() {
        let total = 0;
        // Recorremos los <li> de la lista para calcular el total
        const currentItems = itemsList.querySelectorAll('li');
        currentItems.forEach(item => {
            const variantId = item.dataset.variantId;
            const quantity = parseInt(item.dataset.quantity, 10);
            const variantInfo = variantsData[variantId];
            if (variantInfo) {
                total += parseFloat(variantInfo.precio) * quantity;
            }
        });
        displayValorReal.textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(total);
    }

    addItemBtn.addEventListener('click', function() {
        const selectedOption = variantSelect.options[variantSelect.selectedIndex];
        if (!selectedOption.value) {
            alert('Por favor, selecciona un producto.');
            return;
        }
        const variantId = selectedOption.value;
        const quantity = parseInt(quantityInput.value, 10);
        
        // Verificar si el item ya está en la lista
        if (itemsList.querySelector(`li[data-variant-id="${variantId}"]`)) {
            alert('Este producto ya está en el box. Puedes removerlo y volver a agregarlo con otra cantidad.');
            return;
        }

        const variantText = selectedOption.getAttribute('data-text');
        
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.dataset.variantId = variantId;
        li.dataset.quantity = quantity;
        li.innerHTML = `
            <span>${variantText} x ${quantity}</span>
            <div>
                <input type="hidden" name="items[${variantId}]" value="${quantity}">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button>
            </div>
        `;
        itemsList.appendChild(li);
        
        // Limpiar inputs y recalcular
        variantSelect.value = '';
        quantityInput.value = '1';
        updateTotals();
    });

    itemsList.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item-btn')) {
            e.target.closest('li').remove();
            updateTotals();
        }
    });

    // Calcular totales al cargar la página (para el modo de edición)
    updateTotals();
});
</script>

<?php include 'footer.php'; ?>