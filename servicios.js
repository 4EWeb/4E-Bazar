// Precios base
const PRECIO_BASE = 500;
const PRECIO_ANILLADO = 1000;
const PRECIO_DOBLE_CARA = 500;
const PRECIO_PLASTIFICADO = 700;

function calcularTotal() {
  const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
  const anillado = document.getElementById("anillado").checked;
  const dobleCara = document.getElementById("doble_cara").checked;
  const plastificado = document.getElementById("plastificado").checked;

  let total = PRECIO_BASE * cantidad;
  if (anillado) total += PRECIO_ANILLADO;
  if (dobleCara) total += PRECIO_DOBLE_CARA;
  if (plastificado) total += PRECIO_PLASTIFICADO;

  document.getElementById("precio-total").textContent = total;
  return total;
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
      const total = calcularTotal();

      // Aquí puedes agregar la lógica para enviar al carrito
      alert(
        `Servicio agregado:\n` +
          `Cantidad: ${cantidad}\n` +
          `Anillado: ${anillado ? "Sí" : "No"}\n` +
          `Doble cara: ${dobleCara ? "Sí" : "No"}\n` +
          `Plastificado: ${plastificado ? "Sí" : "No"}\n` +
          `Total: $${total}`
      );
    });

  // Inicializa el precio al cargar
  calcularTotal();
});
