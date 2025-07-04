<?php
require 'admin_functions.php';

// Procesar formularios
handle_box_requests($pdo);

// Obtener datos para mostrar
$boxes = get_all_boxes_with_items($pdo);
$variantes_disponibles = get_available_variants($pdo);
$box_a_editar = get_box_to_edit($pdo, $_GET['edit_id'] ?? null);

include 'header.php';
?>

<div class="page-header">
    <h1>Gestión de Boxes de Promoción</h1>
    <p>Crea, visualiza y edita tus cajas promocionales.</p>
</div>
<hr>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="row">
    <div class="col-5">
        <form id="box-form" action="gestionar_box.php" method="POST" class="sticky-top">
            <div class="card">
                <div class="card-header"><h4><?php echo $box_a_editar ? 'Editando Box #' . $box_a_editar['id_promo'] : 'Crear Nuevo Box'; ?></h4></div>
                <div class="card-body">
                    <input type="hidden" name="id_promo" value="<?php echo $box_a_editar['id_promo'] ?? ''; ?>">
                    <div class="form-group"><label class="form-label">Nombre del Box</label><input type="text" name="nombre_promo" class="form-input" value="<?php echo htmlspecialchars($box_a_editar['nombre_promo'] ?? ''); ?>" required></div>
                    <div class="form-group"><label class="form-label">Descripción</label><textarea name="descripcion_promo" class="form-textarea" rows="2"><?php echo htmlspecialchars($box_a_editar['descripcion_promo'] ?? ''); ?></textarea></div>
                    <div class="row">
                        <div class="col-8"><label class="form-label">Disponible hasta (Opcional)</label><input type="datetime-local" name="fecha_termino" class="form-input" value="<?php echo !empty($box_a_editar['fecha_termino']) ? date('Y-m-d\TH:i', strtotime($box_a_editar['fecha_termino'])) : ''; ?>"></div>
                        <div class="col-4"><label class="form-label">Estado</label><select name="activa" class="form-select" required><option value="1" <?php echo (!isset($box_a_editar) || $box_a_editar['activa'] == 1) ? 'selected' : ''; ?>>Activa</option><option value="0" <?php echo (isset($box_a_editar) && $box_a_editar['activa'] == 0) ? 'selected' : ''; ?>>Inactiva</option></select></div>
                    </div>
                    <hr>
                    <h5>Items del Box</h5>
                    <div class="input-group mb-3"><select id="variant-select" class="form-select"><option value="">Selecciona un producto...</option><?php foreach ($variantes_disponibles as $variante): ?><option value="<?php echo $variante['id_variante']; ?>" data-text="<?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?>"><?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?></option><?php endforeach; ?></select><input type="number" id="item-quantity" class="form-input" value="1" min="1" style="flex: 0 0 80px;"><button type="button" id="add-item-btn" class="btn btn-success">Añadir</button></div>
                    <ul id="items-list" class="mb-3" style="list-style: none; padding: 0;"></ul>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <a href="gestionar_box.php" class="btn btn-secondary">Cancelar</a>
                           <button type="submit" name="save_box" class="btn btn-primary"><?php echo $box_a_editar ? 'Actualizar' : 'Guardar'; ?></button>
                        </div>
                        <div class="text-end">
                            <div class="d-inline-block text-start">
                                <small>Valor Real:</small> <strong id="display-valor-real" class="d-block">$0</strong>
                                <small>Precio Oferta:</small> <input type="number" step="1" name="precio_promo" class="form-input d-inline-block" style="width: 120px;" value="<?php echo (int)($box_a_editar['precio_promo'] ?? 0); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-7">
        <h4>Boxes Existentes</h4>
        <?php if (empty($boxes)): ?>
            <div class="alert alert-info">Aún no has creado ningún box de promoción.</div>
        <?php else: ?>
            <?php foreach ($boxes as $box): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><?php echo htmlspecialchars($box['nombre_promo']); ?> <span class="badge <?php echo $box['activa'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $box['activa'] ? 'Activo' : 'Inactivo'; ?></span></h5>
                        <div><a href="?edit_id=<?php echo $box['id_promo']; ?>" class="btn btn-warning" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">Editar</a><form action="gestionar_box.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que quieres eliminar este box?');"><input type="hidden" name="id_promo" value="<?php echo $box['id_promo']; ?>"><button type="submit" name="delete_box" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">Eliminar</button></form></div>
                    </div>
                    <?php if(!empty($box['descripcion_promo'])): ?><p class="card-text"><?php echo htmlspecialchars($box['descripcion_promo']); ?></p><?php endif; ?>
                    <ul class="list-unstyled mt-2" style="list-style: none; padding: 0;">
                        <?php foreach ($box['items'] as $item): ?>
                            <li>- <?php echo htmlspecialchars($item['nombre'] . ' (' . $item['sku'] . ')'); ?> x <strong><?php echo $item['cantidad']; ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                    <div><span class="text-decoration-line-through text-muted">$<?php echo number_format($box['valor_total_items'], 0, ',', '.'); ?></span><strong class="text-success fs-4 ms-2">$<?php echo number_format($box['precio_promo'], 0, ',', '.'); ?></strong></div>
                    <?php if ($box['fecha_termino']): ?><small class="text-danger">Válido hasta: <?php echo date('d/m/Y', strtotime($box['fecha_termino'])); ?></small><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const variantsData = <?php echo json_encode(array_column($variantes_disponibles, null, 'id_variante')); ?>;
    const itemsParaEditar = <?php echo json_encode($box_a_editar['items'] ?? []); ?>;
    const addItemBtn = document.getElementById('add-item-btn');
    const variantSelect = document.getElementById('variant-select');
    const quantityInput = document.getElementById('item-quantity');
    const itemsList = document.getElementById('items-list');
    const displayValorReal = document.getElementById('display-valor-real');

    function updateTotals() {
        let total = 0;
        itemsList.querySelectorAll('li').forEach(item => {
            const variantId = item.dataset.variantId;
            const quantity = parseInt(item.dataset.quantity, 10);
            if (variantsData[variantId]) {
                total += parseFloat(variantsData[variantId].precio) * quantity;
            }
        });
        displayValorReal.textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(total);
    }

    function addItemToList(variantId, quantity) {
        if (!variantsData[variantId] || !quantity) return;
        if (itemsList.querySelector(`li[data-variant-id="${variantId}"]`)) {
            alert('Este producto ya está en el box.');
            return;
        }
        const variantInfo = variantsData[variantId];
        const text = variantInfo.nombre + ' (' + variantInfo.sku + ')';
        const li = document.createElement('li');
        li.style.cssText = 'padding: .5rem .75rem; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; margin-bottom: -1px;';
        li.dataset.variantId = variantId;
        li.dataset.quantity = quantity;
        li.innerHTML = `
            <span>${text} x ${quantity}</span>
            <div><input type="hidden" name="items[${variantId}]" value="${quantity}"><button type="button" class="btn btn-danger remove-item-btn" style="padding: 0.1rem 0.4rem; font-size: 0.8rem;">X</button></div>
        `;
        itemsList.appendChild(li);
    }

    addItemBtn.addEventListener('click', function() {
        addItemToList(variantSelect.value, quantityInput.value);
        updateTotals();
        variantSelect.value = '';
        quantityInput.value = '1';
    });

    itemsList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn')) {
            e.target.closest('li').remove();
            updateTotals();
        }
    });

    if (Object.keys(itemsParaEditar).length > 0) {
        for (const variantId in itemsParaEditar) {
            addItemToList(variantId, itemsParaEditar[variantId]);
        }
        updateTotals();
    }
});
</script>

<?php include 'footer.php'; ?>