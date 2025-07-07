<?php
require 'admin_functions.php';

// Procesar formularios
handle_catalog_requests($pdo);

// Obtener datos para mostrar
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll();
$atributos = $pdo->query("SELECT * FROM atributos ORDER BY nombre")->fetchAll();
$opciones_agrupadas = [];
$stmt_opciones = $pdo->query("SELECT a.id_atributo, a.nombre as nombre_atributo, o.valor FROM opciones o JOIN atributos a ON o.id_atributo = a.id_atributo ORDER BY a.nombre, o.valor");
foreach ($stmt_opciones as $opcion) {
    $opciones_agrupadas[$opcion['nombre_atributo']][] = $opcion['valor'];
}


include 'header.php';
?>

<div class="page-header">
    <h1>Gestionar Catálogos</h1>
    <p>Administra las categorías, atributos (ej. Color, Talla) y sus opciones (ej. Rojo, Azul, M, L).</p>
</div>

<div class="row">
    <div class="col-4">
        <div class="card catalog-card">
            <div class="card-header"><h4>Categorías</h4></div>
            <div class="card-body">
                <div class="tag-list">
                    <?php foreach($categorias as $c): ?>
                        <span class="tag tag-blue"><?php echo htmlspecialchars($c['nombre_categoria']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer">
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre_categoria" class="form-control" placeholder="Nueva categoría" required>
                        <button class="btn btn-primary" type="submit" name="save_categoria">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card catalog-card">
            <div class="card-header"><h4>Atributos</h4></div>
            <div class="card-body">
                <div class="tag-list">
                     <?php foreach($atributos as $a): ?>
                        <span class="tag tag-green"><?php echo htmlspecialchars($a['nombre']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
             <div class="card-footer">
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre" class="form-control" placeholder="Nuevo atributo (ej. Talla)" required>
                        <button class="btn btn-primary" type="submit" name="save_atributo">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card catalog-card">
            <div class="card-header"><h4>Opciones</h4></div>
            <div class="card-body">
                <?php foreach($opciones_agrupadas as $nombre_attr => $opciones): ?>
                    <div class="option-group">
                        <h6 class="option-group-title"><?php echo htmlspecialchars($nombre_attr); ?></h6>
                        <div class="tag-list">
                            <?php foreach($opciones as $valor): ?>
                                <span class="tag tag-gray"><?php echo htmlspecialchars($valor); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="form-group mb-2">
                        <label class="form-label" style="font-size: 0.9rem;">Agregar opción al atributo:</label>
                        <select name="id_atributo_para_opcion" class="form-select" required>
                            <option value="">Selecciona un atributo</option>
                            <?php foreach($atributos as $a): ?>
                                <option value="<?php echo $a['id_atributo']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.9rem;">Valor de la nueva opción:</label>
                        <div class="input-group">
                            <input type="text" name="valor" class="form-control" placeholder="Ej: Rojo, M, Algodón" required>
                            <button class="btn btn-primary" type="submit" name="add_opcion">Agregar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>