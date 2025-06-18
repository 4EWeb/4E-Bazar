<?php
//require 'verificar_sesion.php';
require '../db.php';

// --- 1. OBTENER ATRIBUTOS Y OPCIONES PARA LOS FORMULARIOS ---
$atributos_query = $pdo->query("
    SELECT a.id_atributo, a.nombre, o.id_opcion, o.valor
    FROM atributos a
    JOIN opciones o ON a.id_atributo = o.id_atributo
    ORDER BY a.nombre, o.valor
");

$atributos_con_opciones = [];
foreach ($atributos_query as $row) {
    $atributos_con_opciones[$row['id_atributo']]['nombre'] = $row['nombre'];
    $atributos_con_opciones[$row['id_atributo']]['opciones'][] = [
        'id_opcion' => $row['id_opcion'],
        'valor' => $row['valor']
    ];
}

// --- 2. LÓGICA PARA PROCESAR EL FORMULARIO COMPLETO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    
    $pdo->beginTransaction();
    try {
        // Insertar el producto padre
        $sql_producto = "INSERT INTO productos (nombre, descripcion, categoriaID) VALUES (?, ?, ?)";
        $stmt_producto = $pdo->prepare($sql_producto);
        $stmt_producto->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['categoriaID']]);
        $id_nuevo_producto = $pdo->lastInsertId();

        // Iterar y guardar cada variante enviada
        if (isset($_POST['variantes'])) {
            foreach ($_POST['variantes'] as $variante_data) {
                // Insertar la variante
                $sql_variante = "INSERT INTO variantes_producto (id_producto, sku, precio, stock) VALUES (?, ?, ?, ?)";
                $stmt_variante = $pdo->prepare($sql_variante);
                $stmt_variante->execute([$id_nuevo_producto, $variante_data['sku'], $variante_data['precio'], $variante_data['stock']]);
                $id_nueva_variante = $pdo->lastInsertId();

                // Vincular las opciones a la variante
                if (isset($variante_data['opciones'])) {
                    foreach ($variante_data['opciones'] as $id_opcion) {
                        $sql_opcion = "INSERT INTO variante_opcion (id_variante, id_opcion) VALUES (?, ?)";
                        $stmt_opcion = $pdo->prepare($sql_opcion);
                        $stmt_opcion->execute([$id_nueva_variante, $id_opcion]);
                    }
                }
            }
        }
        
        $pdo->commit();
        $_SESSION['message'] = "¡Producto y sus variantes creados exitosamente!";
        header("Location: gestionar_productos.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al crear el producto: " . $e->getMessage();
    }
}

$categorias = $pdo->query("SELECT id_categoria, nombre_categoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>

<h1>Agregar Nuevo Producto con Variantes Dinámicas</h1>
<hr>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<form action="agregar_producto.php" method="POST" enctype="multipart/form-data">
    <div class="card mb-4">
        <div class="card-header"><h4>1. Datos del Producto General</h4></div>
        <div class="card-body row g-3">
            <div class="col-md-8"><label class="form-label">Nombre del Producto</label><input type="text" name="nombre" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Categoría</label><select name="categoriaID" class="form-select" required><option value="" disabled selected>Elige...</option><?php foreach ($categorias as $c):?><option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nombre_categoria']); ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="3"></textarea></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h4>2. Variantes del Producto</h4></div>
        <div class="card-body">
            <div id="variantes-container">
                </div>
            <button type="button" id="add-variant-btn" class="btn btn-success mt-2">Añadir Variante</button>
        </div>
    </div>
    
    <div class="d-grid gap-2">
        <button type="submit" name="add_product" class="btn btn-primary btn-lg">Guardar Producto y Todas sus Variantes</button>
    </div>
</form>

<template id="variant-template">
    <div class="variant-form-block border rounded p-3 mb-3">
        <button type="button" class="btn-close float-end remove-variant-btn" aria-label="Close"></button>
        <h5>Nueva Variante</h5>
        <div class="row">
            <div class="col-md-4 mb-2"><label class="form-label">SKU</label><input type="text" name="variantes[__INDEX__][sku]" class="form-control"></div>
            <div class="col-md-4 mb-2"><label class="form-label">Precio</label><input type="number" step="0.01" name="variantes[__INDEX__][precio]" class="form-control" required></div>
            <div class="col-md-4 mb-2"><label class="form-label">Stock</label><input type="number" name="variantes[__INDEX__][stock]" class="form-control" required></div>
        </div>
        <h6>Atributos</h6>
        <div class="row">
            <?php foreach ($atributos_con_opciones as $id_atributo => $atributo): ?>
            <div class="col-md-4 mb-2">
                <label class="form-label"><?php echo htmlspecialchars($atributo['nombre']); ?></label>
                <select name="variantes[__INDEX__][opciones][]" class="form-select">
                    <option value="">No aplica</option>
                    <?php foreach ($atributo['opciones'] as $opcion): ?>
                        <option value="<?php echo $opcion['id_opcion']; ?>"><?php echo htmlspecialchars($opcion['valor']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</template>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const addVariantBtn = document.getElementById('add-variant-btn');
    const container = document.getElementById('variantes-container');
    const template = document.getElementById('variant-template');
    let variantCounter = 0;

    // Función para agregar un nuevo bloque de variante
    addVariantBtn.addEventListener('click', () => {
        variantCounter++;
        // Clonar la plantilla
        const clone = template.content.cloneNode(true);
        // Obtener el HTML clonado y reemplazar el placeholder de índice
        let html = new XMLSerializer().serializeToString(clone);
        html = html.replace(/__INDEX__/g, variantCounter);
        
        // Crear un div temporal para añadir el nuevo bloque
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Añadir el bloque al contenedor
        container.appendChild(tempDiv.firstElementChild);
    });

    // Función para eliminar un bloque de variante (usando delegación de eventos)
    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-variant-btn')) {
            e.target.closest('.variant-form-block').remove();
        }
    });

    // Agregar la primera variante automáticamente al cargar la página
    addVariantBtn.click();
});
</script>

<?php include 'footer.php'; ?>