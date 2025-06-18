<?php
//require 'verificar_sesion.php';
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_changes'])) {
    $pdo->beginTransaction();
    try {
        $sql = "UPDATE variantes_producto SET precio = ?, stock = ?, descuento = ?, destacado = ? WHERE id_variante = ?";
        $stmt = $pdo->prepare($sql);
        $cambios_realizados = 0;
        foreach ($_POST['precio'] as $id_variante => $precio) {
            $stmt->execute([ $precio, $_POST['stock'][$id_variante], $_POST['descuento'][$id_variante], $_POST['destacado'][$id_variante], $id_variante ]);
            $cambios_realizados += $stmt->rowCount();
        }
        $pdo->commit();
        $_SESSION['message'] = "¡Éxito! Se guardaron los cambios en " . $cambios_realizados . " variante(s).";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al guardar los cambios: " . $e->getMessage();
    }
    header("Location: gestionar_productos.php");
    exit();
}

$variantes = $pdo->query("SELECT v.id_variante, v.sku, v.precio, v.stock, v.descuento, v.destacado, p.nombre as nombre_producto FROM variantes_producto v JOIN productos p ON v.id_producto = p.id ORDER BY p.nombre, v.id_variante")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
<h1>Gestionar Variantes de Productos</h1>
<hr>

<?php if (isset($_SESSION['message'])): ?><div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div><?php endif; ?>

<form action="gestionar_productos.php" method="POST">
    <div class="mb-3"><button type="submit" name="save_all_changes" class="btn btn-primary btn-lg">Guardar Todos los Cambios</button></div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark"><tr><th>Producto Padre</th><th>SKU</th><th>Precio</th><th>Stock</th><th>Descuento (%)</th><th>Destacado</th></tr></thead>
            <tbody>
                <?php foreach ($variantes as $variante): ?>
                <tr>
                    <td><?php echo htmlspecialchars($variante['nombre_producto']); ?></td>
                    <td><?php echo htmlspecialchars($variante['sku']); ?></td>
                    <td><input type="number" step="0.01" name="precio[<?php echo $variante['id_variante']; ?>]" class="form-control" value="<?php echo $variante['precio']; ?>"></td>
                    <td><input type="number" name="stock[<?php echo $variante['id_variante']; ?>]" class="form-control" value="<?php echo $variante['stock']; ?>"></td>
                    <td><input type="number" step="0.01" name="descuento[<?php echo $variante['id_variante']; ?>]" class="form-control" value="<?php echo $variante['descuento']; ?>"></td>
                    <td><select name="destacado[<?php echo $variante['id_variante']; ?>]" class="form-select"><option value="0" <?php echo ($variante['destacado'] == 0) ? 'selected' : ''; ?>>No</option><option value="1" <?php echo ($variante['destacado'] == 1) ? 'selected' : ''; ?>>Sí</option></select></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</form>
<?php include 'footer.php'; ?>