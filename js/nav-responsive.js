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