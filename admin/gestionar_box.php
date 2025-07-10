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

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="main-container">
    <div class="editor-column">
        <form id="box-form" action="gestionar_box.php" method="POST" class="sticky-form" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo $box_a_editar ? 'Editando Box #' . $box_a_editar['id_promo'] : 'Crear Nuevo Box'; ?></h4>
                </div>
                <div class="card-body">
                    <input type="hidden" name="id_promo" value="<?php echo $box_a_editar['id_promo'] ?? ''; ?>">
                    <input type="hidden" name="imagen_actual" value="<?php echo $box_a_editar['imagen_promo'] ?? ''; ?>">

                    <div class="form-group mb-3">
                        <label class="form-label">Nombre del Box</label>
                        <input type="text" name="nombre_promo" class="form-control" value="<?php echo htmlspecialchars($box_a_editar['nombre_promo'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion_promo" class="form-control" rows="2"><?php echo htmlspecialchars($box_a_editar['descripcion_promo'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="imagen_promo" class="form-label">Imagen del Box</label>
                        <input class="form-control" type="file" id="imagen_promo" name="imagen_promo">
                        <?php if (!empty($box_a_editar['imagen_promo'])): ?>
                            <div class="mt-2">
                                <small>Imagen actual:</small><br>
                                <img src="../<?php echo htmlspecialchars($box_a_editar['imagen_promo']); ?>" alt="Imagen actual" style="max-width: 80px; border-radius: 5px; border: 1px solid #ddd; padding: 2px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Disponible hasta (Opcional)</label>
                            <input type="datetime-local" name="fecha_termino" class="form-control" value="<?php echo !empty($box_a_editar['fecha_termino']) ? date('Y-m-d\TH:i', strtotime($box_a_editar['fecha_termino'])) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="activa" class="form-select" required>
                                <option value="1" <?php echo (!isset($box_a_editar) || $box_a_editar['activa'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo (isset($box_a_editar) && $box_a_editar['activa'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr class="my-4">

                    <h5>Items del Box</h5>
                    <div class="input-group mb-3">
                        <select id="variant-select" class="form-select">
                            <option value="">Selecciona un producto...</option>
                            <?php foreach ($variantes_disponibles as $variante): ?>
                                <option value="<?php echo $variante['id_variante']; ?>" data-text="<?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?>">
                                    <?php echo htmlspecialchars($variante['nombre'] . ' (' . $variante['sku'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="item-quantity" class="form-control" value="1" min="1" style="max-width: 70px;">
                        <button type="button" id="add-item-btn" class="btn btn-primary">Añadir</button>
                    </div>
                    
                    <ul id="items-list" class="list-group list-group-flush mb-3">
                        </ul>
                </div>
                <div class="card-footer">
                    <div class="box-form-footer">
                         <div class="price-section">
                            <div class="price-item">
                                <label>Valor Real</label>
                                <strong id="display-valor-real">$0</strong>
                            </div>
                            <div class="price-item">
                                <label for="precio_promo">Precio Oferta</label>
                                <input type="number" step="1" id="precio_promo" name="precio_promo" class="form-control price-input" value="<?php echo (int)($box_a_editar['precio_promo'] ?? 0); ?>" required>
                            </div>
                        </div>
                        <div class="actions-section">
                           <a href="gestionar_box.php" class="btn btn-secondary">Cancelar</a>
                           <button type="submit" name="save_box" class="btn btn-success"><?php echo $box_a_editar ? 'Actualizar Box' : 'Guardar Box'; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="products-column">
        <h4>Boxes Existentes</h4>
        <?php if (empty($boxes)): ?>
            <div class="card"><div class="card-body text-center text-muted">Aún no has creado ningún box de promoción.</div></div>
        <?php else: ?>
            <div class="existing-boxes-container">
            <?php foreach ($boxes as $box): ?>
            <div class="card box-card">
                <div class="box-card-header">
                    <h5 class="box-title"><?php echo htmlspecialchars($box['nombre_promo']); ?></h5>
                    <span class="status-badge <?php echo $box['activa'] ? 'status-activo' : 'status-inactivo'; ?>">
                        <?php echo $box['activa'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($box['imagen_promo'])): ?>
                        <img src="../<?php echo htmlspecialchars($box['imagen_promo']); ?>" alt="Imagen del box" style="width: 100%; height: 150px; object-fit: contain; border-radius: 8px; margin-bottom: 1rem; background-color: #f8f9fa;">
                    <?php endif; ?>
                    <?php if(!empty($box['descripcion_promo'])): ?>
                        <p class="box-description"><?php echo htmlspecialchars($box['descripcion_promo']); ?></p>
                    <?php endif; ?>
                    <ul class="box-item-list">
                        <?php foreach ($box['items'] as $item): ?>
                            <li><?php echo htmlspecialchars($item['nombre'] . ' (' . $item['sku'] . ')'); ?> x <strong><?php echo $item['cantidad']; ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer box-card-footer">
                    <div class="box-pricing">
                        <span class="price-original">$<?php echo number_format($box['valor_total_items'], 0, ',', '.'); ?></span>
                        <strong class="price-final">$<?php echo number_format($box['precio_promo'], 0, ',', '.'); ?></strong>
                    </div>
                    <div class="box-actions">
                        <a href="?edit_id=<?php echo $box['id_promo']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <form action="gestionar_box.php" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que quieres eliminar este box?');">
                            <input type="hidden" name="id_promo" value="<?php echo $box['id_promo']; ?>">
                            <button type="submit" name="delete_box" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
                 <?php if ($box['fecha_termino']): ?>
                    <div class="box-expiry-date">
                        Válido hasta: <?php echo date('d/m/Y H:i', strtotime($box['fecha_termino'])); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
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
        itemsList.querySelectorAll('.list-group-item').forEach(item => {
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
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.dataset.variantId = variantId;
        li.dataset.quantity = quantity;
        li.innerHTML = `
            <span>${text} <strong>x ${quantity}</strong></span>
            <div>
                <input type="hidden" name="items[${variantId}]" value="${quantity}">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
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
        const removeButton = e.target.closest('.remove-item-btn');
        if (removeButton) {
            removeButton.closest('li').remove();
            updateTotals();
        }
    });

    if (Object.keys(itemsParaEditar).length > 0) {
        for (const variantId in itemsParaEditar) {
            const quantity = itemsParaEditar[variantId];
            addItemToList(variantId, quantity);
        }
        updateTotals();
    }
});
</script>

<?php include 'footer.php'; ?>