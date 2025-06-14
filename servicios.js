// Función para calcular el total usando precios desde PHP
function calcularTotal() {
  // Obtener valores del formulario
  const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
  const anillado = document.getElementById("anillado").checked;
  const dobleCara = document.getElementById("doble_cara").checked;
  const tipoImpresion = document.getElementById("tipo_impresion").value;
  const termolaminado = document.getElementById("termolaminado").value;
  const comentarios = document.getElementById("comentarios").value;

  // Precios cargados desde PHP
  const precios = window.PRECIOS_SERVICIO;

  // Precio base por hoja
  let precioImpresion = 0;

  // Determinar precio según tipo de impresión
  switch (tipoImpresion) {
    case "bn":
      precioImpresion = precios.bn;
      break;
    case "color":
      precioImpresion = precios.color;
      break;
    case "foto":
      precioImpresion = precios.foto;
      break;
    case "foto_papel":
      precioImpresion = precios.foto_papel;
      break;
  }

  // Aplicar descuento por doble cara
  if (dobleCara) {
    precioImpresion -= 50; // $50 de descuento por hoja
  }

  // Calcular total base
  let total = precios.base * cantidad + precioImpresion * cantidad;

  // Agregar termolaminado
  if (termolaminado === "media_carta") {
    total += precios.termolaminado_media_carta * cantidad;
  } else if (termolaminado === "oficio") {
    total += precios.termolaminado_oficio * cantidad;
  }

  // Agregar anillado según cantidad
  if (anillado) {
    if (cantidad <= 25) {
      total += precios.anillado_minimo;
    } else if (cantidad <= 50) {
      total += precios.anillado_medio;
    } else {
      total += precios.anillado_maximo;
    }
  }

  // Actualizar UI
  document.getElementById("precio-total").textContent = Math.round(total);
  return Math.round(total);
}

// Función para obtener el nombre del tipo de impresión
function obtenerNombreImpresion(tipo) {
  const nombres = {
    bn: "Blanco y negro",
    color: "Color",
    foto: "Fotografía",
    foto_papel: "Foto en papel fotográfico",
  };
  return nombres[tipo] || "Desconocido";
}

// Función para obtener el nombre del termolaminado
function obtenerNombreTermolaminado(tipo) {
  const nombres = {
    media_carta: "Media carta",
    oficio: "Oficio",
    ninguno: "Ninguno",
  };
  return nombres[tipo] || "Ninguno";
}

document.addEventListener("DOMContentLoaded", function () {
  // Inicializar el cálculo del total
  calcularTotal();

  // Escuchar cambios en el formulario
  document
    .getElementById("form-servicio")
    .addEventListener("input", calcularTotal);

  // Manejar clic en "Agregar al carrito"
  document
    .getElementById("agregar-carrito-servicio")
    .addEventListener("click", function () {
      // Obtener valores del formulario
      const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
      const anillado = document.getElementById("anillado").checked;
      const dobleCara = document.getElementById("doble_cara").checked;
      const tipoImpresion = document.getElementById("tipo_impresion").value;
      const termolaminado = document.getElementById("termolaminado").value;
      const comentarios = document.getElementById("comentarios").value;
      const total = calcularTotal();

      // Crear objeto de servicio
      const servicio = {
        id: `servicio-${Date.now()}`, // ID único
        name: "Servicio de Impresión",
        price: total,
        image: "Imagenes/icono-servicio.png", // Imagen genérica para servicios
        tipo: "servicio",
        detalles: {
          cantidad: cantidad,
          anillado: anillado,
          dobleCara: dobleCara,
          tipoImpresion: tipoImpresion,
          termolaminado: termolaminado,
          comentarios: comentarios,
        },
      };

      // Verificar si la función de carrito está disponible
      if (typeof agregarAlCarrito === "function") {
        agregarAlCarrito(servicio);

        // Feedback visual
        const btn = document.getElementById("agregar-carrito-servicio");
        btn.textContent = "✓ Agregado!";
        btn.style.backgroundColor = "#4CAF50";

        setTimeout(() => {
          btn.textContent = "Agregar al carrito";
          btn.style.backgroundColor = "";
        }, 2000);
      } else {
        console.error("La función agregarAlCarrito no está disponible");
        alert("Error: El carrito no está disponible. Recarga la página.");
      }
    });
});
