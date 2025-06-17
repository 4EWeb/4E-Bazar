<?php
// Iniciar la sesión para verificar si el usuario está logueado.
session_start();

// 1. VERIFICACIÓN DE SEGURIDAD: Si no existe la variable de sesión, redirigir al login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit(); // Detener la ejecución del script.
}

// 2. OBTENER DATOS DEL USUARIO LOGUEADO
require __DIR__ . '/db.php';

// Obtenemos el ID del usuario desde la sesión.
$usuario_id = $_SESSION['usuario_id'];
$usuario = null;

try {
    // Preparamos la consulta para obtener todos los datos del usuario.
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si por alguna razón el usuario no se encuentra en la BD (ej. fue eliminado), cerramos su sesión.
    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

} catch (Exception $e) {
    die("Error al obtener la información del usuario: " . $e->getMessage());
}
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
</head>
<body>
    
    <?php include 'nav.php'; // Incluimos la barra de navegación modular ?>

    <main class="page-container" style="padding-top: 120px;">
        <div class="account-container">
            <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre_usuario']) ?></h1>
            <p class="subtitle">Aquí puedes ver la información de tu cuenta.</p>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre Completo</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['nombre_usuario']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Correo Electrónico</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['correo_usuario']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">RUT</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['rut_usuario']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dirección</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['direccion_usuario']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['telefono_usuario']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Miembro desde</span>
                    <span class="info-value"><?= date("d/m/Y", strtotime($usuario['fecha_registro'])) ?></span>
                </div>
            </div>

            <button class="edit-btn">Editar Información</button>
        </div>
    </main>

    <aside class="cart-sidebar"></aside>
    <div class="cart-overlay"></div>
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
</body>
</html>