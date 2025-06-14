<?php
// Variable para mostrar mensajes de error al usuario
$mensaje = '';

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/db.php'; 

    $nombre = trim($_POST['nombre_usuario'] ?? '');
    $rut = trim($_POST['rut_usuario'] ?? '');
    $direccion = trim($_POST['direccion_usuario'] ?? '');
    $telefono = trim($_POST['telefono_usuario'] ?? '');
    $correo = trim($_POST['correo_usuario'] ?? '');
    $contrasena = $_POST['contrasena_usuario'] ?? '';
    
    if (empty($nombre) || empty($rut) || empty($direccion) || empty($telefono) || empty($correo) || empty($contrasena)) {
        $mensaje = '<p class="mensaje error">Todos los campos son obligatorios.</p>';
    } 
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<p class="mensaje error">El formato del correo electrónico no es válido.</p>';
    }
    else {
        try {
            $password_hashed = password_hash($contrasena, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre_usuario, rut_usuario, direccion_usuario, telefono_usuario, correo_usuario, contrasena_usuario)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $rut, $direccion, $telefono, $correo, $password_hashed]);

            // ==================================================================
            // MODIFICACIÓN CLAVE: Redirigir al login en caso de éxito
            // ==================================================================
            header("Location: login.php?registro=exitoso");
            exit(); // Es importante terminar el script después de una redirección

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensaje = '<p class="mensaje error">Error: El RUT o el correo electrónico ya se encuentran registrados.</p>';
            } else {
                $mensaje = '<p class="mensaje error">Error al registrar el usuario. Por favor, inténtelo más tarde.</p>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesformulario.css">
    <title>Formulario de Registro - 4E Bazar</title>
    <style>
        .mensaje { padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center; font-weight: bold; }
        .mensaje.exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .mensaje.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <img src="Imagenes/4e logo actualizado.png" class="logo" alt="Logo 4E Bazar">

    <div class="form-container">
        <?= $mensaje ?>

        <form class="form-registro" method="POST" action="formulario.php" novalidate>
            <h4>Formulario de Registro</h4>
            <input class="controls" type="text" name="nombre_usuario" id="nombre" placeholder="Ingrese su Nombre y Apellido" required>
            <input class="controls" type="text" name="rut_usuario" id="rut" placeholder="Ingrese su RUT (sin puntos ni guión)" required>
            <input class="controls" type="text" name="direccion_usuario" id="direccion" placeholder="Ingrese su Dirección" required>
            <input class="controls" type="tel" name="telefono_usuario" id="telefono" placeholder="Ingrese su Número de Teléfono (9xxxxxxxx)" pattern="[0-9]{9}" title="Ingrese 9 dígitos, sin +56" required>       
            <input class="controls" type="email" name="correo_usuario" id="correo" placeholder="Ingrese su Correo" required>
            <input class="controls" type="password" name="contrasena_usuario" id="contraseña" placeholder="Ingrese su Contraseña" required>
            <p>Estoy de acuerdo con <a href="#">Términos y Condiciones</a></p>
            <input class="botons" type="submit" value="Registrarse">
            <p><a href="login.php">¿Ya tienes una cuenta? Inicia Sesión</a></p>
        </form>
    </div>

    <script src="formulario.js"></script>
</body>
</html>