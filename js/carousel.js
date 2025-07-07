// =======================================================
// CÓDIGO FINAL Y CORREGIDO PARA EL CARRUSEL
// =======================================================
document.addEventListener('DOMContentLoaded', () => {
    const carouselContainer = document.querySelector('.carousel-container');
    if (!carouselContainer) return;

    // LA CORRECCIÓN CLAVE: Seleccionamos el viewport, que es el elemento que se desplaza.
    const viewport = carouselContainer.querySelector('.carousel-viewport'); 
    const track = carouselContainer.querySelector('.carousel-track');
    const prevBtn = carouselContainer.querySelector('.carousel-btn.prev');
    const nextBtn = carouselContainer.querySelector('.carousel-btn.next');
    const items = track.querySelectorAll('.producto-box');

    if (!viewport || !prevBtn || !nextBtn || items.length === 0) {
        return;
    }

    const updateButtons = () => {
        const scrollLeft = Math.round(viewport.scrollLeft);
        const maxScroll = viewport.scrollWidth - viewport.clientWidth;
        
        prevBtn.style.visibility = (scrollLeft <= 0) ? 'hidden' : 'visible';
        nextBtn.style.visibility = (scrollLeft >= maxScroll - 1) ? 'hidden' : 'visible';
    };

    const itemWidth = items[0].offsetWidth;
    const gap = parseInt(window.getComputedStyle(track).gap) || 24;
    const scrollAmount = itemWidth + gap;

    nextBtn.addEventListener('click', () => {
        // Le damos la orden de moverse al viewport, no al track.
        viewport.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
        // Le damos la orden de moverse al viewport, no al track.
        viewport.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    
    // El resto de la lógica para actualizar los botones se mantiene.
    viewport.addEventListener('scroll', () => {
        // Usamos un timeout para que la función no se ejecute demasiadas veces
        setTimeout(updateButtons, 200);
    });

    // Llamada inicial para ocultar el botón "anterior"
    updateButtons();
});