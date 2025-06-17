// Espera a que todo el HTML esté cargado para ejecutar el script.
document.addEventListener("DOMContentLoaded", function () {
  // Si no estamos en la página de servicios, no hacemos nada.
  const form = document.getElementById("form-servicio");
  if (!form) return;

  // --- OBTENER ELEMENTOS DEL FORMULARIO ---
  const precioTotalSpan = document.getElementById("precio-total");
  const cantidadInput = document.getElementById("cantidad");
  const anilladoCheckbox = document.getElementById("anillado");
  const dobleCaraCheckbox = document.getElementById("doble_cara");
  const tipoImpresionSelect = document.getElementById("tipo_impresion");
  const termolaminadoSelect = document.getElementById("termolaminado");
  const agregarBtn = document.getElementById("agregar-carrito-servicio");

  // Usamos el objeto de precios que pasamos desde PHP
  const precios = window.PRECIOS_SERVICIO;

  /**
   * Calcula el precio total basado en las selecciones del formulario.
   */
  function calcularTotal() {
    const cantidad = parseInt(cantidadInput.value) || 0;
    const anillado = anilladoCheckbox.checked;
    const dobleCara = dobleCaraCheckbox.checked;
    const tipoImpresion = tipoImpresionSelect.value;
    const termolaminado = termolaminadoSelect.value;

    let total = 0;

    // 1. Calcular costo de impresión por hoja.
    let precioPorHoja = precios[tipoImpresion] || 0;
    if (dobleCara && precioPorHoja >= 50) {
      precioPorHoja -= 50; // Descuento de $50 por hoja si es doble cara.
    }
    total += cantidad * precioPorHoja;

    // 2. Agregar costo del anillado (precio fijo según cantidad de hojas).
    if (anillado) {
      if (cantidad <= 25) {
        total += precios.anillado_minimo || 1000;
      } else if (cantidad <= 50) {
        total += precios.anillado_medio || 1500;
      } else {
        total += precios.anillado_maximo || 2000;
      }
    }

    // 3. Agregar costo del termolaminado (es un costo fijo, no por hoja).
    if (termolaminado !== "ninguno") {
      const precioTermolaminado = precios['termolaminado_' + termolaminado] || 0;
      total += precioTermolaminado;
    }

    // Asegurarse de que el total nunca sea negativo.
    total = Math.max(0, total);

    // Actualizar el precio en la página, formateado con puntos.
    precioTotalSpan.textContent = total.toLocaleString('es-CL');
    return total;
  }

  /**
   * Agrega el servicio configurado al carrito de compras.
   */
  function agregarServicioAlCarrito() {
    const total = calcularTotal(); // Llama a la función para obtener el total actualizado.

    if (total <= 0 || (parseInt(cantidadInput.value) || 0) <= 0) {
      alert("Por favor, selecciona una cantidad válida y opciones de impresión.");
      return;
    }

    // Crear un nombre descriptivo para el ítem del carrito.
    let nombreServicio = `${cantidadInput.value}x Impresión ${tipoImpresionSelect.options[tipoImpresionSelect.selectedIndex].text.split('(')[0].trim()}`;
    if (anilladoCheckbox.checked) nombreServicio += " + Anillado";
    if (termolaminadoSelect.value !== "ninguno") nombreServicio += " + Termolaminado";
    if (dobleCaraCheckbox.checked) nombreServicio += " (Doble cara)";
    
    // Crear el objeto de servicio para el carrito.
    const servicio = {
      id: `servicio-${Date.now()}`, // ID único para evitar colisiones.
      name: nombreServicio,
      price: total,
      image: "Imagenes/4e logo actualizado.png", // Imagen genérica.
      quantity: 1, // Los servicios personalizados se agregan como un solo paquete.
    };

    // Usar la función global de carrito.js si está disponible.
    if (typeof agregarAlCarrito === "function") {
      agregarAlCarrito(servicio);

      // Feedback visual para el usuario.
      agregarBtn.textContent = "✓ ¡Agregado!";
      agregarBtn.style.backgroundColor = "#28a745"; // Verde de éxito.

      setTimeout(() => {
        agregarBtn.textContent = "Agregar al carrito";
        agregarBtn.style.backgroundColor = ""; // Vuelve al color original.
      }, 2000); // El mensaje dura 2 segundos.
    } else {
      console.error("La función agregarAlCarrito no está disponible.");
      alert("Error: El sistema de carrito no está funcionando. Por favor, recarga la página.");
    }
  }

  // --- EVENT LISTENERS ---
  // Escuchar cambios en cualquier campo del formulario para recalcular el precio.
  form.addEventListener("input", calcularTotal);
  
  // Escuchar el clic en el botón para agregar al carrito.
  agregarBtn.addEventListener("click", agregarServicioAlCarrito);
  
  // Calcular el precio una vez al cargar la página.
  calcularTotal();
});