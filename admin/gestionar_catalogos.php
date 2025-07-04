<?php
require 'admin_functions.php';

// Procesar formularios
handle_catalog_requests($pdo);

// Obtener datos para mostrar
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll();
$atributos = $pdo->query("SELECT * FROM atributos ORDER BY nombre")->fetchAll();
$opciones_agrupadas = $pdo->query("SELECT a.nombre as nombre_atributo, o.valor FROM opciones o JOIN atributos a ON o.id_atributo = a.id_atributo ORDER BY a.nombre, o.valor")->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h1>Gestionar Catálogos</h1>
    <p>Administra las categorías, atributos (ej. Color, Talla) y sus opciones (ej. Rojo, Azul, M, L).</p>
</div>
<hr>

<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header"><h4>Categorías</h4></div>
            <div class="card-body">
                <ul class="list-group mb-3" style="list-style: none; padding: 0;">
                    <?php foreach($categorias as $c): ?>
                        <li style="padding: .5rem .75rem; border: 1px solid #ddd; margin-bottom: -1px;"><?php echo htmlspecialchars($c['nombre_categoria']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre_categoria" class="form-input" placeholder="Nueva categoría" required>
                        <button class="btn btn-primary" type="submit" name="save_categoria">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header"><h4>Atributos</h4></div>
            <div class="card-body">
                <ul class="list-group mb-3" style="list-style: none; padding: 0;">
                     <?php foreach($atributos as $a): ?>
                        <li style="padding: .5rem .75rem; border: 1px solid #ddd; margin-bottom: -1px;"><?php echo htmlspecialchars($a['nombre']); ?></li>
                    <?php endforeach; ?>
                </ul>
                 <form action="gestionar_catalogos.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="nombre" class="form-input" placeholder="Nuevo atributo (ej. Talla)" required>
                        <button class="btn btn-primary" type="submit" name="save_atributo">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header"><h4>Opciones</h4></div>
            <div class="card-body">
                <?php $current_attr = ''; foreach($opciones_agrupadas as $o): ?>
                    <?php if ($o['nombre_atributo'] != $current_attr): ?>
                        <?php if($current_attr != ''): ?><br><br><?php endif; ?>
                        <?php $current_attr = $o['nombre_atributo']; ?>
                        <strong><?php echo htmlspecialchars($current_attr); ?>:</strong><br>
                    <?php endif; ?>
                    <span class="badge bg-secondary" style="margin-right: 5px;"><?php echo htmlspecialchars($o['valor']); ?></span>
                <?php endforeach; ?>
                <hr>
                <form action="gestionar_catalogos.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">Agregar opción al atributo:</label>
                        <select name="id_atributo_para_opcion" class="form-select" required>
                            <option value="">Selecciona atributo</option>
                            <?php foreach($atributos as $a): ?>
                                <option value="<?php echo $a['id_atributo']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor de la nueva opción:</label>
                        <input type="text" name="valor" class="form-input" placeholder="Ej: Rojo, M, Algodón" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="add_opcion">Agregar Opción</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>