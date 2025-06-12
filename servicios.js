// Precios base
const PRECIO_BASE = 100;
const PRECIO_BN = 100;
const PRECIO_COLOR = 200;
const PRECIO_FOTO = 300;
const PRECIO_FOTO_PAPEL = 500;
const PRECIO_DOBLE_CARA = 0; // Ya no se suma, solo afecta el precio de impresión
const PRECIO_TERMOLAMINADO_MEDIA_CARTA = 1200;
const PRECIO_TERMOLAMINADO_OFICIO = 1500;

function calcularTotal() {
  const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
  const anillado = document.getElementById("anillado").checked;
  const dobleCara = document.getElementById("doble_cara").checked;
  const tipoImpresion = document.getElementById("tipo_impresion").value;
  const termolaminado = document.getElementById("termolaminado").value;

  // Si tienes plastificado, descomenta la siguiente línea y define PRECIO_PLASTIFICADO
  // const plastificado = document.getElementById("plastificado")?.checked;

  let precioImpresion = 0;
  if (tipoImpresion === "bn") precioImpresion = PRECIO_BN;
  else if (tipoImpresion === "color") precioImpresion = PRECIO_COLOR;
  else if (tipoImpresion === "foto") precioImpresion = PRECIO_FOTO;
  else if (tipoImpresion === "foto_papel") precioImpresion = PRECIO_FOTO_PAPEL;

  // Aplica descuento del 20% si es doble cara SOLO al precio de impresión
  if (dobleCara) {
    precioImpresion = precioImpresion - 50;
  }

  let total = PRECIO_BASE * cantidad + precioImpresion * cantidad;

  // Termolaminado
  if (termolaminado === "media_carta")
    total += PRECIO_TERMOLAMINADO_MEDIA_CARTA * cantidad;
  if (termolaminado === "oficio")
    total += PRECIO_TERMOLAMINADO_OFICIO * cantidad;

  // Plastificado (si existe el campo)
  // if (plastificado) total += PRECIO_PLASTIFICADO;

  // Anillado según cantidad (corregido)
  if (anillado) {
    if (cantidad >= 5 && cantidad <= 25) total += 1000;
    else if (cantidad >= 26 && cantidad <= 50) total += 1500;
    else if (cantidad >= 51 && cantidad <= 100) total += 2000;
    else if (cantidad < 5) total += 1000;
  }

  document.getElementById("precio-total").textContent = Math.round(total);
  return Math.round(total);
}

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("form-servicio")
    .addEventListener("input", calcularTotal);

  document
    .getElementById("agregar-carrito-servicio")
    .addEventListener("click", function () {
      const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
      const anillado = document.getElementById("anillado").checked;
      const dobleCara = document.getElementById("doble_cara").checked;
      const plastificado = document.getElementById("plastificado").checked;
      const tipoImpresion = document.getElementById("tipo_impresion").value;
      const termolaminado = document.getElementById("termolaminado").value;
      const comentarios = document.getElementById("comentarios").value;
      const total = calcularTotal();

      alert(
        `Servicio agregado:\n` +
          `Cantidad: ${cantidad}\n` +
          `Tipo de impresión: ${
            tipoImpresion === "bn" ? "Blanco y negro" : "Color"
          }\n` +
          `Foto: ${esFoto ? "Sí" : "No"}\n` +
          `Papel fotográfico: ${papelFoto ? "Sí" : "No"}\n` +
          `Termolaminado: ${
            termolaminado === "media_carta"
              ? "Media carta"
              : termolaminado === "oficio"
              ? "Oficio"
              : "Ninguno"
          }\n` +
          `Anillado: ${anillado ? "Sí" : "No"}\n` +
          `Doble cara: ${dobleCara ? "Sí" : "No"}\n` +
          `Plastificado: ${plastificado ? "Sí" : "No"}\n` +
          `Comentarios: ${comentarios}\n` +
          `Total: $${total}`
      );
    });

  // Inicializa el precio al cargar
  calcularTotal();
});
