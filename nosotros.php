<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - 4E Bazar</title>
    
    <link rel="stylesheet" href="css/styles.css">     
    <link rel="stylesheet" href="css/layout.css">     
    <link rel="stylesheet" href="css/components.css"> 
    <link rel="stylesheet" href="css/cart.css">       
    <link rel="stylesheet" href="css/responsive.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

    <style>
      .page-container {
          padding-top: 90px;
          background-color: #fdf7f8;
      }
      .about-hero {
          position: relative;
          text-align: center;
          color: white;
          padding: 100px 20px;
          border-radius: 0 0 30% 30% / 0 0 10% 10%;
          overflow: hidden;
          background-size: cover;
          background-position: center 70%;
          background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('imagenes/bg.jpg');
      }
      .about-hero h1 { font-size: 3.5rem; font-weight: 800; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
      .about-hero .subtitle { font-size: 1.3rem; font-style: italic; max-width: 600px; margin: 10px auto 0; }
      .about-main-content { max-width: 1000px; margin: -80px auto 40px auto; position: relative; z-index: 2; background: #fff; padding: 40px 50px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
      .about-content { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: center; margin-bottom: 50px; }
      .about-text h2 { font-size: 2rem; color: #333; margin-bottom: 20px; position: relative; padding-bottom: 10px; }
      .about-text h2::after { content: ''; position: absolute; bottom: 0; left: 0; width: 60px; height: 4px; background-color: #e75480; border-radius: 2px; }
      .about-text p { font-size: 1.1rem; line-height: 1.8; color: #555; }
      .about-image img { width: 100%; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
      .values-section { text-align: center; padding-top: 40px; border-top: 1px solid #eee; }
      .values-section h2 { font-size: 2.2rem; color: #333; margin-bottom: 40px; }
      .values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
      .value-item { background: #fdf7f8; padding: 30px; border-radius: 15px; transition: all 0.3s ease; border: 1px solid #f0e4e8; }
      .value-item:hover { transform: translateY(-10px); box-shadow: 0 10px 25px rgba(231, 84, 128, 0.15); border-color: #e75480; }
      .value-item i { font-size: 3rem; color: #e75480; margin-bottom: 15px; }
      .value-item h3 { font-size: 1.4rem; color: #444; margin-bottom: 10px; }
      .value-item p { color: #666; line-height: 1.6; }
      @media (max-width: 768px) {
          .about-hero h1 { font-size: 2.5rem; }
          .about-content { grid-template-columns: 1fr; }
          .about-image { order: -1; margin-bottom: 30px; }
      }
    </style>
</head>
<body>
    
    <?php include 'nav.php'; ?>

    <main class="page-container">
        
        <section class="about-hero">
            <h1>Nuestra Historia</h1>
            <p class="subtitle">"Todo lo hago por mis hijos"</p>
        </section>

        <div class="about-main-content">
            <section class="about-content">
                <div class="about-text">
                    <h2>De un sueño familiar a tu tienda de confianza</h2>
                    <p>
                        4E Bazar nació en el corazón de <strong>Peñalolén</strong> como un pequeño emprendimiento familiar, con el sueño de ofrecer en un solo lugar todo lo que las familias y estudiantes necesitan para su día a día. Desde el principio, nuestro objetivo ha sido claro: combinar la variedad de un gran bazar con la atención cercana y amable de una tienda de barrio.
                    </p>
                    <p>
                        Con el tiempo y gracias a la confianza de nuestros increíbles clientes, hemos crecido, ampliando nuestro catálogo para incluir no solo los mejores útiles de papelería, sino también productos para el hogar, juguetes, artículos de fiesta y mucho más. Cada producto que seleccionamos es elegido con cariño, en ofrecer precios justos.
                    </p>
                    <p>
                        4E viene de las iniciales de mis hijos <strong>Emmanuel, Ezequiel, Estefania y Eleonor</strong> que son la razón para seguir adelante cada día. Cada rincón de nuestra tienda refleja el amor y dedicación que ponemos en todo lo que hacemos, porque creemos que cada cliente es parte de nuestra familia.
                    </p>
                </div>
                <div class="about-image">
                    <img src="imagenes/fondo-de-regreso-la-escuela-con-utiles-escolares-y-espacio-de-copia-en-el-cuaderno.jpg" alt="Interior de la tienda 4E Bazar">
                </div>
            </section>

            <section class="values-section">
                <h2>Nuestros Valores</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-star"></i>
                        <h3>Calidad y Variedad</h3>
                        <p>Nos esforzamos por ofrecer un catálogo amplio y de alta calidad para satisfacer todas tus necesidades.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-heart"></i>
                        <h3>Atención Cercana</h3>
                        <p>Creemos en el trato directo y amable. Estamos aquí para ayudarte a encontrar exactamente lo que buscas.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-users"></i>
                        <h3>Compromiso Local</h3>
                        <p>Somos de <strong>Peñalolén</strong> y para <strong>Peñalolén</strong>. Apoyar a nuestra comunidad es el motor de nuestro negocio.</p>
                    </div>
                </div>
            </section>
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