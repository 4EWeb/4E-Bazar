// ===================================================================================
// CARRITO.JS - GESTIÓN COMPLETA DEL CARRITO DE COMPRAS (CON COSTO DE ENVÍO)
// ===================================================================================

// Variable global para que las funciones onclick de los productos puedan acceder a ella.
let agregarAlCarrito;

document.addEventListener("DOMContentLoaded", () => {
  // === CONSTANTES Y ELEMENTOS DEL DOM ===
  const SHIPPING_COST = 2990; // Costo de envío fijo
  const cartIconBtn = document.getElementById("cart-icon-btn");
  const cartSidebar = document.querySelector(".cart-sidebar");
  const cartCloseBtn = document.querySelector(".cart-close-btn");
  const cartOverlay = document.querySelector(".cart-overlay");
  const cartCounterSpan = document.getElementById("contador-carrito");
  const cartBody = document.querySelector(".cart-body");
  const cartSubtotalEl = document.getElementById("cart-subtotal-price");
  const cartShippingEl = document.getElementById("cart-shipping-price");
  const cartTotalEl = document.getElementById("cart-total-price");
  const shippingRow = document.getElementById("shipping-cost-row");
  const emptyCartMsg = document.querySelector(".cart-empty-msg");
  const finalizePurchaseBtn = document.getElementById("btn-finalize-purchase");
  const shippingOptionsContainer = document.getElementById('shipping-options');

  // === ESTADO DEL CARRITO ===
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // === FUNCIONES ===

  const saveCart = () => localStorage.setItem("cart", JSON.stringify(cart));
  const openCart = () => {
    if(cartSidebar) cartSidebar.classList.add("active");
    if(cartOverlay) cartOverlay.classList.add("active");
  };
  const closeCart = () => {
    if(cartSidebar) cartSidebar.classList.remove("active");
    if(cartOverlay) cartOverlay.classList.remove("active");
  };

  const updateCartUI = () => {
    renderCartItems();
    updateCartTotals();
    saveCart();
  };

  /**
   * Actualiza los totales (subtotal, envío y total) en la interfaz.
   */
  const updateCartTotals = () => {
      const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
      const selectedShipping = document.querySelector('input[name="shipping"]:checked');
      let shippingCost = 0;
      let finalTotal = subtotal;

      if (selectedShipping && selectedShipping.id === 'shipping-delivery') {
          shippingCost = SHIPPING_COST;
          finalTotal += shippingCost;
          if (shippingRow) shippingRow.style.display = 'flex';
      } else {
          if (shippingRow) shippingRow.style.display = 'none';
      }

      if (cartSubtotalEl) cartSubtotalEl.textContent = `$${subtotal.toLocaleString("es-CL")}`;
      if (cartShippingEl) cartShippingEl.textContent = `$${shippingCost.toLocaleString("es-CL")}`;
      if (cartTotalEl) cartTotalEl.textContent = `$${finalTotal.toLocaleString("es-CL")}`;
      
      const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
      if (cartCounterSpan) cartCounterSpan.textContent = totalItems;

      if (finalizePurchaseBtn) {
          finalizePurchaseBtn.disabled = !selectedShipping || cart.length === 0;
      }
  };

  const renderCartItems = () => {
    if (!cartBody) return;
    cartBody.innerHTML = "";
    const hasItems = cart.length > 0;

    if (emptyCartMsg) emptyCartMsg.style.display = hasItems ? "none" : "block";
    if (shippingOptionsContainer) shippingOptionsContainer.style.display = hasItems ? 'block' : 'none';

    if (!hasItems) {
      if(emptyCartMsg) cartBody.appendChild(emptyCartMsg);
      if (shippingRow) shippingRow.style.display = 'none'; // Oculta costo de envío si el carrito está vacío
      return;
    }

    cart.forEach(item => {
      const cartItemDiv = document.createElement("div");
      cartItemDiv.className = "cart-item";
      cartItemDiv.dataset.id = item.id;
      
      const itemTotalPrice = (item.price * item.quantity).toLocaleString("es-CL");

      if (!item.name.includes('Impresión')) { // Producto normal
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
              <button class="remove-item-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>`;
      } else { // Servicio
        cartItemDiv.innerHTML = `
          <div class="cart-item-info">
            <p class="cart-item-title">${item.name}</p>
            <p class="cart-item-price">$${item.price.toLocaleString("es-CL")}</p>
          </div>
          <button class="remove-item-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>`;
      }
      cartBody.appendChild(cartItemDiv);
    });
  };

  agregarAlCarrito = (item) => {
    item.quantity = item.quantity || 1;
    const existingItem = cart.find(i => i.id === item.id);
    if (existingItem && !item.name.includes('Impresión')) {
      existingItem.quantity += item.quantity;
    } else {
      cart.push(item);
    }
    updateCartUI();
    openCart();
  };

  // === MANEJO DE EVENTOS ===

  if (cartIconBtn) {
    cartIconBtn.addEventListener("click", openCart);
    if(cartCloseBtn) cartCloseBtn.addEventListener("click", closeCart);
    if(cartOverlay) cartOverlay.addEventListener("click", closeCart);
  }

  if (cartBody) {
    cartBody.addEventListener("click", e => {
      const cartItemElement = e.target.closest(".cart-item");
      if (!cartItemElement) return;
      const id = cartItemElement.dataset.id;
      
      if (e.target.closest(".remove-item-btn")) {
        cart = cart.filter(item => item.id != id);
      }
      if (e.target.closest(".quantity-btn")) {
        const itemInCart = cart.find(item => item.id == id);
        if (!itemInCart) return;
        const action = e.target.closest(".quantity-btn").dataset.action;
        if (action === "increase") itemInCart.quantity++;
        else if (action === "decrease") {
          itemInCart.quantity > 1 ? itemInCart.quantity-- : (cart = cart.filter(item => item.id != id));
        }
      }
      updateCartUI();
    });
  }
  
  const generarUrlWhatsAppYRedirigir = () => {
    const phoneNumber = '56976509490';
    let message = '¡Hola! Me gustaría hacer el siguiente pedido:\n\n';

    cart.forEach(item => {
        message += `*${item.quantity}x* - ${item.name}\n`;
    });

    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const selectedShipping = document.querySelector('input[name="shipping"]:checked');
    let finalTotal = subtotal;

    message += `\n*Subtotal: $${subtotal.toLocaleString('es-CL')}*`;

    if (selectedShipping && selectedShipping.id === 'shipping-delivery') {
        finalTotal += SHIPPING_COST;
        message += `\n*Costo de Envío: $${SHIPPING_COST.toLocaleString('es-CL')}*`;
    }

    message += `\n*Total a Pagar: $${finalTotal.toLocaleString('es-CL')}*`;
    message += `\n\n*Método de Entrega: ${selectedShipping.value}*`;
    
    if (window.IS_LOGGED_IN && window.USER_ADDRESS && selectedShipping && selectedShipping.id === 'shipping-delivery') {
      message += `\n\n*Dirección de Envío Registrada:*\n${window.USER_ADDRESS}`;
    }

    message += `\n\nPor favor, confírmame la disponibilidad. ¡Gracias!`;

    const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappURL, '_blank');
  };

  if (shippingOptionsContainer) {
    shippingOptionsContainer.addEventListener('change', updateCartTotals);
  }

  if (finalizePurchaseBtn) {
    finalizePurchaseBtn.addEventListener('click', async () => {
        if (cart.length === 0 || !document.querySelector('input[name="shipping"]:checked')) return;
        if (!window.IS_LOGGED_IN) {
            alert("Por favor, inicia sesión para guardar tu pedido antes de continuar.");
            window.location.href = 'login.php';
            return;
        }

        finalizePurchaseBtn.disabled = true;
        finalizePurchaseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        try {
            const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const response = await fetch('registrar-pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart, total }),
            });
            const result = await response.json();

            if (result.success) {
                generarUrlWhatsAppYRedirigir();
                cart = [];
                document.querySelectorAll('input[name="shipping"]').forEach(r => r.checked = false);
                updateCartUI();
                closeCart();
            } else {
                alert(`Hubo un error al registrar tu pedido: ${result.message || 'Error desconocido.'}`);
            }
        } catch (error) {
            console.error('Error de conexión:', error);
            alert('Hubo un error de conexión. No se pudo registrar el pedido.');
        } finally {
            if(finalizePurchaseBtn) {
                finalizePurchaseBtn.disabled = true;
                finalizePurchaseBtn.innerHTML = '<i class="fab fa-whatsapp"></i> Pedir por WhatsApp';
            }
        }
    });
  }

  // === INICIALIZACIÓN ===
  updateCartUI();
});