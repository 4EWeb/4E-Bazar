<?php
require __DIR__ . '/db.php';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo_usuario'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<p class="mensaje error">Por favor, introduce un correo válido.</p>';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo_usuario = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Generar un token seguro y único
            $token = bin2hex(random_bytes(32));
            // Establecer una expiración (ej. 1 hora)
            $expiracion = date('Y-m-d H:i:s', time() + 3600);

            // Guardar el token y la expiración en la base de datos
            $update_stmt = $pdo->prepare("UPDATE usuarios SET token_restablecimiento = ?, token_expiracion = ? WHERE id = ?");
            $update_stmt->execute([$token, $expiracion, $usuario['id']]);

            // Construir el enlace de restablecimiento
            // IMPORTANTE: Cambia 'http://tu-sitio.com' por la URL real de tu web
            $enlace = 'http://tu-sitio.com/restablecer-contrasena.php?token=' . $token;

            // Enviar el correo (ver nota al final sobre esto)
            $asunto = 'Restablecimiento de Contraseña - 4E Bazar';
            $cuerpo = "Hola,\n\nPara restablecer tu contraseña, por favor haz clic en el siguiente enlace:\n" . $enlace . "\n\nSi no solicitaste esto, puedes ignorar este correo.\n\nGracias,\nEl equipo de 4E Bazar";
            $headers = 'From: no-reply@4ebazar.com';

            mail($correo, $asunto, $cuerpo, $headers);
        }
        
        $mensaje = '<p class="mensaje exito">Si tu correo existe en nuestro sistema, hemos enviado un enlace para restablecer tu contraseña.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleslogin.css">
    <title>Olvido de Contraseña - 4E Bazar</title>
</head>
<body>
    <a href="index.php"><img src="Imagenes/4e logo actualizado.png" class="logo" alt="Logo 4E Bazar"></a>
    <div class="formulario">
        <h1>Recuperar Contraseña</h1>
        <p style="color:#555; margin-bottom: 20px;">Introduce tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

        <?= $mensaje ?>

        <form method="POST" action="olvido-contrasena.php">
            <div class="username">
                <input type="email" name="correo_usuario" required>
                <label>Correo Electrónico</label>
            </div>
            <input type="submit" value="Enviar Enlace">
            <div class="registrarse">
               <p><a href="login.php">Volver a Inicio de Sesión</a></p>
            </div>
        </form>
    </div>
</body>
</html>