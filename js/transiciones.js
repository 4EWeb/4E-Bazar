// Aplica fade-in al cargar la página
document.body.classList.add('fade-in');

// Busca todos los enlaces de la página
document.querySelectorAll('a').forEach(link => {
    // Solo aplica a enlaces internos y que no abran en nueva pestaña
    if (link.hostname === window.location.hostname) {
        link.addEventListener('click', function(e) {
            // Ignora enlaces con target _blank o anclas
            if (link.getAttribute('target') === '_blank' || link.href.includes('#')) return;
            e.preventDefault();
            document.body.classList.remove('fade-in');
            document.body.classList.add('fade-out');
            setTimeout(() => {
                window.location = link.href;
            }, 300); // 300ms para coincidir con el CSS
        });
    }
});
