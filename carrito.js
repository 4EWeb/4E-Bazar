function agregarAlCarrito(producto) {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const idx = carrito.findIndex(item => item.id === producto.id);
    if (idx > -1) {
        carrito[idx].cantidad += 1;
    } else {
        carrito.push({ ...producto, cantidad: 1 });
    }
    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarContadorCarrito();
    mostrarNotificacion("¡Agregado al carrito!");
}

function actualizarContadorCarrito() {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    let total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    let contador = document.getElementById('contador-carrito');
    if (contador) {
        contador.textContent = total;
        contador.style.display = total > 0 ? 'inline-block' : 'none';
    }
}

// Llama a esto al cargar la página para mostrar el número correcto
document.addEventListener('DOMContentLoaded', actualizarContadorCarrito);

// Notificación simple
function mostrarNotificacion(mensaje) {
    let notif = document.createElement('div');
    notif.textContent = mensaje;
    notif.style.position = 'fixed';
    notif.style.bottom = '30px';
    notif.style.right = '30px';
    notif.style.background = '#e75480';
    notif.style.color = '#fff';
    notif.style.padding = '14px 28px';
    notif.style.borderRadius = '8px';
    notif.style.fontSize = '1.1rem';
    notif.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
    notif.style.zIndex = 9999;
    notif.style.opacity = 0.95;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.style.transition = 'opacity 0.5s';
        notif.style.opacity = 0;
        setTimeout(() => notif.remove(), 500);
    }, 1200);
}

function mostrarCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const lista = document.getElementById('carrito-lista');
    const totalDiv = document.getElementById('carrito-total');
    lista.innerHTML = '';
    let total = 0;

    if (carrito.length === 0) {
        lista.innerHTML = '<p style="text-align:center; color:#e75480; font-size:1.2rem;">El carrito está vacío.</p>';
        totalDiv.textContent = '';
        return;
    }

    const table = document.createElement('table');
    table.innerHTML = `
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
        </tr>
    `;

    carrito.forEach((item, idx) => {
        const subtotal = item.precio * item.cantidad;
        total += subtotal;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.nombre}</td>
            <td>$${item.precio.toLocaleString()}</td>
            <td>
                <button onclick="cambiarCantidad(${idx}, -1)">-</button>
                <span style="margin:0 8px;display:inline-block;min-width:24px;">${item.cantidad}</span>
                <button onclick="cambiarCantidad(${idx}, 1)">+</button>
            </td>
            <td>$${subtotal.toLocaleString()}</td>
            <td>
                <button onclick="eliminarDelCarrito(${idx})" style="background:#ff4d4d;">Eliminar</button>
            </td>
        `;
        table.appendChild(row);
    });

    lista.appendChild(table);
    totalDiv.textContent = `Total: $${total.toLocaleString()}`;
}

function cambiarCantidad(idx, cambio) {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    carrito[idx].cantidad += cambio;
    if (carrito[idx].cantidad < 1) carrito[idx].cantidad = 1;
    localStorage.setItem('carrito', JSON.stringify(carrito));
    mostrarCarrito();
    if (typeof actualizarContadorCarrito === "function") actualizarContadorCarrito();
}

function eliminarDelCarrito(idx) {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    carrito.splice(idx, 1);
    localStorage.setItem('carrito', JSON.stringify(carrito));
    mostrarCarrito();
    if (typeof actualizarContadorCarrito === "function") actualizarContadorCarrito();
}

// Mostrar el carrito al cargar la página
document.addEventListener('DOMContentLoaded', mostrarCarrito);