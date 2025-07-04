<?php
// admin/gestionar_pedidos.php (Versión Final)

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require '../db.php';

// Lógica para actualizar el estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $pedido_id = $_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->execute([$nuevo_estado, $pedido_id]);
        $_SESSION['message'] = "Estado del pedido #$pedido_id actualizado correctamente.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error al actualizar el estado: " . $e->getMessage();
    }
    header("Location: gestionar_pedidos.php");
    exit();
}

// Obtener todos los pedidos junto con la información del usuario
$sql = "
    SELECT 
        p.id_pedido, p.monto_total, p.estado, p.fecha_pedido,
        u.nombre_usuario, u.correo_usuario, u.telefono_usuario, u.direccion_usuario
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
";
$pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$estados_posibles = ['Pendiente', 'En preparación', 'Enviado', 'Completado', 'Cancelado'];

include 'header.php';
?>

<h1>Gestión de Pedidos</h1>
<p>Revisa y actualiza el estado de los pedidos recibidos.</p>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h4>Lista de Pedidos</h4>
    </div>
    <div class="card-body" style="padding: 0.5rem;">
        <?php if (empty($pedidos)): ?>
            <p class="p-3">No hay pedidos para mostrar.</p>
        <?php else: ?>
            <div class="accordion">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="accordion-item">
                        <button class="accordion-button">
                            <div class="order-summary">
                                <span class="order-id">#<?php echo $pedido['id_pedido']; ?></span>
                                <span class="order-customer" title="<?php echo htmlspecialchars($pedido['nombre_usuario']); ?>"><?php echo htmlspecialchars($pedido['nombre_usuario']); ?></span>
                                <span class="order-date"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                                <span class="order-total">$<?php echo number_format($pedido['monto_total'], 0, ',', '.'); ?></span>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pedido['estado'])); ?>"><?php echo htmlspecialchars($pedido['estado']); ?></span>
                            </div>
                        </button>
                        <div class="accordion-content">
                            <div class="accordion-content-inner">
                                <h5>Detalles del Pedido</h5>
                                <div class="order-details-grid">
                                    <div><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_usuario']); ?></div>
                                    <div><strong>Email:</strong> <?php echo htmlspecialchars($pedido['correo_usuario']); ?></div>
                                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono_usuario']); ?></div>
                                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion_usuario']); ?></div>
                                </div>
                                <hr>
                                <h6>Productos y Servicios del Pedido:</h6>
                                <?php
                                $stmt_items = $pdo->prepare("SELECT pi.*, vp.sku, p.nombre FROM pedidos_items pi JOIN variantes_producto vp ON pi.variante_id = vp.id_variante JOIN productos p ON vp.id_producto = p.id WHERE pi.pedido_id = ?");
                                $stmt_items->execute([$pedido['id_pedido']]);
                                $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                                $stmt_servicios = $pdo->prepare("SELECT * FROM detalles_servicio WHERE pedido_id = ?");
                                $stmt_servicios->execute([$pedido['id_pedido']]);
                                $servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <ul class="item-list">
                                    <?php foreach ($items as $item): ?>
                                        <li>
                                            <span>(<?php echo $item['cantidad']; ?>x) <?php echo htmlspecialchars($item['nombre']); ?> (SKU: <?php echo htmlspecialchars($item['sku']); ?>)</span>
                                            <span>$<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 0, ',', '.'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <li>
                                            <span>(<?php echo $servicio['cantidad']; ?>x) <?php echo htmlspecialchars($servicio['nombre_servicio']); ?></span>
                                            <span>$<?php echo number_format($servicio['precio_unitario'] * $servicio['cantidad'], 0, ',', '.'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <hr>
                                <form action="gestionar_pedidos.php" method="POST" class="update-status-form">
                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id_pedido']; ?>">
                                    <label for="estado-<?php echo $pedido['id_pedido']; ?>"><strong>Actualizar Estado:</strong></label>
                                    <select name="estado" id="estado-<?php echo $pedido['id_pedido']; ?>" class="form-select" style="width: auto; flex-grow: 1;">
                                        <?php foreach ($estados_posibles as $estado): ?>
                                            <option value="<?php echo $estado; ?>" <?php echo ($pedido['estado'] == $estado) ? 'selected' : ''; ?>>
                                                <?php echo $estado; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="actualizar_estado" class="btn btn-primary">Actualizar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>