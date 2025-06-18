<?php
// Asegurarnos que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializamos las variables que pasaremos a JavaScript
$is_logged_in_js = 'false';
$user_address_js = '';

// Si el usuario ha iniciado sesión, obtenemos sus datos
if (isset($_SESSION['usuario_id'])) {
    $is_logged_in_js = 'true';
    
    // Incluimos la conexión a la BD solo si es necesario
    require_once __DIR__ . '/db.php';
    
    try {
        $stmt = $pdo->prepare("SELECT direccion_usuario FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $direccion = $stmt->fetchColumn();
        // Preparamos la dirección para ser usada en JavaScript de forma segura
        $user_address_js = addslashes(htmlspecialchars($direccion ?: ''));
    } catch (Exception $e) {
        // En caso de error, la dirección simplemente quedará vacía
        $user_address_js = '';
    }
}
?>
<nav class="navbar-fijo">
    <div class="nav-content">
        <a href="index.php" class="logo-emprendimiento">
            <img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" />
        </a>

        <ul class="menu-horizontal" id="main-menu">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="catalogo.php">Catálogo</a></li>
            <li><a href="nosotros.php">Sobre Nosotros</a></li>
            <li><a href="contacto.php">Contacto</a></li>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="nav-mobile-auth"><a href="mi-cuenta.php">Mi Cuenta</a></li>
                <li class="nav-mobile-auth"><a href="logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li class="nav-mobile-auth"><a href="login.php">Iniciar Sesión</a></li>
                <li class="nav-mobile-auth"><a href="formulario.php">Registrarse</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-actions">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="login-box nav-desktop-auth">
                    <button class="login-menu">
                        <img src="Imagenes/icono-login.png" alt="Usuario" width="32" height="32" />
                    </button>
                    <ul class="submenu">
                        <li><a href="mi-cuenta.php">Mi Cuenta</a></li>
                        <li><a href="pedidos.php">Mis Pedidos</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="nav-auth-link nav-desktop-auth" title="Iniciar Sesión / Registrarse">
                    <i class="fas fa-right-to-bracket"></i>
                </a>
            <?php endif; ?>

            <div class="carrito-box">
                <button class="carrito-menu" id="cart-icon-btn" title="Ver carrito de compras">
                    <svg viewBox="0 0 576 512" width="32" height="32" fill="currentColor"><path d="M528.12 301.319l47.273-208A16 16 0 0 0 560 80H120l-9.4-44.5A24 24 0 0 0 87 16H24A24 24 0 0 0 24 64h47.2l70.4 332.8A56 56 0 1 0 216 464h256a56 56 0 1 0 56-56H159.2l-7.2-32H528a16 16 0 0 0 15.12-12.681zM504 464a24 24 0 1 1-24-24 24 24 0 0 1 24 24zm-288 0a24 24 0 1 1-24-24 24 24 0 0 1 24 24z"/></svg>
                    <span id="contador-carrito" class="contador-carrito">0</span>
                </button>
            </div>
            
            <button class="nav-toggle-btn" id="nav-toggle" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<script>
    window.IS_LOGGED_IN = <?= $is_logged_in_js ?>;
    window.USER_ADDRESS = '<?= $user_address_js ?>';
</script>