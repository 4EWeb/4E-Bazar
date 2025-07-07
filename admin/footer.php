</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            // Evita que el click en un formulario dentro del botón active el acordeón de forma extraña
            if (event.target.closest('form')) {
                return;
            }

            // --- ESTA ES LA LÓGICA CORREGIDA Y MÁS ROBUSTA ---
            // 1. Encuentra el contenedor padre '.accordion-item'
            const accordionItem = this.closest('.accordion-item');
            
            if (accordionItem) {
                // 2. Busca el contenido '.accordion-content' DENTRO de ese contenedor
                const content = accordionItem.querySelector('.accordion-content');

                if (content) {
                    // 3. Activa la clase en el botón para la flecha
                    this.classList.toggle('active');

                    // 4. Expande o contrae el contenido cambiando la altura
                    if (content.style.maxHeight) {
                        content.style.maxHeight = null; // Contraer
                    } else {
                        content.style.maxHeight = content.scrollHeight + "px"; // Expandir
                    }
                }
            }
        });
    });
});
</script>
</body>
</html>