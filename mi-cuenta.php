<?php
// mi-cuenta.php (Versión Funcional y Mejorada)

session_start();

// 1. VERIFICACIÓN DE SEGURIDAD: Si no está logueado, redirigir al login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require __DIR__ . '/db.php';

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

// 2. LÓGICA PARA ACTUALIZAR DATOS (CUANDO SE ENVÍA EL FORMULARIO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    // Recolectar y limpiar los datos del formulario
    $nombre = trim($_POST['nombre_usuario']);
    $direccion = trim($_POST['direccion_usuario']);
    $telefono = trim($_POST['telefono_usuario']);

    // Validación simple
    if (empty($nombre) || empty($direccion) || empty($telefono)) {
        $mensaje = '<div class="account-message error">Todos los campos son obligatorios.</div>';
    } else {
        try {
            // Preparar y ejecutar la actualización en la base de datos
            $sql = "UPDATE usuarios SET nombre_usuario = ?, direccion_usuario = ?, telefono_usuario = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $direccion, $telefono, $usuario_id]);

            $_SESSION['mensaje_cuenta'] = '¡Tu información ha sido actualizada con éxito!';
            // Redirigir a la misma página para evitar reenvío del formulario
            header('Location: mi-cuenta.php');
            exit();

        } catch (Exception $e) {
            $mensaje = '<div class="account-message error">Error al actualizar la información. Por favor, inténtalo de nuevo.</div>';
        }
    }
}

// Mensaje de éxito después de la redirección
if (isset($_SESSION['mensaje_cuenta'])) {
    $mensaje = '<div class="account-message success">' . $_SESSION['mensaje_cuenta'] . '</div>';
    unset($_SESSION['mensaje_cuenta']);
}

// 3. OBTENER DATOS DEL USUARIO (siempre se ejecuta para mostrar la info)
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (Exception $e) {
    die("Error al obtener la información del usuario: " . $e->getMessage());
}

// 4. DETERMINAR SI ESTAMOS EN MODO EDICIÓN
$edit_mode = isset($_GET['action']) && $_GET['action'] === 'edit';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - 4E Bazar</title>
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        /* Estilos para los mensajes de éxito/error */
        .account-message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; }
        .account-message.success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .account-message.error { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>

    <main class="page-container" style="padding-top: 120px;">
        <div class="account-container">
            <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre_usuario']) ?></h1>
            <p class="subtitle">
                <?php echo $edit_mode ? 'Aquí puedes editar tu información personal.' : 'Aquí puedes ver la información de tu cuenta.'; ?>
            </p>
            
            <?php echo $mensaje; // Muestra mensajes de éxito o error ?>

            <?php if ($edit_mode): ?>
                <form action="mi-cuenta.php" method="POST">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label" for="nombre_usuario">Nombre Completo</label>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                        </div>
                        <div class="info-item">
                            <label class="info-label" for="correo_usuario">Correo Electrónico (no editable)</label>
                            <input type="email" id="correo_usuario" class="form-control" value="<?= htmlspecialchars($usuario['correo_usuario']) ?>" readonly disabled>
                        </div>
                        <div class="info-item">
                            <label class="info-label" for="rut_usuario">RUT (no editable)</label>
                            <input type="text" id="rut_usuario" class="form-control" value="<?= htmlspecialchars($usuario['rut_usuario']) ?>" readonly disabled>
                        </div>
                        <div class="info-item">
                            <label class="info-label" for="direccion_usuario">Dirección</label>
                            <input type="text" id="direccion_usuario" name="direccion_usuario" class="form-control" value="<?= htmlspecialchars($usuario['direccion_usuario']) ?>" required>
                        </div>
                        <div class="info-item">
                            <label class="info-label" for="telefono_usuario">Teléfono</label>
                            <input type="text" id="telefono_usuario" name="telefono_usuario" class="form-control" value="<?= htmlspecialchars($usuario['telefono_usuario']) ?>" required>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Miembro desde</label>
                            <input type="text" class="form-control" value="<?= date("d/m/Y", strtotime($usuario['fecha_registro'])) ?>" readonly disabled>
                        </div>
                    </div>
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="submit" name="update_account" class="edit-btn">Guardar Cambios</button>
                        <a href="mi-cuenta.php" class="edit-btn" style="background-color: #6c757d;">Cancelar</a>
                    </div>
                </form>

            <?php else: ?>
                <div class="info-grid">
                    <div class="info-item"><span class="info-label">Nombre Completo</span><span class="info-value"><?= htmlspecialchars($usuario['nombre_usuario']) ?></span></div>
                    <div class="info-item"><span class="info-label">Correo Electrónico</span><span class="info-value"><?= htmlspecialchars($usuario['correo_usuario']) ?></span></div>
                    <div class="info-item"><span class="info-label">RUT</span><span class="info-value"><?= htmlspecialchars($usuario['rut_usuario']) ?></span></div>
                    <div class="info-item"><span class="info-label">Dirección</span><span class="info-value"><?= htmlspecialchars($usuario['direccion_usuario']) ?></span></div>
                    <div class="info-item"><span class="info-label">Teléfono</span><span class="info-value"><?= htmlspecialchars($usuario['telefono_usuario']) ?></span></div>
                    <div class="info-item"><span class="info-label">Miembro desde</span><span class="info-value"><?= date("d/m/Y", strtotime($usuario['fecha_registro'])) ?></span></div>
                </div>
                <a href="mi-cuenta.php?action=edit" class="edit-btn" style="text-decoration: none;">Editar Información</a>
            <?php endif; ?>

        </div>
    </main>

        <aside class="cart-sidebar">
      <div class="cart-header"><h3>Tu Carrito</h3><button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button></div>
      <div class="cart-body"><p class="cart-empty-msg">Tu carrito está vacío.</p></div>
      <div class="cart-footer">
        <div class="cart-total"><strong>Total:</strong><span id="cart-total-price">$0</span></div>
        
        <div class="shipping-options" id="shipping-options" style="display: none;">
            <h4>Selecciona un método de entrega</h4>
            <div class="shipping-option">
                <input type="radio" id="shipping-pickup" name="shipping" value="Retiro en tienda física">
                <label for="shipping-pickup">Retiro en tienda física</label>
            </div>
            <div class="shipping-option">
                <input type="radio" id="shipping-delivery" name="shipping" value="Envío a domicilio">
                <label for="shipping-delivery">Envío a domicilio</label>
            </div>
        </div>
        
        <button class="btn-checkout" id="btn-finalize-purchase" disabled><i class="fab fa-whatsapp"></i> Pedir por WhatsApp</button>
        </div>
    </aside>
    <div class="cart-overlay"></div>
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
</body>
</html>