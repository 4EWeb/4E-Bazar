<?php
// Iniciar la sesión para verificar si el usuario está logueado.
session_start();

// VERIFICACIÓN DE SEGURIDAD: Si no existe la variable de sesión, redirigir al login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// OBTENER LOS PEDIDOS DEL USUARIO LOGUEADO
require __DIR__ . '/db.php';
$usuario_id = $_SESSION['usuario_id'];
$pedidos = [];

// --- INICIO: FUNCIÓN PARA LIMPIAR EL TEXTO DEL ESTADO ---
// Esta función convierte "En preparación" a "en-preparacion"
function slugify_status($text) {
    $text = strtolower($text);
    $text = str_replace(' ', '-', $text);
    $text = str_replace('ó', 'o', $text); // Reemplaza la 'ó' por 'o'
    return preg_replace('/[^a-z0-9-]/', '', $text); // Elimina cualquier otro caracter no válido
}
// --- FIN: FUNCIÓN PARA LIMPIAR EL TEXTO DEL ESTADO ---

try {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha_pedido DESC");
    $stmt->execute([$usuario_id]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error al obtener los pedidos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - 4E Bazar</title>
    
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
            <h1>Mis Pedidos</h1>
            <p class="subtitle">Aquí puedes ver el historial de todas tus compras.</p>
            
            <div class="orders-list">
                <?php if (empty($pedidos)): ?>
                    <p>Aún no has realizado ningún pedido.</p>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Monto Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td data-label="ID Pedido">#<?= htmlspecialchars($pedido['id_pedido']) ?></td>
                                    <td data-label="Fecha"><?= date("d/m/Y", strtotime($pedido['fecha_pedido'])) ?></td>
                                    <td data-label="Monto Total">$<?= number_format($pedido['monto_total'], 0, ',', '.') ?></td>
                                    <td data-label="Estado">
                                        <span class="status-badge status-<?= slugify_status($pedido['estado']) ?>">
                                            <?= htmlspecialchars($pedido['estado']) ?>
                                        </span>
                                    </td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
             <a href="catalogo.php" class="edit-btn" style="margin-top: 30px; text-decoration: none;">Ir al Catálogo</a>
        </div>
    </main>

    <aside class="cart-sidebar">
      <div class="cart-header"><h3>Tu Carrito</h3><button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button></div>
      <div class="cart-body"><p class="cart-empty-msg">Tu carrito está vacío.</p></div>
      <div class="cart-footer">
        <div class="cart-summary">
            <div class="cart-summary-row">
                <span>Subtotal</span>
                <span id="cart-subtotal-price">$0</span>
            </div>
            <div class="cart-summary-row" id="shipping-cost-row">
                <span>Envío</span>
                <span id="cart-shipping-price">$0</span>
            </div>
        </div>
        
        <div class="cart-final-total">
            <span>Total</span>
            <span id="cart-total-price">$0</span>
        </div>
    
        <div class="shipping-options" id="shipping-options" style="display: none;">
            <h4>Selecciona un método de entrega</h4>
            <div class="shipping-option">
                <input type="radio" id="shipping-pickup" name="shipping" value="Retiro en tienda física">
                <label for="shipping-pickup">Retiro en tienda física</label>
            </div>
            <div class="shipping-option">
                <input type="radio" id="shipping-delivery" name="shipping" value="Envío a domicilio">
                <label for="shipping-delivery">Envío a domicilio</label>
            </div>
        </div>
        
        <button class="btn-checkout" id="btn-finalize-purchase" disabled>
            <i class="fab fa-whatsapp"></i> Pedir por WhatsApp
        </button>
      </div>
    </aside>
    <div class="cart-overlay"></div>
    <script src="js/carrito.js"></script>
    <script src="js/nav-responsive.js"></script>
</body>
</html>