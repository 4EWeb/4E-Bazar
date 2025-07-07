document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('nav-toggle');
    const mainMenu = document.getElementById('main-menu');

    if (navToggle && mainMenu) {
        navToggle.addEventListener('click', () => {
            // Añade o quita la clase 'active' del menú
            mainMenu.classList.toggle('active');
        });
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const carouselContainer = document.querySelector('.carousel-container');
    if (!carouselContainer) return; // Si no hay carrusel en la página, no hace nada

    const track = carouselContainer.querySelector('.carousel-track');
    const prevBtn = carouselContainer.querySelector('.carousel-btn.prev');
    const nextBtn = carouselContainer.querySelector('.carousel-btn.next');

    // Mover al siguiente producto
    nextBtn.addEventListener('click', () => {
        const itemWidth = track.querySelector('.producto-box').offsetWidth;
        const gap = parseInt(window.getComputedStyle(track).gap);
        track.scrollBy({ left: itemWidth + gap, behavior: 'smooth' });
    });

    // Mover al producto anterior
    prevBtn.addEventListener('click', () => {
        const itemWidth = track.querySelector('.producto-box').offsetWidth;
        const gap = parseInt(window.getComputedStyle(track).gap);
        track.scrollBy({ left: -(itemWidth + gap), behavior: 'smooth' });
    });
});
