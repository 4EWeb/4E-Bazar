<?php
// admin/gestionar_productos.php

// Iniciar la sesión para poder usar variables de sesión para los mensajes
session_start(); 

// require 'verificar_sesion.php'; // Lo agregaremos al final
require '../db.php'; // Asegúrate de que la ruta sea correcta

// Lógica para procesar TODOS los cambios a la vez
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_changes'])) {
    
    // Iniciar transacción para asegurar la integridad de los datos
    $pdo->beginTransaction();
    try {
        // Preparar la consulta UNA SOLA VEZ fuera del bucle para mayor eficiencia
        $sql = "UPDATE productos SET precio = ?, cantidad = ?, destacado = ?, descuento = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        $cambios_realizados = 0;

        // Iterar sobre los datos enviados. Usamos 'precio' como referencia.
        // Los datos llegarán en arrays asociativos donde la clave es el ID del producto.
        foreach ($_POST['precio'] as $id => $precio) {
            // Recoger los datos correspondientes para este ID
            $cantidad = $_POST['cantidad'][$id];
            $destacado = $_POST['destacado'][$id];
            $descuento = $_POST['descuento'][$id];

            // Ejecutar la actualización para cada producto
            $stmt->execute([$precio, $cantidad, $destacado, $descuento, $id]);
            $cambios_realizados += $stmt->rowCount(); // rowCount() devuelve el número de filas afectadas
        }

        // Confirmar la transacción si todo fue exitoso
        $pdo->commit();

        // Crear un mensaje de éxito para mostrarlo después de redirigir
        if ($cambios_realizados > 0) {
            $_SESSION['message'] = "¡Éxito! Se guardaron los cambios en " . $cambios_realizados . " producto(s).";
        } else {
            $_SESSION['message'] = "No se detectaron cambios para guardar.";
        }

    } catch (Exception $e) {
        // Si algo falla, deshacer todos los cambios
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al guardar los cambios: " . $e->getMessage();
    }

    // Redirigir a la misma página para mostrar los cambios y el mensaje
    header("Location: gestionar_productos.php");
    exit();
}

$productos = $pdo->query("SELECT id, nombre, precio, cantidad, destacado, descuento FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<h1>Gestionar Productos</h1>
<hr>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); // Limpiar el mensaje para que no aparezca de nuevo ?>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
     <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>


<form action="gestionar_productos.php" method="POST">
    
    <div class="mb-3">
        <button type="submit" name="save_all_changes" class="btn btn-primary btn-lg">Guardar Todos los Cambios</button>
        <a href="agregar_producto.php" class="btn btn-success btn-lg">Agregar Nuevo Producto</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Destacado</th>
                    <th>Descuento (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    
                    <td>
                        <input type="number" name="precio[<?php echo $producto['id']; ?>]" class="form-control" value="<?php echo $producto['precio']; ?>" required>
                    </td>
                    <td>
                        <input type="number" name="cantidad[<?php echo $producto['id']; ?>]" class="form-control" value="<?php echo $producto['cantidad']; ?>" required>
                    </td>
                    <td>
                        <select name="destacado[<?php echo $producto['id']; ?>]" class="form-select">
                            <option value="No" <?php echo ($producto['destacado'] == 'No') ? 'selected' : ''; ?>>No</option>
                            <option value="Si" <?php echo ($producto['destacado'] == 'Si') ? 'selected' : ''; ?>>Sí</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="descuento[<?php echo $producto['id']; ?>]" class="form-control" value="<?php echo $producto['descuento']; ?>" min="0" max="100">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</form>

<?php include 'footer.php'; ?>