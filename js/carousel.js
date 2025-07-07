// =======================================================
// CÓDIGO FINAL Y CORREGIDO PARA EL CARRUSEL
// =======================================================
document.addEventListener("DOMContentLoaded", () => {
  // Selecciona TODOS los contenedores de carrusel en la página
  const carouselContainers = document.querySelectorAll(".carousel-container");
  if (carouselContainers.length === 0) return;

  // Itera sobre cada carrusel encontrado para inicializarlo
  carouselContainers.forEach((carouselContainer) => {
    const viewport = carouselContainer.querySelector(".carousel-viewport");
    const track = carouselContainer.querySelector(".carousel-track");
    const prevBtn = carouselContainer.querySelector(".carousel-btn.prev");
    const nextBtn = carouselContainer.querySelector(".carousel-btn.next");
    const items = track ? track.querySelectorAll(".producto-box") : [];

    if (!viewport || !track || !prevBtn || !nextBtn || items.length === 0) {
      return; // Si a un carrusel le falta una parte, lo ignora y continúa con el siguiente
    }

    const updateButtons = () => {
      const scrollLeft = Math.round(viewport.scrollLeft);
      const maxScroll = viewport.scrollWidth - viewport.clientWidth;

      // Oculta o muestra los botones según la posición del scroll
      prevBtn.style.visibility = scrollLeft <= 0 ? "hidden" : "visible";
      nextBtn.style.visibility =
        scrollLeft >= maxScroll - 1 ? "hidden" : "visible";
    };

    const itemWidth = items[0].offsetWidth;
    const gap = parseInt(window.getComputedStyle(track).gap) || 24;
    const scrollAmount = itemWidth + gap;

    nextBtn.addEventListener("click", () => {
      viewport.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });

    prevBtn.addEventListener("click", () => {
      viewport.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });

    // Actualiza los botones al hacer scroll
    viewport.addEventListener("scroll", () => {
      setTimeout(updateButtons, 200);
    });

    // Llamada inicial para establecer el estado correcto de los botones
    updateButtons();
  });
});
