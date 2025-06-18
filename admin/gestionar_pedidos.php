<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pedido_id = $_POST['id_pedido'];
    $nuevo_estado = $_POST['estado'];

    $stmt_estado_actual = $pdo->prepare("SELECT estado FROM pedidos WHERE id_pedido = ?");
    $stmt_estado_actual->execute([$pedido_id]);
    $estado_actual = $stmt_estado_actual->fetchColumn();

    $pdo->beginTransaction();
    try {
        // La condición para reducir stock sigue siendo la misma
        if ($estado_actual != 'En preparación' && $nuevo_estado == 'En preparación') {
            // Obtener los items del pedido usando el variante_id
            $stmt_items = $pdo->prepare("SELECT variante_id, cantidad FROM pedidos_items WHERE pedido_id = ?");
            $stmt_items->execute([$pedido_id]);
            $items_del_pedido = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items_del_pedido as $item) {
                // Actualizar el stock en la tabla 'variantes_producto' usando el 'id_variante'
                $sql_update_stock = "UPDATE variantes_producto SET stock = stock - ? WHERE id_variante = ?";
                $stmt_update_stock = $pdo->prepare($sql_update_stock);
                $stmt_update_stock->execute([$item['cantidad'], $item['variante_id']]);
            }
        }
        
        // Actualizar el estado del pedido
        $sql_update_pedido = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
        $stmt_update_pedido = $pdo->prepare($sql_update_pedido);
        $stmt_update_pedido->execute([$nuevo_estado, $pedido_id]);

        $pdo->commit();
        $_SESSION['message'] = "Estado del pedido actualizado correctamente.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al actualizar el pedido: " . $e->getMessage();
    }

    header("Location: gestionar_pedidos.php");
    exit();
}

// La consulta para mostrar los pedidos no necesita grandes cambios
$pedidos = $pdo->query("
    SELECT p.id_pedido, p.monto_total, p.estado, p.fecha_pedido, u.nombre_usuario
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
// ... el resto del HTML para mostrar la tabla de pedidos puede ser muy similar al anterior ...
// Solo asegúrate de usar 'id_pedido' en los formularios.
?>