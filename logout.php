<?php
// Es crucial iniciar la sesión para poder acceder a ella y destruirla.
session_start();

// 1. Limpia todas las variables de la sesión.
$_SESSION = array();

// 2. Destruye la sesión completamente.
// Esto eliminará la sesión, y no solo los datos de la sesión.
session_destroy();

// 3. Redirige al usuario a la página de login con un mensaje de éxito.
// El '?logout=exitoso' es para que la página de login sepa que mostrar el mensaje.
header("Location: login.php?logout=exitoso");
exit; // Es importante usar exit() después de una redirección para detener la ejecución del script.
?>