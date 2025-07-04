<?php
require 'admin_functions.php';

// Procesar formularios
handle_pedidos_requests($pdo);

// Obtener datos para mostrar
$pedidos = get_all_pedidos_with_details($pdo);

include 'header.php';
?>

<div class="page-header">
    <h1>Gestión de Pedidos</h1>
    <p>Visualiza y actualiza el estado de los pedidos de tus clientes.</p>
</div>
<hr>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="row">
    <?php foreach ($pedidos as $pedido): ?>
        <div class="col-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedido #<?php echo $pedido['id_pedido']; ?></h5>
                    <span class="badge bg-secondary"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                    <p><strong>Monto Total:</strong> <span class="fs-5 text-success fw-bold">$<?php echo number_format($pedido['monto_total'], 0, ',', '.'); ?></span></p>
                    
                    <form action="gestionar_pedidos.php" method="POST" class="mt-3">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius: 0.25rem 0 0 0.25rem; border: 1px solid #ced4da; padding: 0.5rem 0.75rem; background: #e9ecef;">Estado:</span>
                            <select name="estado" class="form-select" style="border-radius:0;">
                                <option value="en espera" <?php if($pedido['estado'] == 'en espera') echo 'selected'; ?>>En Espera</option>
                                <option value="preparando" <?php if($pedido['estado'] == 'preparando') echo 'selected'; ?>>Preparando</option>
                                <option value="enviado" <?php if($pedido['estado'] == 'enviado') echo 'selected'; ?>>Enviado</option>
                                <option value="en camino" <?php if($pedido['estado'] == 'en camino') echo 'selected'; ?>>En Camino</option>
                                <option value="cancelado" <?php if($pedido['estado'] == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-info w-100" data-modal-target="#detallePedidoModal-<?php echo $pedido['id_pedido']; ?>">
                        Ver Detalles del Pedido
                    </button>
                </div>
            </div>
        </div>

        <div class="modal" id="detallePedidoModal-<?php echo $pedido['id_pedido']; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle del Pedido #<?php echo $pedido['id_pedido']; ?></h5>
                        <button type="button" class="btn-close">×</button>
                    </div>
                    <div class="modal-body">
                        <h6>Información del Cliente</h6>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_usuario']); ?></p>
                        <hr>
                        <h6>Ítems a Preparar</h6>
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr><th>Producto (SKU)</th><th>Cantidad</th><th>Precio</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre_producto'] . ' (' . $item['sku'] . ')'); ?></td>
                                        <td><strong><?php echo $item['cantidad']; ?></strong></td>
                                        <td>$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>