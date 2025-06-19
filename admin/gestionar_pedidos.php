<?php
//require 'verificar_sesion.php';
require '../db.php';

// --- LÓGICA PARA ACTUALIZAR EL ESTADO Y EL STOCK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pedido_id = $_POST['id_pedido'];
    $nuevo_estado = $_POST['estado'];

    // 1. Obtenemos el estado actual del pedido ANTES de hacer cualquier cambio.
    $stmt_estado_actual = $pdo->prepare("SELECT estado FROM pedidos WHERE id_pedido = ?");
    $stmt_estado_actual->execute([$pedido_id]);
    $estado_actual = $stmt_estado_actual->fetchColumn();

    // 2. Iniciamos una transacción para garantizar la integridad de los datos.
    $pdo->beginTransaction();
    try {
        // 3. Condición CLAVE: ¿El estado está cambiando A "preparando" desde CUALQUIER OTRO estado?
        // Esto asegura que el stock solo se descuente UNA VEZ.
        if ($estado_actual != 'preparando' && $nuevo_estado == 'preparando') {
            
            // Obtenemos todos los items (variantes) del pedido.
            $stmt_items = $pdo->prepare("SELECT variante_id, cantidad FROM pedidos_items WHERE pedido_id = ?");
            $stmt_items->execute([$pedido_id]);
            $items_del_pedido = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // Preparamos la consulta para actualizar el stock.
            $sql_update_stock = "UPDATE variantes_producto SET stock = stock - ? WHERE id_variante = ?";
            $stmt_update_stock = $pdo->prepare($sql_update_stock);

            // Recorremos cada item y actualizamos su stock.
            foreach ($items_del_pedido as $item) {
                if ($item['variante_id']) { // Asegurarnos de que hay una variante asociada
                    $stmt_update_stock->execute([$item['cantidad'], $item['variante_id']]);
                }
            }
            $_SESSION['message'] = "Estado actualizado y stock descontado.";
        } else {
             $_SESSION['message'] = "Estado del pedido actualizado.";
        }
        
        // 4. Actualizamos el estado del pedido. Esto se hace siempre.
        $sql_update_pedido = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
        $stmt_update_pedido = $pdo->prepare($sql_update_pedido);
        $stmt_update_pedido->execute([$nuevo_estado, $pedido_id]);

        // 5. Si todo fue bien, confirmamos los cambios en la base de datos.
        $pdo->commit();
    } catch (Exception $e) {
        // 6. Si algo falló, revertimos TODOS los cambios.
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al actualizar el pedido: " . $e->getMessage();
    }

    header("Location: gestionar_pedidos.php");
    exit();
}

// --- OBTENCIÓN DE DATOS PARA MOSTRAR ---
// Obtenemos todos los pedidos con la info del usuario.
$pedidos = $pdo->query("
    SELECT p.id_pedido, p.monto_total, p.estado, p.fecha_pedido, u.nombre_usuario, u.direccion_usuario
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Para cada pedido, obtenemos sus items para mostrarlos en el modal.
foreach ($pedidos as $key => $pedido) {
    $stmt_items = $pdo->prepare("
        SELECT pi.cantidad, pi.precio_unitario, v.sku, prod.nombre as nombre_producto
        FROM pedidos_items pi
        JOIN variantes_producto v ON pi.variante_id = v.id_variante
        JOIN productos prod ON v.id_producto = prod.id
        WHERE pi.pedido_id = ?
    ");
    $stmt_items->execute([$pedido['id_pedido']]);
    $pedidos[$key]['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
}


include 'header.php';
?>

<h1>Gestión de Pedidos</h1>
<p>Visualiza y actualiza el estado de los pedidos de tus clientes.</p>
<hr>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="row row-cols-1 row-cols-lg-2 g-4">
    <?php foreach ($pedidos as $pedido): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedido #<?php echo $pedido['id_pedido']; ?></h5>
                    <span class="badge bg-secondary"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                </div>
                <div class="card-body">
                    <p class="card-text"><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                    <p class="card-text"><strong>Monto Total:</strong> <span class="fs-5 text-success fw-bold">$<?php echo number_format($pedido['monto_total'], 0, ',', '.'); ?></span></p>
                    
                    <form action="gestionar_pedidos.php" method="POST" class="mt-3">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                        <div class="input-group">
                            <label class="input-group-text" for="estado-<?php echo $pedido['id_pedido']; ?>">Estado:</label>
                            <select name="estado" id="estado-<?php echo $pedido['id_pedido']; ?>" class="form-select">
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
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#detallePedidoModal-<?php echo $pedido['id_pedido']; ?>">
                        Ver Detalles del Pedido para Preparar Envío
                    </button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="detallePedidoModal-<?php echo $pedido['id_pedido']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle del Pedido #<?php echo $pedido['id_pedido']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Información del Cliente</h6>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></p>
                        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_usuario']); ?></p>
                        <hr>
                        <h6>Ítems a Preparar</h6>
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Producto (SKU)</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre_producto'] . ' (' . $item['sku'] . ')'); ?></td>
                                        <td><strong><?php echo $item['cantidad']; ?></strong></td>
                                        <td>$<?php echo number_format($item['precio_unitario'], 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>