// Función para limpiar y limitar el input del teléfono
function restrictToNumbers(input) {
    // Remover cualquier carácter que no sea número
    let value = input.value.replace(/[^0-9]/g, '');
    
    // Si tiene más de 9 dígitos, mostrar alerta y limitar
    if (value.length > 9) {
        alert("Solo se permiten 9 números");
        value = value.slice(0, 9);
    }
    
    input.value = value;
}

// JavaScript para RUT chileno
function formatRUT(rut) {
    // Remover puntos y guiones
    let cleanRUT = rut.replace(/[^0-9kK]/g, '');
    
    // Limitar a 9 caracteres máximo (8 números + 1 dígito verificador)
    if (cleanRUT.length > 9) {
        cleanRUT = cleanRUT.slice(0, 9);
    }
    
    // Formatear con guión si tiene más de 1 carácter
    if (cleanRUT.length > 1) {
        const body = cleanRUT.slice(0, -1);
        const dv = cleanRUT.slice(-1);
        return body.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv;
    }
    
    return cleanRUT;
}

// Aplicar formato al RUT
document.getElementById('rut').addEventListener('input', function(e) {
    this.value = formatRUT(this.value);
});

// Configuración para el campo teléfono
const telefonoInput = document.getElementById('telefono');

// Evento input - se ejecuta cuando cambia el valor
telefonoInput.addEventListener('input', function(e) {
    restrictToNumbers(this);
});

// Evento keypress - previene la escritura de caracteres no válidos
telefonoInput.addEventListener('keypress', function(e) {
    // Obtener el carácter que se está intentando escribir
    const char = String.fromCharCode(e.which);
    
    // Si no es un número, prevenir la escritura
    if (!/[0-9]/.test(char)) {
        e.preventDefault();
        return;
    }
    
    // Si ya tiene 9 dígitos, mostrar alerta y prevenir más escritura
    if (this.value.length >= 9) {
        alert("Solo se permiten 9 números");
        e.preventDefault();
    }
});

// Evento paste - manejar pegado de texto
telefonoInput.addEventListener('paste', function(e) {
    // Usar setTimeout para procesar después de que se pegue el texto
    setTimeout(() => {
        restrictToNumbers(this);
    }, 1);
});

// Evento keydown - control más específico de teclas
telefonoInput.addEventListener('keydown', function(e) {
    // Teclas permitidas (backspace, delete, arrows, tab, escape, home, end)
    const allowedKeys = [8, 9, 27, 46, 35, 36, 37, 38, 39, 40];
    
    // Permitir teclas especiales o números
    if (allowedKeys.includes(e.keyCode) || 
        (e.keyCode >= 48 && e.keyCode <= 57) || // números del teclado principal (0-9)
        (e.keyCode >= 96 && e.keyCode <= 105)) { // números del teclado numérico (0-9)
        
        // Si ya tiene 9 dígitos y no es una tecla de borrado/navegación
        if (this.value.length >= 9 && 
            !allowedKeys.includes(e.keyCode) && 
            !e.ctrlKey && !e.metaKey) {
            alert("Solo se permiten 9 números");
            e.preventDefault();
        }
        return;
    }
    
    // Bloquear cualquier otra tecla
    e.preventDefault();
});