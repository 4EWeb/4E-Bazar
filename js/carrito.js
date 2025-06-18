// ===================================================================================
// CARRITO.JS - GESTIÓN COMPLETA DEL CARRITO DE COMPRAS
// ===================================================================================

// Variable global para que las funciones onclick de los productos puedan acceder a ella.
let agregarAlCarrito;

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

  // === ESTADO DEL CARRITO ===
  // Carga el carrito desde la memoria local del navegador o lo inicia como un array vacío.
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // === FUNCIONES PRINCIPALES ===

  /** Guarda el estado actual del carrito en la memoria local del navegador. */
  const saveCart = () => {
    localStorage.setItem("cart", JSON.stringify(cart));
  };

  /** Abre el panel lateral del carrito. */
  const openCart = () => {
    if(cartSidebar) cartSidebar.classList.add("active");
    if(cartOverlay) cartOverlay.classList.add("active");
  };

  /** Cierra el panel lateral del carrito. */
  const closeCart = () => {
    if(cartSidebar) cartSidebar.classList.remove("active");
    if(cartOverlay) cartOverlay.classList.remove("active");
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
      cartTotalPriceEl.textContent = `$${totalPrice.toLocaleString("es-CL")}`;
    }
  };

  /** Dibuja los ítems (productos y servicios) dentro del panel del carrito. */
  const renderCartItems = () => {
    if (!cartBody) return;

    cartBody.innerHTML = "";
    const hasItems = cart.length > 0;

    emptyCartMsg.style.display = hasItems ? "none" : "block";
    if(finalizePurchaseBtn) finalizePurchaseBtn.disabled = !hasItems;

    if (!hasItems) {
      cartBody.appendChild(emptyCartMsg);
      return;
    }

    cart.forEach((item) => {
      const cartItemDiv = document.createElement("div");
      cartItemDiv.dataset.id = item.id;
      cartItemDiv.className = "cart-item";

      // Si es un producto normal (no un servicio)
      if (!item.name.includes('Impresión')) { // Una forma simple de diferenciar
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
      } else {
        // Si es un servicio personalizado
        cartItemDiv.innerHTML = `
          <div class="cart-item-info">
              <p class="cart-item-title">${item.name}</p>
              <p class="cart-item-price">$${item.price.toLocaleString("es-CL")}</p>
          </div>
          <button class="remove-item-btn" title="Eliminar servicio">
            <i class="fas fa-trash-alt"></i>
          </button>
        `;
      }
      cartBody.appendChild(cartItemDiv);
    });
  };

  /** Función global para agregar cualquier item al carrito. */
  agregarAlCarrito = (item) => {
    // Si el item ya tiene 'quantity', es un producto normal.
    // Si no, es un servicio y se le asigna quantity = 1.
    if (!item.quantity) {
        item.quantity = 1;
    }
    
    const existingItem = cart.find((i) => i.id === item.id);
    
    if (existingItem && !item.name.includes('Impresión')) { // Solo agrupa si es un producto
        existingItem.quantity += item.quantity;
    } else {
        // Si es un servicio o un producto nuevo, lo añade.
        cart.push(item);
    }
    
    updateCartUI();
    openCart();
  };

  // === EVENT LISTENERS (MANEJO DE INTERACCIONES) ===

  if (cartIconBtn) {
    cartIconBtn.addEventListener("click", openCart);
    cartCloseBtn.addEventListener("click", closeCart);
    cartOverlay.addEventListener("click", closeCart);
  }

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
  
  /** Función auxiliar para generar y abrir el enlace de WhatsApp */
  function generarUrlWhatsAppYRedirigir() {
    const phoneNumber = '56976509490';
    let message = '¡Hola! Me gustaría hacer el siguiente pedido:\n\n';

    cart.forEach(item => {
        message += `*${item.quantity}x* - ${item.name}\n`;
    });
    
    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    message += `\n*Total Estimado: $${totalPrice.toLocaleString('es-CL')}*`;

    // Añade la dirección del usuario si está logueado y la dirección existe
    if (window.IS_LOGGED_IN && window.USER_ADDRESS) {
        message += `\n\n*Dirección de Envío Registrada:*\n${window.USER_ADDRESS}`;
    }

    message += `\n\nPor favor, confírmame la disponibilidad y el valor final. ¡Gracias!`;

    const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappURL, '_blank');
  }

  // Lógica para el botón final de "Pedir por WhatsApp"
  if (finalizePurchaseBtn) {
    finalizePurchaseBtn.addEventListener('click', async () => {
        if (cart.length === 0) {
            alert('Tu carrito está vacío.');
            return;
        }

        // Si el usuario no ha iniciado sesión, le pedimos que lo haga para poder guardar su pedido.
        if (!window.IS_LOGGED_IN) {
            alert("Por favor, inicia sesión para que tu pedido quede guardado en tu cuenta antes de continuar.");
            window.location.href = 'login.php'; // Opcional: Redirigir al login
            return;
        }

        // Si el usuario SÍ ha iniciado sesión, intentamos guardar el pedido.
        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

        try {
            finalizePurchaseBtn.disabled = true;
            finalizePurchaseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            const response = await fetch('registrar-pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart, total: total }),
            });

            const result = await response.json();

            if (result.success) {
                // Si se guardó con éxito, AHORA abrimos WhatsApp
                generarUrlWhatsAppYRedirigir();
                
                // Limpiar el carrito después de un pedido exitoso
                cart = [];
                updateCartUI();
                closeCart();
            } else {
                alert('Hubo un error al registrar tu pedido: ' + (result.message || 'Error desconocido.'));
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

  // === INICIALIZACIÓN ===
  // Cargar el estado del carrito al iniciar la página.
  updateCartUI();
});