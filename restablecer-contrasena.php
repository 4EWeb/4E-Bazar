<?php
require __DIR__ . '/db.php';
$mensaje = '';
$token_valido = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Buscar el token en la BD y asegurarse de que no haya expirado
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token_restablecimiento = ? AND token_expiracion > NOW()");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token_valido = true;
    } else {
        $mensaje = '<p class="mensaje error">El enlace de restablecimiento es inválido o ha expirado.</p>';
    }
} else {
    // Si no hay token en la URL, redirigir
    header('Location: login.php');
    exit;
}

// Procesar el cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $contrasena1 = $_POST['contrasena1'];
    $contrasena2 = $_POST['contrasena2'];

    if (empty($contrasena1) || empty($contrasena2)) {
        $mensaje = '<p class="mensaje error">Ambos campos de contraseña son obligatorios.</p>';
    } elseif ($contrasena1 !== $contrasena2) {
        $mensaje = '<p class="mensaje error">Las contraseñas no coinciden.</p>';
    } else {
        // Todo correcto, actualizamos la contraseña
        $password_hashed = password_hash($contrasena1, PASSWORD_DEFAULT);
        
        // Actualizamos la contraseña y limpiamos el token para que no se pueda volver a usar
        $update_stmt = $pdo->prepare("UPDATE usuarios SET contrasena_usuario = ?, token_restablecimiento = NULL, token_expiracion = NULL WHERE id = ?");
        $update_stmt->execute([$password_hashed, $usuario['id']]);
        
        $mensaje = '<p class="mensaje exito">¡Tu contraseña ha sido actualizada con éxito! Ya puedes <a href="login.php">iniciar sesión</a>.</p>';
        $token_valido = false; // Ocultar el formulario después de actualizar
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleslogin.css">
    <title>Restablecer Contraseña - 4E Bazar</title>
</head>
<body>
    <a href="index.php"><img src="Imagenes/4e logo actualizado.png" class="logo" alt="Logo 4E Bazar"></a>
    <div class="formulario">
        <h1>Restablecer Contraseña</h1>
        <?= $mensaje ?>

        <?php if ($token_valido): ?>
            <form method="POST" action="restablecer-contrasena.php?token=<?= htmlspecialchars($token) ?>">
                <div class="username">
                    <input type="password" name="contrasena1" required>
                    <label>Nueva Contraseña</label>
                </div>
                <div class="username">
                    <input type="password" name="contrasena2" required>
                    <label>Confirmar Nueva Contraseña</label>
                </div>
                <input type="submit" value="Guardar Contraseña">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>