// Variable global para que la función onclick del HTML la encuentre
let agregarAlCarrito;

// Funciones auxiliares para mostrar nombres de servicios
function obtenerNombreImpresion(tipo) {
  const nombres = {
    bn: "Blanco y negro",
    color: "Color",
    foto: "Fotografía",
    foto_papel: "Foto en papel fotográfico",
  };
  return nombres[tipo] || "Desconocido";
}

function obtenerNombreTermolaminado(tipo) {
  const nombres = {
    media_carta: "Media carta",
    oficio: "Oficio",
    ninguno: "Ninguno",
  };
  return nombres[tipo] || "Ninguno";
}

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
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // === FUNCIONES ===
  const saveCart = () => {
    localStorage.setItem("cart", JSON.stringify(cart));
  };

  const openCart = () => {
    cartSidebar.classList.add("active");
    cartOverlay.classList.add("active");
  };

  const closeCart = () => {
    cartSidebar.classList.remove("active");
    cartOverlay.classList.remove("active");
  };

  const updateCartUI = () => {
    updateCartInfo();
    renderCartItems();
    saveCart();
  };

  const updateCartInfo = () => {
    // Calcular items contando productos y servicios
    const totalItems = cart.reduce((sum, item) => {
      // Si es un servicio, cuenta como 1. Si es un producto, suma su cantidad.
      return sum + (item.tipo === "servicio" ? 1 : item.quantity);
    }, 0);

    // Calcular precio total
    const totalPrice = cart.reduce((sum, item) => {
      // Si es un servicio, suma su precio una vez. Si es un producto, multiplica por la cantidad.
      return sum + item.price * (item.tipo === "servicio" ? 1 : item.quantity);
    }, 0);

    if (cartCounterSpan) cartCounterSpan.textContent = totalItems;
    if (cartTotalPriceEl)
      cartTotalPriceEl.textContent = `$${totalPrice.toLocaleString("es-CL", {
        minimumFractionDigits: 0,
      })}`;
  };

  const renderCartItems = () => {
    if (!cartBody) return;

    cartBody.innerHTML = "";
    const hasItems = cart.length > 0;

    emptyCartMsg.style.display = hasItems ? "none" : "block";
    finalizePurchaseBtn.disabled = !hasItems; // Deshabilitar botón si no hay nada

    if (!hasItems) {
      cartBody.appendChild(emptyCartMsg);
      return;
    }

    cart.forEach((item) => {
      const cartItemDiv = document.createElement("div");
      // Asignar un data-id único a cada elemento del carrito para facilitar su manipulación
      cartItemDiv.dataset.id = item.id;
      cartItemDiv.className = "cart-item";

      if (item.tipo === "servicio") {
        // Renderizado para servicios
        const servicio = item.detalles;
        cartItemDiv.classList.add("servicio");
        cartItemDiv.innerHTML = `
          <div class="servicio-header">
            <img src="${
              item.image
            }" alt="Servicio de impresión" class="cart-item-img">
            <div>
              <p class="cart-item-title">${item.name}</p>
              <p class="cart-item-price">$${item.price.toLocaleString(
                "es-CL"
              )}</p>
            </div>
          </div>
          <div class="servicio-details">
            <p><strong>Cantidad:</strong> ${servicio.cantidad} hojas</p>
            <p><strong>Tipo:</strong> ${obtenerNombreImpresion(
              servicio.tipoImpresion
            )}</p>
            <p><strong>Termolaminado:</strong> ${obtenerNombreTermolaminado(
              servicio.termolaminado
            )}</p>
            <p><strong>Anillado:</strong> ${servicio.anillado ? "Sí" : "No"}</p>
            <p><strong>Doble cara:</strong> ${
              servicio.dobleCara ? "Sí" : "No"
            }</p>
            ${
              servicio.comentarios
                ? `<p><strong>Comentarios:</strong> ${servicio.comentarios}</p>`
                : ""
            }
          </div>
          <button class="remove-item-btn" title="Eliminar servicio">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            </svg>
          </button>
        `;
      } else {
        // Renderizado para productos
        const itemTotalPrice = (item.price * item.quantity).toLocaleString(
          "es-CL"
        );
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
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                </svg>
              </button>
            </div>
          </div>
        `;
      }
      cartBody.appendChild(cartItemDiv);
    });
  };

  // Función global para agregar cualquier tipo de item al carrito
  agregarAlCarrito = (item) => {
    if (item.tipo === "servicio") {
      // SOLUCIÓN: Asignar un ID único a cada servicio para poder eliminarlo individualmente
      const servicioConId = { ...item, id: `servicio-${Date.now()}` };
      cart.push(servicioConId);
    } else {
      // Lógica para productos
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

    // Mostrar carrito automáticamente
    if (cartSidebar) {
      openCart();
    }
  };

  // === EVENT LISTENERS ===
  if (cartIconBtn) {
    cartIconBtn.addEventListener("click", openCart);
    cartCloseBtn.addEventListener("click", closeCart);
    cartOverlay.addEventListener("click", closeCart);
  }

  // Event listener para manejar clics en el carrito
  if (cartBody) {
    cartBody.addEventListener("click", (e) => {
      // SOLUCIÓN: Usar .closest() para asegurar que el evento se capture aunque se haga clic en el SVG
      const removeButton = e.target.closest(".remove-item-btn");
      const quantityButton = e.target.closest(".quantity-btn");

      if (!removeButton && !quantityButton) return; // Si no se hizo clic en un botón de acción, no hacer nada

      const cartItemElement = e.target.closest(".cart-item");
      const id = cartItemElement.dataset.id;
      const itemInCart = cart.find((item) => item.id == id);

      if (!itemInCart) return;

      if (removeButton) {
        // Filtrar el carrito para eliminar el item seleccionado
        cart = cart.filter((item) => item.id != id);
      }

      if (quantityButton) {
        const action = quantityButton.dataset.action;
        if (action === "increase") {
          itemInCart.quantity++;
        } else if (action === "decrease") {
          if (itemInCart.quantity > 1) {
            itemInCart.quantity--;
          } else {
            // Si la cantidad es 1, eliminar el producto del carrito
            cart = cart.filter((item) => item.id != id);
          }
        }
      }

      // Centralizar la actualización de la UI
      updateCartUI();
    });
  }

  if (finalizePurchaseBtn) {
    finalizePurchaseBtn.addEventListener("click", () => {
      if (cart.length === 0) {
        alert("Tu carrito está vacío. Agrega productos antes de continuar.");
        return;
      }

      const phoneNumber = "56976509490";
      let message =
        "¡Hola! Me gustaría cotizar los siguientes productos y servicios:\n\n";

      // Construir mensaje para productos
      const productos = cart.filter((item) => item.tipo !== "servicio");
      if (productos.length > 0) {
        message += "*PRODUCTOS:*\n";
        productos.forEach((item) => {
          message += `- ${item.quantity}x ${
            item.name
          } ($${item.price.toLocaleString("es-CL")} c/u)\n`;
        });
        message += "\n";
      }

      // Construir mensaje para servicios
      const servicios = cart.filter((item) => item.tipo === "servicio");
      if (servicios.length > 0) {
        message += "*SERVICIOS DE IMPRESIÓN:*\n";
        servicios.forEach((servicio) => {
          const det = servicio.detalles;
          message += `- Impresión: ${
            det.cantidad
          } hojas (${obtenerNombreImpresion(det.tipoImpresion)})\n`;
          message += `  • Anillado: ${det.anillado ? "Sí" : "No"}\n`;
          message += `  • Doble cara: ${det.dobleCara ? "Sí" : "No"}\n`;
          message += `  • Termolaminado: ${obtenerNombreTermolaminado(
            det.termolaminado
          )}\n`;
          if (det.comentarios) message += `  • Notas: ${det.comentarios}\n`;
          message += `  • Total servicio: $${servicio.price.toLocaleString(
            "es-CL"
          )}\n\n`;
        });
      }

      // Calcular total general
      const totalPrice = cart.reduce((sum, item) => {
        return (
          sum + item.price * (item.tipo === "servicio" ? 1 : item.quantity)
        );
      }, 0);

      message += `\n*TOTAL ESTIMADO: $${totalPrice.toLocaleString("es-CL")}*`;
      message += `\n\nPor favor, confírmame la disponibilidad y el valor final. ¡Gracias!`;

      const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(
        message
      )}`;
      window.open(whatsappURL, "_blank");

      // Vaciar carrito después de enviar
      cart = [];
      updateCartUI();
      closeCart();
    });
  }

  // === INICIALIZACIÓN ===
  // Inicializar la UI al cargar la página
  updateCartUI();
});
