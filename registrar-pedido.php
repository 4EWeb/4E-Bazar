<?php
// Iniciar sesión y la conexión a la base de datos
session_start();
require __DIR__ . '/db.php';

// Definir la cabecera como JSON para la respuesta
header('Content-Type: application/json');

// 1. Seguridad: Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

// 2. Obtener los datos del carrito enviados desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['cart']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'No hay datos del carrito para procesar.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$cart_items = $data['cart'];
$monto_total = $data['total'];

// 3. Usar una transacción para asegurar la integridad de los datos
// Si algo falla, nada se guarda.
try {
    $pdo->beginTransaction();

    // 3.1. Insertar el pedido principal en la tabla `pedidos`
    $sql_pedido = "INSERT INTO pedidos (usuario_id, monto_total, estado) VALUES (?, ?, ?)";
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([$usuario_id, $monto_total, 'Pendiente']);
    
    // Obtener el ID del pedido que acabamos de crear
    $pedido_id = $pdo->lastInsertId();

    // 3.2. Insertar cada item del carrito en la tabla `pedidos_items`
    $sql_item = "INSERT INTO pedidos_items (pedido_id, producto_nombre, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_item = $pdo->prepare($sql_item);

    foreach ($cart_items as $item) {
        $stmt_item->execute([
            $pedido_id,
            $item['name'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Si todo salió bien, confirmamos los cambios en la base de datos
    $pdo->commit();

    // 4. Enviar una respuesta de éxito a JavaScript
    echo json_encode(['success' => true, 'message' => 'Pedido registrado con éxito.']);

} catch (Exception $e) {
    // Si algo falló, revertimos todos los cambios
    $pdo->rollBack();
    // Y enviamos una respuesta de error
    echo json_encode(['success' => false, 'message' => 'Error al guardar el pedido: ' . $e->getMessage()]);
}
?>