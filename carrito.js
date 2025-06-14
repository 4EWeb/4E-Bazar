// Variable global para que la función onclick del HTML la encuentre
let agregarAlCarrito;

document.addEventListener('DOMContentLoaded', () => {
  // === SELECCIÓN DE ELEMENTOS DEL DOM ===
  const cartIconBtn = document.getElementById('cart-icon-btn');
  const cartSidebar = document.querySelector('.cart-sidebar');
  const cartCloseBtn = document.querySelector('.cart-close-btn');
  const cartOverlay = document.querySelector('.cart-overlay');
  const cartCounterSpan = document.getElementById('contador-carrito');
  const cartBody = document.querySelector('.cart-body');
  const cartTotalPriceEl = document.getElementById('cart-total-price');
  const emptyCartMsg = document.querySelector('.cart-empty-msg');
  const finalizePurchaseBtn = document.getElementById('btn-finalize-purchase');

  // === ESTADO DEL CARRITO ===
  let cart = JSON.parse(localStorage.getItem('cart')) || [];

  // === FUNCIONES ===

  const saveCart = () => {
    localStorage.setItem('cart', JSON.stringify(cart));
  };

  const openCart = () => {
    cartSidebar.classList.add('active');
    cartOverlay.classList.add('active');
  };

  const closeCart = () => {
    cartSidebar.classList.remove('active');
    cartOverlay.classList.remove('active');
  };

  const updateCartInfo = () => {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    cartCounterSpan.textContent = totalItems;
    cartTotalPriceEl.textContent = `$${totalPrice.toLocaleString('es-CL')}`;
  };

  const renderCartItems = () => {
    cartBody.innerHTML = '';
    if (cart.length === 0) {
      cartBody.appendChild(emptyCartMsg);
      emptyCartMsg.style.display = 'block';
    } else {
      emptyCartMsg.style.display = 'none';
      cart.forEach(item => {
        const itemTotalPrice = (item.price * item.quantity).toLocaleString('es-CL');
        const cartItemDiv = document.createElement('div');
        cartItemDiv.className = 'cart-item';
        cartItemDiv.innerHTML = `
          <img src="${item.imagen}" alt="${item.nombre}" class="cart-item-img">
          <div class="cart-item-info">
            <p class="cart-item-title">${item.nombre}</p>
            <p class="cart-item-price">$${itemTotalPrice}</p>
            <div class="cart-item-actions">
              <div class="quantity-controls">
                <button class="quantity-btn" data-id="${item.id}" data-action="decrease">-</button>
                <span class="item-quantity">${item.quantity}</span>
                <button class="quantity-btn" data-id="${item.id}" data-action="increase">+</button>
              </div>
              <button class="remove-item-btn" data-id="${item.id}" title="Eliminar producto">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                </svg>
              </button>
            </div>
          </div>
        `;
        cartBody.appendChild(cartItemDiv);
      });
    }
    updateCartInfo();
  };

  agregarAlCarrito = (product) => {
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
      existingItem.quantity++;
    } else {
      cart.push({ ...product, quantity: 1 });
    }
    saveCart();
    renderCartItems();
  };

  // === EVENT LISTENERS ===

  cartIconBtn.addEventListener('click', openCart);
  cartCloseBtn.addEventListener('click', closeCart);
  cartOverlay.addEventListener('click', closeCart);

  cartBody.addEventListener('click', (e) => {
    const target = e.target.closest('button');
    if (!target) return;
    const id = parseInt(target.dataset.id);
    const itemToUpdate = cart.find(item => item.id === id);
    if (!itemToUpdate) return;

    if (target.classList.contains('quantity-btn')) {
      const action = target.dataset.action;
      if (action === 'increase') {
        itemToUpdate.quantity++;
      } else if (action === 'decrease') {
        if (itemToUpdate.quantity > 1) {
          itemToUpdate.quantity--;
        } else {
          cart = cart.filter(item => item.id !== id);
        }
      }
    }

    if (target.classList.contains('remove-item-btn')) {
      cart = cart.filter(item => item.id !== id);
    }
    saveCart();
    renderCartItems();
  });

  // MODIFICADO: Event listener para el botón de finalizar compra por WhatsApp
  finalizePurchaseBtn.addEventListener('click', () => {
    if (cart.length === 0) {
      alert('Tu carrito está vacío. Agrega productos antes de continuar.');
      return;
    }

    const phoneNumber = '56976509490'; // Número de WhatsApp del emprendimiento
    let message = '¡Hola! Me gustaría cotizar los siguientes productos de su catálogo:\n\n';

    cart.forEach(item => {
      // Formato: *Cantidadx* - NombreProducto
      message += `*${item.quantity}x* - ${item.nombre}\n`;
    });

    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    message += `\n*Total estimado: $${totalPrice.toLocaleString('es-CL')}*`;

    // Codificar mensaje para la URL y abrir en una nueva pestaña
    const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(whatsappURL, '_blank');
  });


  // === INICIALIZACIÓN ===
  renderCartItems();
});