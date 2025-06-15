let agregarAlCarrito;

function obtenerNombreImpresion(tipo) {
  const nombres = {
    bn: "Blanco y negro",
    color: "Color",
    foto: "Fotografía",
    foto_papel: "Foto en papel fotográfico",
  };
  return nombres[tipo] || "No especificado";
}

function obtenerNombreTermolaminado(tipo) {
  const nombres = {
    media_carta: "Media carta",
    oficio: "Oficio",
    ninguno: "Ninguno",
  };
  return nombres[tipo] || "Ninguno";
}


// --- LÓGICA PRINCIPAL DEL CARRITO ---
// Se ejecuta cuando todo el documento HTML ha sido cargado.
document.addEventListener("DOMContentLoaded", () => {
  // === SELECCIÓN DE ELEMENTOS DEL DOM ===
  const cartIconBtn = document.getElementById("cart-icon-btn");
  const cartSidebar = document.querySelector(".cart-sidebar");
  const cartCloseBtn = document.querySelector(".cart-close-btn");
  const cartOverlay = document.querySelector(".cart-overlay");
  const cartCounterSpan = document.getElementById("contador-carrito");
  const cartBody = document.querySelector(".cart-body");
  const cartTotalPriceEl = document.getElementById("cart-total-price");
  const emptyCartMsg = document.querySelector(".cart-empty-msg");
  const finalizePurchaseBtn = document.getElementById("btn-finalize-purchase");

if (finalizePurchaseBtn) {
        finalizePurchaseBtn.addEventListener('click', async () => {
            if (cart.length === 0) {
                alert('Tu carrito está vacío.');
                return;
            }

            // Si el usuario no ha iniciado sesión, simplemente abre WhatsApp como antes.
            if (!window.IS_LOGGED_IN) {
                alert("Por favor, inicia sesión para que tu pedido quede guardado en tu cuenta antes de continuar.");
                generarUrlWhatsAppYRedirigir(); // Simplemente abre WhatsApp
                return;
            }

          const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

            try {
                // Mostramos un estado de "guardando" en el botón
                finalizePurchaseBtn.disabled = true;
                finalizePurchaseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                const response = await fetch('registrar-pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ cart: cart, total: total }),
                });

                const result = await response.json();

                if (result.success) {
                    // Si se guardó con éxito, AHORA abrimos WhatsApp
                    generarUrlWhatsAppYRedirigir();
                    
                    // Opcional: limpiar el carrito después de un pedido exitoso
                    cart = [];
                    saveCart();
                    renderCartItems();
                } else {
                    // Si el servidor devolvió un error
                    alert('Hubo un error al registrar tu pedido: ' + result.message);
                }

            } catch (error) {
                console.error('Error de conexión:', error);
                alert('Hubo un error de conexión. No se pudo registrar el pedido.');
            } finally {
                 // Restauramos el botón a su estado original
                finalizePurchaseBtn.disabled = false;
                finalizePurchaseBtn.innerHTML = '<i class="fab fa-whatsapp"></i> Pedir por WhatsApp';
            }
        });
    }
    
 function generarUrlWhatsAppYRedirigir() {
        const phoneNumber = '56976509490';
        let message = '¡Hola! Me gustaría hacer el siguiente pedido:\n\n';

        cart.forEach(item => {
            message += `*${item.quantity}x* - ${item.name}\n`;
        });
        
        const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        message += `\n*Total: $${totalPrice.toLocaleString('es-CL')}*`;

        const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
        window.open(whatsappURL, '_blank');
    }

  // Carga el carrito desde la memoria local del navegador o lo inicia como un array vacío.
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // === FUNCIONES PRINCIPALES ===

  /** Guarda el estado actual del carrito en la memoria local del navegador. */
  const saveCart = () => {
    localStorage.setItem("cart", JSON.stringify(cart));
  };

  /** Abre el panel lateral del carrito. */
  const openCart = () => {
    cartSidebar.classList.add("active");
    cartOverlay.classList.add("active");
  };

  /** Cierra el panel lateral del carrito. */
  const closeCart = () => {
    cartSidebar.classList.remove("active");
    cartOverlay.classList.remove("active");
  };

  /** Función central para actualizar toda la interfaz del carrito. */
  const updateCartUI = () => {
    renderCartItems();
    updateCartInfo();
    saveCart();
  };

  /** Actualiza el contador de ítems y el precio total. */
  const updateCartInfo = () => {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    if (cartCounterSpan) cartCounterSpan.textContent = totalItems;
    if (cartTotalPriceEl) {
      cartTotalPriceEl.textContent = `$${totalPrice.toLocaleString("es-CL", { minimumFractionDigits: 0 })}`;
    }
  };

  /** Dibuja los ítems (productos y servicios) dentro del panel del carrito. */
  const renderCartItems = () => {
    if (!cartBody) return;

    cartBody.innerHTML = "";
    const hasItems = cart.length > 0;

    emptyCartMsg.style.display = hasItems ? "none" : "block";
    finalizePurchaseBtn.disabled = !hasItems;

    if (!hasItems) {
      cartBody.appendChild(emptyCartMsg);
      return;
    }

    cart.forEach((item) => {
      const cartItemDiv = document.createElement("div");
      cartItemDiv.dataset.id = item.id;
      cartItemDiv.className = "cart-item";

      // Lógica para renderizar un SERVICIO
      if (item.tipo === "servicio") {
        const servicio = item.detalles;
        cartItemDiv.classList.add("servicio");
        cartItemDiv.innerHTML = `
          <div class="cart-item-info">
              <p class="cart-item-title">${item.name}</p>
              <p class="cart-item-price">$${item.price.toLocaleString("es-CL")}</p>
              <div class="servicio-details">
                <p><strong>Cantidad:</strong> ${servicio.cantidad} hojas</p>
                <p><strong>Tipo:</strong> ${obtenerNombreImpresion(servicio.tipoImpresion)}</p>
                <p><strong>Termolaminado:</strong> ${obtenerNombreTermolaminado(servicio.termolaminado)}</p>
                <p><strong>Anillado:</strong> ${servicio.anillado ? "Sí" : "No"}</p>
                <p><strong>Doble cara:</strong> ${servicio.dobleCara ? "Sí" : "No"}</p>
                ${servicio.comentarios ? `<p><strong>Notas:</strong> ${servicio.comentarios}</p>`: ""}
              </div>
          </div>
          <button class="remove-item-btn" title="Eliminar servicio">
            <i class="fas fa-trash-alt"></i>
          </button>
        `;
      } 
      // Lógica para renderizar un PRODUCTO
      else {
        const itemTotalPrice = (item.price * item.quantity).toLocaleString("es-CL");
        cartItemDiv.innerHTML = `
          <img src="${item.image}" alt="${item.name}" class="cart-item-img">
          <div class="cart-item-info">
            <p class="cart-item-title">${item.name}</p>
            <p class="cart-item-price">$${itemTotalPrice}</p>
            <div class="cart-item-actions">
              <div class="quantity-controls">
                <button class="quantity-btn" data-action="decrease">-</button>
                <span class="item-quantity">${item.quantity}</span>
                <button class="quantity-btn" data-action="increase">+</button>
              </div>
              <button class="remove-item-btn" title="Eliminar producto">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
        `;
      }
      cartBody.appendChild(cartItemDiv);
    });
  };

  /** Función global para agregar cualquier item al carrito. */
  agregarAlCarrito = (item) => {
    // Si es un servicio, siempre se agrega como un nuevo ítem único.
    if (item.tipo === "servicio") {
      cart.push(item);
    } else {
      // Si es un producto, se busca si ya existe para aumentar la cantidad.
      const existingItem = cart.find(
        (i) => i.id === item.id && i.tipo !== "servicio"
      );
      if (existingItem) {
        existingItem.quantity++;
      } else {
        cart.push({ ...item, quantity: 1 });
      }
    }
    updateCartUI();
  };

  // === EVENT LISTENERS (MANEJO DE INTERACCIONES) ===

  // Abrir y cerrar el carrito
  if (cartIconBtn) {
    cartIconBtn.addEventListener("click", openCart);
    cartCloseBtn.addEventListener("click", closeCart);
    cartOverlay.addEventListener("click", closeCart);
  }

  // Manejar clics dentro del cuerpo del carrito (eliminar, +/- cantidad)
  if (cartBody) {
    cartBody.addEventListener("click", (e) => {
      const removeButton = e.target.closest(".remove-item-btn");
      const quantityButton = e.target.closest(".quantity-btn");

      if (!removeButton && !quantityButton) return;

      const cartItemElement = e.target.closest(".cart-item");
      const id = cartItemElement.dataset.id;
      
      if (removeButton) {
        cart = cart.filter((item) => item.id != id);
      }

      if (quantityButton) {
        const itemInCart = cart.find((item) => item.id == id);
        if (!itemInCart) return;

        const action = quantityButton.dataset.action;
        if (action === "increase") {
          itemInCart.quantity++;
        } else if (action === "decrease") {
          if (itemInCart.quantity > 1) {
            itemInCart.quantity--;
          } else {
            cart = cart.filter((item) => item.id != id);
          }
        }
      }
      updateCartUI();
    });
  }

  // Manejar el botón final para enviar el pedido
  if (finalizePurchaseBtn) {
    finalizePurchaseBtn.addEventListener("click", () => {
      if (cart.length === 0) return;
      
      const phoneNumber = "56976509490";
      let message = "¡Hola! Me gustaría cotizar lo siguiente:\n\n";

      // Mensaje para PRODUCTOS
      const productos = cart.filter((item) => item.tipo !== "servicio");
      if (productos.length > 0) {
        message += "*PRODUCTOS:*\n";
        productos.forEach((item) => {
          message += `- ${item.quantity}x ${item.name} ($${item.price.toLocaleString("es-CL")} c/u)\n`;
        });
        message += "\n";
      }

      // Mensaje para SERVICIOS
      const servicios = cart.filter((item) => item.tipo === "servicio");
      if (servicios.length > 0) {
        message += "*SERVICIOS PERSONALIZADOS:*\n";
        servicios.forEach((item) => {
          message += `- ${item.name} | Total: $${item.price.toLocaleString("es-CL")}\n`;
        });
      }

      // Total General
      const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
      message += `\n*TOTAL ESTIMADO: $${totalPrice.toLocaleString("es-CL")}*`;
      message += `\n\nPor favor, confírmame la disponibilidad y el valor final. ¡Gracias!`;

      // Redirección a WhatsApp
      const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
      window.open(whatsappURL, "_blank");

      // Vaciar carrito después de enviar
      cart = [];
      updateCartUI();
      closeCart();
    });
  }

  // === INICIALIZACIÓN ===
  // Cargar el estado del carrito al iniciar la página.
  updateCartUI();
});