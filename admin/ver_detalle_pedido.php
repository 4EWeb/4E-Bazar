<?php
// admin/ver_detalle_pedido.php
require 'admin_functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de pedido no válido.");
}
$pedido_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM pedidos_items WHERE pedido_id = ?");
$stmt->execute([$pedido_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="page-header">
    <h1>Detalle del Pedido #<?php echo $pedido_id; ?></h1>
</div>
<hr>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_pedido = 0;
        foreach ($items as $item):
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $total_pedido += $subtotal;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
            <td><?php echo $item['cantidad']; ?></td>
            <td>$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
            <td>$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #f8f9fa;">
            <td colspan="3" class="text-end"><strong>Total del Pedido:</strong></td>
            <td><strong>$<?php echo number_format($total_pedido, 0, ',', '.'); ?></strong></td>
        </tr>
    </tfoot>
</table>

<a href="gestionar_pedidos.php" class="btn btn-secondary">Volver a Pedidos</a>

<?php include 'footer.php'; ?>