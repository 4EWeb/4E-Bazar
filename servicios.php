<?php
session_start();
require __DIR__ . '/db.php';

// Obtener precios de servicios desde la base de datos
$precios = [];
try {
    $stmt = $pdo->query("SELECT clave, valor FROM servicios_precios");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $row) {
        $precios[$row['clave']] = (float)$row['valor'];
    }
} catch (Exception $e) {
    // Si hay un error, usamos precios por defecto
    $precios = [
        'bn' => 100, 'color' => 200, 'foto' => 300, 'foto_papel' => 500,
        'termolaminado_media_carta' => 1200, 'termolaminado_oficio' => 1500,
        'anillado_minimo' => 1000, 'anillado_medio' => 1500, 'anillado_maximo' => 2000
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Servicios de Impresión - 4E Bazar</title>
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <script>
        // Pasamos los precios de PHP a una variable global de JavaScript
        window.PRECIOS_SERVICIO = <?php echo json_encode($precios); ?>;
    </script>
    <style>
        /* ESTILOS PARA QUE LA PÁGINA DE SERVICIOS SE VEA BIEN */
        .page-container {
            padding-top: 40px;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(231, 84, 128, 0.15);
            padding: 2rem 2.5rem;
            max-width: 800px;
            margin: 2rem auto;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .form-group, .form-group-checkbox {
            margin-bottom: 1.2rem;
        }
        .form-group label, .form-group-checkbox label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-control-file {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f8f9fa;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-control-file:focus {
            outline: none;
            border-color: #e75480;
            box-shadow: 0 0 0 3px rgba(231, 84, 128, 0.2);
        }
        .form-group-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #fdf2f7;
            border-radius: 8px;
        }
        .form-group-checkbox input[type="checkbox"] {
            width: 1.3em;
            height: 1.3em;
            accent-color: #e75480;
            cursor: pointer;
        }
        .form-group-checkbox label { margin-bottom: 0; font-weight: 500; cursor: pointer; }

        .precio-total {
            background: linear-gradient(135deg, #ffe0ec, #fff0f7);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            font-size: 1.4rem;
            color: #e75480;
            font-weight: bold;
            margin-top: 1.5rem;
            border: 2px solid rgba(231, 84, 128, 0.2);
        }
        /* Estilo para el botón que se había perdido */
        .btn-servicios {
            width: 100%;
            margin-top: 1.5rem;
            font-size: 1.2rem;
            padding: 16px 0;
            background: linear-gradient(135deg, #e75480, #ff6b9d, #f44336);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .btn-servicios:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(231, 84, 128, 0.4);
        }
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>

    <main class="page-container">
        <div class="form-container">
            <h1 class="ofertas-title">Crea tu Servicio de Impresión</h1>
            
            <form id="form-servicio" autocomplete="off" style="margin-top: 2rem;">
                <div class="form-group">
                    <label for="archivo">Archivo a imprimir:</label>
                    <input type="file" id="archivo" name="archivo" class="form-control-file" required />
                </div>
                
                <div class="form-group">
                    <label for="cantidad">Cantidad de hojas:</label>
                    <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" value="1" required />
                </div>
                
                <div class="form-group">
                    <label for="tipo_impresion">Tipo de impresión:</label>
                    <select name="tipo_impresion" id="tipo_impresion" class="form-control">
                        <option value="bn">Blanco y negro ($<?= number_format($precios['bn'] ?? 100, 0) ?>/hoja)</option>
                        <option value="color">Color ($<?= number_format($precios['color'] ?? 200, 0) ?>/hoja)</option>
                        <option value="foto">Foto ($<?= number_format($precios['foto'] ?? 300, 0) ?>/hoja)</option>
                        <option value="foto_papel">Foto en papel fotográfico ($<?= number_format($precios['foto_papel'] ?? 500, 0) ?>/hoja)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="termolaminado">Termolaminado:</label>
                    <select name="termolaminado" id="termolaminado" class="form-control">
                        <option value="ninguno">Ninguno</option>
                        <option value="media_carta">Media carta (+$<?= number_format($precios['termolaminado_media_carta'] ?? 1200, 0) ?>)</option>
                        <option value="oficio">Oficio (+$<?= number_format($precios['termolaminado_oficio'] ?? 1500, 0) ?>)</option>
                    </select>
                </div>
                
                <div class="form-group-checkbox">
                    <input type="checkbox" name="anillado" id="anillado" />
                    <label for="anillado">Anillado (Precio varía por cantidad de hojas)</label>
                </div>

                <div class="form-group">
                    <label for="comentarios">Comentarios adicionales:</label>
                    <textarea name="comentarios" id="comentarios" class="form-control" maxlength="200" rows="3" placeholder="Ej: Necesito 5 copias de este set..."></textarea>
                </div>
                
                <div class="precio-total">
                    <strong>Total Estimado: $<span id="precio-total">0</span></strong>
                </div>

                <button type="button" class="btn-servicios" id="agregar-carrito-servicio">
                    Agregar al Carrito
                </button>
            </form>
        </div>
    </main>

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
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("form-servicio");
        if (!form) return;

        const precioTotalSpan = document.getElementById("precio-total");
        const cantidadInput = document.getElementById("cantidad");
        const anilladoCheckbox = document.getElementById("anillado");
        const tipoImpresionSelect = document.getElementById("tipo_impresion");
        const termolaminadoSelect = document.getElementById("termolaminado");
        const agregarBtn = document.getElementById("agregar-carrito-servicio");

        const precios = window.PRECIOS_SERVICIO;

        function calcularTotal() {
            if (!precios) {
                console.error("Los precios no están definidos.");
                return 0;
            }
            const cantidad = parseInt(cantidadInput.value) || 0;
            const anillado = anilladoCheckbox.checked;
            const tipoImpresion = tipoImpresionSelect.value;
            const termolaminado = termolaminadoSelect.value;

            let total = 0;
            
            let precioPorHoja = precios[tipoImpresion] || 0;
            total += cantidad * precioPorHoja;

            if (anillado) {
                if (cantidad <= 25) total += precios.anillado_minimo || 1000;
                else if (cantidad <= 50) total += precios.anillado_medio || 1500;
                else total += precios.anillado_maximo || 2000;
            }
            if (termolaminado !== "ninguno") {
                const precioTermolaminado = precios['termolaminado_' + termolaminado] || 0;
                total += precioTermolaminado;
            }

            total = Math.max(0, total);
            precioTotalSpan.textContent = total.toLocaleString('es-CL');
            return total;
        }

        function agregarServicioAlCarrito() {
            const total = calcularTotal();
            if (total <= 0 || (parseInt(cantidadInput.value) || 0) <= 0) {
                alert("Por favor, selecciona una cantidad válida y opciones de impresión.");
                return;
            }

            let nombreServicio = `${cantidadInput.value}x Impresión ${tipoImpresionSelect.options[tipoImpresionSelect.selectedIndex].text.split('(')[0].trim()}`;
            if (anilladoCheckbox.checked) nombreServicio += " + Anillado";
            if (termolaminadoSelect.value !== "ninguno") nombreServicio += " + Termolaminado";
            
            const servicio = {
                id: `servicio-${Date.now()}`,
                name: nombreServicio,
                price: total,
                image: "Imagenes/4e logo actualizado.png",
                quantity: 1,
            };

            if (typeof agregarAlCarrito === "function") {
                agregarAlCarrito(servicio);
                agregarBtn.textContent = "✓ ¡Agregado!";
                agregarBtn.style.backgroundColor = "#28a745";
                setTimeout(() => {
                    agregarBtn.textContent = "Agregar al Carrito";
                    agregarBtn.style.backgroundColor = "";
                }, 2000);
            } else {
                alert("Error: El sistema de carrito no está funcionando.");
            }
        }

        form.addEventListener("input", calcularTotal);
        form.addEventListener("change", calcularTotal);
        agregarBtn.addEventListener("click", agregarServicioAlCarrito);
        
        // Calcular precio inicial
        calcularTotal();
    });
    </script>
</body>
</html>