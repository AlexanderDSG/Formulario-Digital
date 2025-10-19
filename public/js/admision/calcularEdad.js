// Función mejorada para calcular edad en diferentes unidades
function calcularEdadPorUnidad(fechaNacimiento, unidad = 'A') {
    if (!fechaNacimiento) return 0;
    
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    
    // Verificar que la fecha de nacimiento sea válida y no sea futura
    if (isNaN(nacimiento.getTime()) || nacimiento > hoy) {
        return 0;
    }
    
    const diferenciaTiempo = hoy.getTime() - nacimiento.getTime();
    
    switch(unidad.toUpperCase()) {
        case 'A': // Años
            let años = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                años--;
            }
            return Math.max(0, años);
            
        case 'M': // Meses
            let totalMeses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12;
            totalMeses += (hoy.getMonth() - nacimiento.getMonth());
            
            if (hoy.getDate() < nacimiento.getDate()) {
                totalMeses--;
            }
            return Math.max(0, totalMeses);
            
        case 'D': // Días
            const dias = Math.floor(diferenciaTiempo / (1000 * 60 * 60 * 24));
            return Math.max(0, dias);
            
        case 'H': // Horas
            const horas = Math.floor(diferenciaTiempo / (1000 * 60 * 60));
            return Math.max(0, horas);
            
        default:
            return 0;
    }
}

// Función para actualizar la edad automáticamente
function actualizarEdad() {
    const fechaNacimiento = document.getElementById('pac_fecha_nacimiento').value;
    const unidadSeleccionada = document.querySelector('input[name="pac_edad_unidad"]:checked');
    const campoEdad = document.getElementById('pac_edad_valor');
    
    if (!fechaNacimiento || !unidadSeleccionada || !campoEdad) {
        return;
    }
    
    const unidad = unidadSeleccionada.value;
    const edad = calcularEdadPorUnidad(fechaNacimiento, unidad);
    campoEdad.value = edad;
}

// Event listeners para actualizar automáticamente la edad
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar cuando cambie la fecha de nacimiento
    const fechaNacimientoInput = document.getElementById('pac_fecha_nacimiento');
    if (fechaNacimientoInput) {
        fechaNacimientoInput.addEventListener('change', actualizarEdad);
        fechaNacimientoInput.addEventListener('input', actualizarEdad);
    }
    
    // Actualizar cuando cambie la unidad de edad
    const radiosEdad = document.querySelectorAll('input[name="pac_edad_unidad"]');
    radiosEdad.forEach(radio => {
        radio.addEventListener('change', actualizarEdad);
    });
    
    // Marcar "Años" por defecto si no hay ninguno seleccionado
    const radioAños = document.getElementById('pac_edad_unidad_a');
    const algunoMarcado = document.querySelector('input[name="pac_edad_unidad"]:checked');
    if (radioAños && !algunoMarcado) {
        radioAños.checked = true;
    }
});

// Función para establecer edad desde búsqueda
function establecerEdadDesdeBusqueda(fechaNacimiento) {
    const fechaNacInput = document.getElementById('pac_fecha_nacimiento');
    const radioAnios = document.getElementById('pac_edad_unidad_a');

    if (fechaNacInput && fechaNacimiento) {
        // Convertir fecha al formato correcto (YYYY-MM-DD)
        let fechaFormateada = '';
        
        if (fechaNacimiento.includes('/')) {
            // Si viene en formato DD/MM/YYYY o MM/DD/YYYY
            const partes = fechaNacimiento.split('/');
            if (partes.length === 3) {
                // Asumir DD/MM/YYYY
                fechaFormateada = `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
            }
        } else {
            // Si viene en formato datetime, extraer solo la fecha
            const fecha = new Date(fechaNacimiento);
            if (!isNaN(fecha.getTime())) {
                fechaFormateada = fecha.toISOString().split('T')[0];
            }
        }
        
        fechaNacInput.value = fechaFormateada;
    }
    
    // Marcar años por defecto
    if (radioAnios) {
        radioAnios.checked = true;
    }
    
    // Calcular y establecer la edad
    actualizarEdad();
}

// Validación adicional para asegurar datos correctos
function validarFechaNacimiento(fecha) {
    const fechaNac = new Date(fecha);
    const hoy = new Date();
    
    if (isNaN(fechaNac.getTime())) {
        console.warn('Fecha de nacimiento inválida:', fecha);
        return false;
    }
    
    if (fechaNac > hoy) {
        console.warn('Fecha de nacimiento no puede ser futura:', fecha);
        return false;
    }
    
    // Verificar que no sea demasiado antigua (más de 150 años)
    const fechaLimite = new Date();
    fechaLimite.setFullYear(fechaLimite.getFullYear() - 150);
    
    if (fechaNac < fechaLimite) {
        console.warn('Fecha de nacimiento muy antigua:', fecha);
        return false;
    }
    
    return true;
}

