<?php
// admin/gestionar_pedidos.php
// require 'verificar_sesion.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pedido_id = $_POST['id_pedido'];
    $nuevo_estado = $_POST['estado'];

    // 1. Obtener el estado actual del pedido ANTES de actualizar
    $stmt_estado_actual = $pdo->prepare("SELECT estado FROM pedidos WHERE id = ?");
    $stmt_estado_actual->execute([$pedido_id]);
    $estado_actual = $stmt_estado_actual->fetchColumn();

    // 2. Iniciar una transacción para asegurar que todo se ejecute correctamente
    $pdo->beginTransaction();

    try {
        // 3. Condición clave: ¿El estado está cambiando A "En preparación" desde otro estado?
        if ($estado_actual != 'En preparación' && $nuevo_estado == 'En preparación') {
            // Obtener todos los ítems de este pedido
            $stmt_items = $pdo->prepare("SELECT producto_nombre, cantidad FROM pedidos_items WHERE pedido_id = ?");
            $stmt_items->execute([$pedido_id]);
            $items_del_pedido = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // Recorrer cada ítem y actualizar el stock del producto correspondiente
            foreach ($items_del_pedido as $item) {
                $sql_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE nombre = ?";
                $stmt_update_stock = $pdo->prepare($sql_update_stock);
                // Restamos la cantidad del item al stock del producto
                $stmt_update_stock->execute([$item['cantidad'], $item['producto_nombre']]);
            }
        }
        
        // 4. Actualizar el estado del pedido (esto pasa siempre)
        $sql_update_pedido = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt_update_pedido = $pdo->prepare($sql_update_pedido);
        $stmt_update_pedido->execute([$nuevo_estado, $pedido_id]);

        // 5. Si todo salió bien, confirmar los cambios
        $pdo->commit();

    } catch (Exception $e) {
        // 6. Si algo falló, deshacer todos los cambios
        $pdo->rollBack();
        die("Error al actualizar el pedido: " . $e->getMessage());
    }

    header("Location: gestionar_pedidos.php");
    exit();
}


// El resto del archivo (la parte que muestra la tabla) sigue igual
$pedidos = $pdo->query("
    SELECT p.id, p.monto_total, p.estado, p.fecha_pedido, u.nombre_usuario
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<h1>Gestionar Pedidos</h1>
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID Pedido</th>
            <th>Cliente</th>
            <th>Monto Total</th>
            <th>Fecha</th>
            <th>Estado Actual</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedidos as $pedido): ?>
        <tr>
            <form action="gestionar_pedidos.php" method="POST">
                <td>#<?php echo $pedido['id']; ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_usuario']); ?></td>
                <td>$<?php echo number_format($pedido['monto_total'], 0, ',', '.'); ?></td>
                <td><?php echo date('d-m-Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                <td>
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <select name="estado" class="form-select">
                        <option value="Pendiente" <?php echo ($pedido['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="En preparación" <?php echo ($pedido['estado'] == 'En preparación') ? 'selected' : ''; ?>>En preparación</option>
                        <option value="Enviado" <?php echo ($pedido['estado'] == 'Enviado') ? 'selected' : ''; ?>>Enviado</option>
                        <option value="Completado" <?php echo ($pedido['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                        <option value="Cancelado" <?php echo ($pedido['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </td>
                <td>
                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Guardar</button>
                    <a href="ver_detalle_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-info btn-sm">Ver Detalle</a>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>