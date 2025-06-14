<?php
require __DIR__ . '/db.php';

// Obtener precios de servicios desde la base de datos
$precios = [];
try {
    $stmt = $pdo->query("SELECT clave, valor FROM servicios_precios");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $row) {
        $precios[$row['clave']] = $row['valor'];
    }
} catch (Exception $e) {
    // Valores por defecto si hay error
    $precios = [
        'base' => 100,
        'bn' => 100,
        'color' => 200,
        'foto' => 300,
        'foto_papel' => 500,
        'termolaminado_media_carta' => 1200,
        'termolaminado_oficio' => 1500,
        'anillado_minimo' => 1000,
        'anillado_medio' => 1500,
        'anillado_maximo' => 2000
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Servicios de Impresión</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="cart-styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        .servicios-graficos {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .Servicios-box {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .Servicios-box label {
            display: flex;
            flex-direction: column;
            font-weight: 500;
        }
        
        .Servicios-box input[type="file"],
        .Servicios-box input[type="number"],
        .Servicios-box select,
        .Servicios-box textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 1rem;
        }
        
        .Servicios-box input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }
        
        .precio-total {
            font-size: 1.4rem;
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }
        
        .btn-servicios {
            background-color: #e75480;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-servicios:hover {
            background-color: #d14a6f;
        }
        
        .btn-servicios:active {
            transform: scale(0.98);
        }
    </style>
    <script>
        // Pasar precios de PHP a JavaScript
        window.PRECIOS_SERVICIO = <?php echo json_encode($precios); ?>;
    </script>
</head>
<body>
    <nav class="navbar-fijo">
        <div class="nav-content">
            <a href="index.php" class="logo-emprendimiento">
                <img src="Imagenes/4e logo actualizado.png" alt="Logo 4E Bazar" />
            </a>
            <ul class="menu-horizontal">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="contacto.html">Contacto</a></li>
            </ul>
            <div class="carrito-box">
                <button class="carrito-menu" id="cart-icon-btn" title="Ver carrito de compras">
                    <svg viewBox="0 0 576 512" width="32" height="32" fill="currentColor">
                        <path d="M528.12 301.319l47.273-208A16 16 0 0 0 560 80H120l-9.4-44.5A24 24 0 0 0 87 16H24A24 24 0 0 0 24 64h47.2l70.4 332.8A56 56 0 1 0 216 464h256a56 56 0 1 0 56-56H159.2l-7.2-32H528a16 16 0 0 0 15.12-12.681zM504 464a24 24 0 1 1-24-24 24 24 0 0 1 24 24zm-288 0a24 24 0 1 1-24-24 24 24 0 0 1 24 24z"/>
                    </svg>
                    <span id="contador-carrito" class="contador-carrito">0</span>
                </button>
            </div>
        </div>
    </nav>

    <main style="margin-top: 110px">
        <section class="ofertas-box servicios-graficos">
            <h1 class="ofertas-title">Crea tu Servicio de Impresión</h1>
            <form id="form-servicio" class="Servicios-box" autocomplete="off">
                <label>
                    Archivo a imprimir:
                    <input type="file" name="archivo" required />
                </label>
                <label>
                    Cantidad de impresiones:
                    <input
                        type="number"
                        name="cantidad"
                        id="cantidad"
                        min="1"
                        value="1"
                        required
                    />
                </label>
                <label for="anillado">
                    <input type="checkbox" name="anillado" id="anillado" />
                    Anillado
                </label>
                <label for="doble_cara">
                    <input type="checkbox" name="doble_cara" id="doble_cara" />
                    Impresión doble cara (descuento por hoja)
                </label>
                <label>
                    Tipo de impresión:
                    <select name="tipo_impresion" id="tipo_impresion">
                        <option value="bn">Blanco y negro (<?= '$' . $precios['bn'] ?>/hoja)</option>
                        <option value="color">Color (<?= '$' . $precios['color'] ?>/hoja)</option>
                        <option value="foto">Foto (<?= '$' . $precios['foto'] ?>/hoja)</option>
                        <option value="foto_papel">Foto en papel fotográfico (<?= '$' . $precios['foto_papel'] ?>/hoja)</option>
                    </select>
                </label>
                <label>
                    Termolaminado:
                    <select name="termolaminado" id="termolaminado">
                        <option value="ninguno">Ninguno</option>
                        <option value="media_carta">Media carta (+<?= '$' . $precios['termolaminado_media_carta'] ?>/hoja)</option>
                        <option value="oficio">Oficio (+<?= '$' . $precios['termolaminado_oficio'] ?>/hoja)</option>
                    </select>
                </label>
                <label>
                    Comentarios adicionales:
                    <textarea
                        name="comentarios"
                        id="comentarios"
                        maxlength="200"
                        rows="3"
                        placeholder="Máx. 200 caracteres"
                    ></textarea>
                </label>
                <div class="precio-total">
                    <strong>Total: $<span id="precio-total">0</span></strong>
                </div>
                <button
                    type="button"
                    class="btn-servicios"
                    id="agregar-carrito-servicio"
                >
                    Agregar al carrito
                </button>
            </form>
        </section>
    </main>

    <!-- Carrito lateral (mismo que en otras páginas) -->
    <aside class="cart-sidebar">
        <div class="cart-header">
            <h3>Tu Carrito</h3>
            <button class="cart-close-btn" aria-label="Cerrar carrito">&times;</button>
        </div>
        <div class="cart-body">
            <p class="cart-empty-msg">Tu carrito está vacío.</p>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <strong>Total:</strong>
                <span id="cart-total-price">$0</span>
            </div>
            <button class="btn-checkout" id="btn-finalize-purchase">
                <i class="fab fa-whatsapp"></i> Pedir por WhatsApp
            </button>
        </div>
    </aside>
    <div class="cart-overlay"></div>

    <script src="transiciones.js"></script>
    <script src="carrito.js"></script>
    <script src="servicios.js"></script>
</body>
</html>