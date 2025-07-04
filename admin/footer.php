</main>
<footer class="text-center mt-4" style="padding: 1.5rem 0; background-color: #e9ecef;">
    <p>&copy; <?php echo date('Y'); ?> Panel de Administración</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Manejador para el menú de navegación móvil
    const navToggler = document.getElementById('nav-toggler-btn');
    const navCollapse = document.getElementById('navbarAdmin');
    if (navToggler) {
        navToggler.addEventListener('click', () => {
            navCollapse.classList.toggle('show');
        });
    }

    // 2. Manejador para el acordeón de productos
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('click', () => {
            const collapse = document.querySelector(button.getAttribute('data-target'));
            if (!collapse) return;

            const isCollapsed = button.classList.contains('collapsed');

            button.classList.toggle('collapsed', !isCollapsed);
            collapse.classList.toggle('show', isCollapsed);
        });
    });

    // 3. Manejador para los modales de pedidos
    // Abrir modal
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(button.getAttribute('data-modal-target'));
            if (modal) {
                modal.classList.add('show');
            }
        });
    });

    // Cerrar modal
    document.querySelectorAll('.modal .btn-close, .modal [data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // --- CÓDIGO AÑADIDO ---
    // 4. Abrir el acordeón que fue marcado como activo desde PHP
    const activeAccordion = document.querySelector('.accordion-collapse[data-show="true"]');
    if (activeAccordion) {
        const button = document.querySelector(`[data-target="#${activeAccordion.id}"]`);
        activeAccordion.classList.add('show');
        if (button) {
            button.classList.remove('collapsed');
        }
    }
});
</script>

</body>
</html>