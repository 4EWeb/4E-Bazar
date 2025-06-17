<?php
session_start();
$mensaje_envio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING));
    $mensaje = trim(filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING));

    // Validar los datos
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        $mensaje_envio = '<p class="mensaje error">Por favor, completa todos los campos obligatorios.</p>';
    } elseif (!$email) {
        $mensaje_envio = '<p class="mensaje error">El correo electrónico no es válido.</p>';
    } else {
        $destinatario = "tu_correo@tu_dominio.com"; 
        $asunto = "Nuevo mensaje de contacto desde 4E Bazar de: " . $nombre;

        $cuerpo_mensaje = "Has recibido un nuevo mensaje de contacto:\n\n";
        $cuerpo_mensaje .= "Nombre: " . $nombre . "\n";
        $cuerpo_mensaje .= "Correo: " . $email . "\n";
        if (!empty($telefono)) {
            $cuerpo_mensaje .= "Teléfono: " . $telefono . "\n";
        }
        $cuerpo_mensaje .= "\nMensaje:\n" . $mensaje;

        $headers = 'From: ' . $nombre . ' <' . $email . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $email . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();

        if (mail($destinatario, $asunto, $cuerpo_mensaje, $headers)) {
            $mensaje_envio = '<p class="mensaje exito">¡Gracias por tu mensaje! Te responderemos a la brevedad.</p>';
        } else {
            $mensaje_envio = '<p class="mensaje error">Lo sentimos, hubo un error al enviar tu mensaje. Por favor, intenta de nuevo.</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contacto - 4E Bazar</title>
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
</head>
<body>
    
    <?php include 'nav.php'; // Incluimos la barra de navegación modular ?>

    <main class="page-container" style="padding-top: 120px;">
        <div class="container-form">
            <div class="info-form">
                <h1>Contáctanos</h1>
                <p>¿Tienes dudas, sugerencias o quieres hacer un pedido especial? Envíanos un mensaje o utiliza nuestros medios de contacto. ¡Te responderemos a la brevedad!</p>
                <div class="datos-contacto">
                    <a href="tel:56976509490" class="dato-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>+56 9 7650 9490</span>
                    </a>
                    <a href="mailto:contacto@4ebazar.com" class="dato-item">
                        <i class="fas fa-envelope"></i>
                        <span>contacto@4ebazar.com</span>
                    </a>
                </div>
                <div class="mapa-contacto">
                    <iframe
                        src="https://www.google.com/maps?q=Las+Parcelas+8122,+Peñalolén,+Región+Metropolitana,+Chile&output=embed"
                        width="100%" height="200" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            
            <div class="formulario-wrapper">
                <?= $mensaje_envio ?> <form method="POST" action="contacto.php" autocomplete="off">
                    <h2>Enviar un Mensaje</h2>
                    <div class="form-group">
                        <input type="text" name="nombre" placeholder="Tu nombre completo" class="campo" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Tu correo electrónico" class="campo" required>
                    </div>
                     <div class="form-group">
                        <input type="tel" name="telefono" placeholder="Tu teléfono (opcional)" class="campo">
                     </div>
                    <div class="form-group">
                        <textarea name="mensaje" placeholder="Escribe tu mensaje aquí..." required></textarea>
                    </div>
                    <input type="submit" name="enviar" value="Enviar Mensaje" class="btn-enviar">
                </form>
            </div>
        </div>
    </main>
    
    <footer><p>&copy; <?= date('Y') ?> 4E Bazar. Todos los derechos reservados.</p></footer>
    <aside class="cart-sidebar"></aside>
    <div class="cart-overlay"></div>
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
</body>
</html>