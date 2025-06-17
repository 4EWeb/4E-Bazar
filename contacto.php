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

    <footer class="social-panel">
        <h2 class="texto-blanco">Síguenos en redes sociales</h2>
        <div class="iconos-horizontales">
            <a href="https://www.instagram.com/bazarycomercial.4e/" class="social-icon instagram" target="_blank" title="Instagram">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
            </a>
            <a href="https://api.whatsapp.com/send/?phone=56976509490&text&type=phone_number&app_absent=0" class="social-icon whatsapp" target="_blank" title="WhatsApp">
                <svg viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            </a>
        </div>
      </footer>
    
    <footer><p>&copy; <?= date('Y') ?> 4E Bazar. Todos los derechos reservados.</p></footer>
    <aside class="cart-sidebar">
      <div class="cart-header"><h3>Tu Carrito</h3><button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button></div>
      <div class="cart-body"><p class="cart-empty-msg">Tu carrito está vacío.</p></div>
      <div class="cart-footer">
        <div class="cart-total"><strong>Total:</strong><span id="cart-total-price">$0</span></div>
        <button class="btn-checkout" id="btn-finalize-purchase"><i class="fab fa-whatsapp"></i> Pedir por WhatsApp</button>
      </div>
    </aside>
    <div class="cart-overlay"></div>
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
</body>
</html>