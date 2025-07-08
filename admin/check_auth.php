<?php
// admin/check_auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si la variable de sesión del admin no existe,
// significa que no ha iniciado sesión.
if (!isset($_SESSION['admin_id'])) {
    // Redirigir a la página de login
    header('Location: login.php');
    // Detener la ejecución del script para que no se muestre nada más
    exit();
}
?>