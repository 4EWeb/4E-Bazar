<?php
// logout.php
session_start();

// Limpia todas las variables de la sesión.
$_SESSION = array();

// Destruye la sesión.
session_destroy();

// --- MODIFICADO ---
// Redirige al nuevo login de administrador con un mensaje de éxito.
header("Location: login.php");
exit();
?>