<?php
// Lo primero en una página con sesiones es iniciar la sesión
session_start();

// Si el usuario ya está logueado, redirigirlo a la página principal
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require __DIR__ . '/db.php';
$error_login = '';
$mensaje_exito = '';

// Mensaje de éxito si viene de la página de registro
if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso') {
    $mensaje_exito = "¡Registro completado! Por favor, inicia sesión.";
}

// Procesar el formulario de login cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo_usuario'] ?? '';
    $contrasena = $_POST['contrasena_usuario'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        $error_login = 'Por favor, ingrese correo y contraseña.';
    } else {
        // Buscar al usuario por su correo electrónico
        $stmt = $pdo->prepare('SELECT id, nombre_usuario, contrasena_usuario FROM usuarios WHERE correo_usuario = ?');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        // Verificar si el usuario existe y si la contraseña es correcta
        if ($usuario && password_verify($contrasena, $usuario['contrasena_usuario'])) {
            // ¡Login exitoso!
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);

            // Guardar datos del usuario en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre_usuario'];

            // Redirigir a la página principal
            header('Location: index.php');
            exit();
        } else {
            // Si los datos son incorrectos
            $error_login = 'El correo o la contraseña son incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleslogin.css">
    <title>Iniciar Sesión - 4E Bazar</title>
</head>
<body>
    
    <img src="Imagenes/4e logo actualizado.png" class="logo" alt="Logo 4E Bazar">
    
    <div class="formulario">
        <h1>Inicio de Sesión</h1>

        <?php if ($error_login): ?>
            <p class="mensaje-login error"><?= $error_login ?></p>
        <?php endif; ?>
        <?php if ($mensaje_exito): ?>
            <p class="mensaje-login exito"><?= $mensaje_exito ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="username">
                <input type="email" name="correo_usuario" required>
                <label>Correo Electrónico</label>
            </div>
            <div class="username">
                <input type="password" name="contrasena_usuario" required>
                <label>Contraseña</label>
            </div>
            <div class="recordar"><a href="olvido-contrasena.php">¿Olvidó su contraseña?</a></div>
            <input type="submit" value="Iniciar">
            <div class="registrarse">
                <p>¿No tienes cuenta? <a href="formulario.php">Regístrate</a></p>
            </div>
        </form>
    </div>
</body>
</html>