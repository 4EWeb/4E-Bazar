<?php
//require 'verificar_sesion.php';
require '../db.php';

// --- LÓGICA PARA PROCESAR TODOS LOS FORMULARIOS DE ESTA PÁGINA ---

// Agregar/Editar Categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_categoria'])) {
    if (empty($_POST['id_categoria'])) { // Agregar
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (?)");
        $stmt->execute([$_POST['nombre_categoria']]);
    } else { // Editar
        $stmt = $pdo->prepare("UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ?");
        $stmt->execute([$_POST['nombre_categoria'], $_POST['id_categoria']]);
    }
    header("Location: gestionar_catalogos.php"); exit();
}

// Agregar/Editar Atributo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_atributo'])) {
    if (empty($_POST['id_atributo'])) { // Agregar
        $stmt = $pdo->prepare("INSERT INTO atributos (nombre) VALUES (?)");
        $stmt->execute([$_POST['nombre']]);
    } else { // Editar
        $stmt = $pdo->prepare("UPDATE atributos SET nombre = ? WHERE id_atributo = ?");
        $stmt->execute([$_POST['nombre'], $_POST['id_atributo']]);
    }
    header("Location: gestionar_catalogos.php"); exit();
}

// Agregar Opción a un Atributo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_opcion'])) {
    $stmt = $pdo->prepare("INSERT INTO opciones (id_atributo, valor) VALUES (?, ?)");
    $stmt->execute([$_POST['id_atributo_para_opcion'], $_POST['valor']]);
    header("Location: gestionar_catalogos.php"); exit();
}


// --- OBTENER DATOS PARA MOSTRAR ---
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll();
$atributos = $pdo->query("SELECT * FROM atributos ORDER BY nombre")->fetchAll();
$opciones_agrupadas = $pdo->query("SELECT a.nombre as nombre_atributo, o.valor FROM opciones o JOIN atributos a ON o.id_atributo = a.id_atributo ORDER BY a.nombre, o.valor")->fetchAll();

include 'header.php';
?>

<h1>Gestionar Catálogos</h1>
<p>Administra las categorías, atributos (ej. Color, Talla) y sus opciones (ej. Rojo, Azul, M, L).</p>
<hr>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4>Categorías</h4></div>
            <div class="card-body">
                <ul class="list-group mb-3">
                    <?php foreach($categorias as $c): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($c['nombre_categoria']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre_categoria" class="form-control" placeholder="Nueva categoría" required>
                        <button class="btn btn-primary" type="submit" name="save_categoria">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4>Atributos</h4></div>
            <div class="card-body">
                <ul class="list-group mb-3">
                     <?php foreach($atributos as $a): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($a['nombre']); ?></li>
                    <?php endforeach; ?>
                </ul>
                 <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nuevo atributo (ej. Talla)" required>
                        <button class="btn btn-primary" type="submit" name="save_atributo">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4>Opciones</h4></div>
            <div class="card-body">
                <?php $current_attr = ''; foreach($opciones_agrupadas as $o): ?>
                    <?php if ($o['nombre_atributo'] != $current_attr): ?>
                        <?php $current_attr = $o['nombre_atributo']; ?>
                        <strong><?php echo htmlspecialchars($current_attr); ?>:</strong>
                    <?php endif; ?>
                    <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($o['valor']); ?></span>
                <?php endforeach; ?>
                <hr>
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="mb-3">
                        <label>Agregar opción al atributo:</label>
                        <select name="id_atributo_para_opcion" class="form-select" required>
                            <option value="">Selecciona atributo</option>
                            <?php foreach($atributos as $a): ?>
                                <option value="<?php echo $a['id_atributo']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Valor de la nueva opción:</label>
                        <input type="text" name="valor" class="form-control" placeholder="Ej: Rojo, M, Algodón" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="add_opcion">Agregar Opción</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>