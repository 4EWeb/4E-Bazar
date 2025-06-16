<?php
// admin/agregar_producto.php
// require 'verificar_sesion.php';
require '../db.php'; // Asegúrate de que la ruta sea correcta

$categorias = $pdo->query("SELECT id, nombreCategoria FROM categorias ORDER BY nombreCategoria ASC")->fetchAll(PDO::FETCH_ASSOC);
$upload_message = ""; // Variable para mensajes de subida

// Lógica para procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    
    $imagen_path_for_db = null; // Por defecto, no hay imagen

    // --- LÓGICA PARA MANEJAR LA SUBIDA DE LA IMAGEN ---
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "../Imagenes/productos/"; // Ruta desde la carpeta 'admin' hacia la de imágenes
        
        // Crear un nombre de archivo único para evitar sobreescribir
        $original_name = basename($_FILES["imagen"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_name = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_name;

        // Validaciones de la imagen
        $check = getimagesize($_FILES["imagen"]["tmp_name"]);
        if($check !== false) {
            if ($_FILES["imagen"]["size"] < 5000000) { // Límite de 5MB
                if(in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                    // Mover el archivo subido a la carpeta de destino
                    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                        // Guardar la ruta relativa a la raíz del proyecto para la BD
                        $imagen_path_for_db = "Imagenes/productos/" . $unique_name;
                    } else {
                        $upload_message = "Error: Hubo un problema al mover el archivo.";
                    }
                } else {
                    $upload_message = "Error: Solo se permiten archivos JPG, JPEG, PNG y GIF.";
                }
            } else {
                $upload_message = "Error: El archivo es demasiado grande (máximo 5MB).";
            }
        } else {
            $upload_message = "Error: El archivo no es una imagen válida.";
        }
    }

    // --- LÓGICA PARA INSERTAR EN LA BASE DE DATOS ---
    if (empty($upload_message)) { // Solo proceder si no hubo errores de subida
        $sql = "INSERT INTO productos (nombre, descripcion, precio, cantidad, destacado, descuento, categoriaID, imagen) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['cantidad'],
            $_POST['destacado'],
            $_POST['descuento'],
            $_POST['categoriaID'],
            $imagen_path_for_db // Guardar la ruta de la imagen o NULL
        ]);
        
        header("Location: gestionar_productos.php?status=success");
        exit();
    }
}

include 'header.php';
?>

<h1>Agregar Nuevo Producto</h1>
<hr>

<?php if (!empty($upload_message)): ?>
    <div class="alert alert-danger"><?php echo $upload_message; ?></div>
<?php endif; ?>

<form action="agregar_producto.php" method="POST" class="row g-3" enctype="multipart/form-data">
    <div class="col-md-12">
        <label for="nombre" class="form-label">Nombre del Producto</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>
    <div class="col-md-12">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
    </div>

    <div class="col-md-12">
        <label for="imagen" class="form-label">Imagen del Producto</label>
        <input class="form-control" type="file" id="imagen" name="imagen">
    </div>

    <div class="col-md-6">
        <label for="precio" class="form-label">Precio</label>
        <input type="number" class="form-control" id="precio" name="precio" required>
    </div>
    <div class="col-md-6">
        <label for="cantidad" class="form-label">Stock Inicial (Cantidad)</label>
        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
    </div>
     <div class="col-md-4">
        <label for="categoriaID" class="form-label">Categoría</label>
        <select id="categoriaID" name="categoriaID" class="form-select" required>
            <option value="" selected disabled>Elige...</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombreCategoria']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label for="destacado" class="form-label">Destacado</label>
        <select id="destacado" name="destacado" class="form-select">
            <option value="No" selected>No</option>
            <option value="Si">Sí</option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="descuento" class="form-label">Descuento (%)</label>
        <input type="number" class="form-control" id="descuento" name="descuento" value="0" min="0" max="100">
    </div>
    <div class="col-12">
        <button type="submit" name="add_product" class="btn btn-primary">Agregar Producto</button>
        <a href="gestionar_productos.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php include 'footer.php'; ?>