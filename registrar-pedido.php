<?php
session_start();
require __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['cart']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'No hay datos del carrito para procesar.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$cart_items = $data['cart'];
$monto_total = $data['total'];

try {
    $pdo->beginTransaction();

    // Insertar el pedido principal en la tabla `pedidos` (usando el nombre de columna correcto `usuario_id`)
    $sql_pedido = "INSERT INTO pedidos (usuario_id, monto_total, estado) VALUES (?, ?, ?)";
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([$usuario_id, $monto_total, 'Pendiente']);
    $pedido_id = $pdo->lastInsertId();

    // Preparar las dos consultas, una para cada tabla de detalles
    $sql_item_producto = "INSERT INTO pedidos_items (pedido_id, variante_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_item_producto = $pdo->prepare($sql_item_producto);

    $sql_item_servicio = "INSERT INTO detalles_servicio (pedido_id, nombre_servicio, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_item_servicio = $pdo->prepare($sql_item_servicio);

    // Recorrer el carrito y decidir dónde guardar cada ítem
    foreach ($cart_items as $item) {

        if ((is_string($item['id']) && strpos($item['id'], 'servicio-') === 0) || 
                (is_string($item['id']) && strpos($item['id'], 'promo-') === 0)) {

                $stmt_item_servicio->execute([
                    $pedido_id,
                    $item['name'],
                    $item['quantity'],
                    $item['price']
                ]);

            } else {
                // Es un producto, lo guardamos en la tabla 'pedidos_items'
                $stmt_item_producto->execute([
                    $pedido_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
        }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Pedido registrado con éxito.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar el pedido: ' . $e->getMessage()]);
}
?>