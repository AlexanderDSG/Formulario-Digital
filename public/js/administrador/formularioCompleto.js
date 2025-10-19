
// Inicialización del sistema PDF
function inicializarSistemaPDF() {
    const btnPDF = document.getElementById('btn-generar-pdf-008');
    
    if (btnPDF && typeof window.generatePDF === 'function') {
        btnPDF.disabled = false;
        btnPDF.classList.remove('opacity-50', 'cursor-not-allowed');
        
        // Limpiar eventos previos
        const nuevoBtnPDF = btnPDF.cloneNode(true);
        btnPDF.parentNode.replaceChild(nuevoBtnPDF, btnPDF);
        
        // Agregar evento
        nuevoBtnPDF.addEventListener('click', async (event) => {
            event.preventDefault();
            try {
                await window.generatePDF();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al generar el PDF',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    } else if (typeof window.reinicializarGeneradorPDF === 'function') {
        window.reinicializarGeneradorPDF();
    }
}

// Verificar que las funciones estén disponibles (mismo patrón que usuarios.php)
setTimeout(() => {
    const funcionesDisponibles = {
        cargarSeccionFormularios: typeof window.cargarSeccionFormularios,
        generatePDF: typeof window.generatePDF,
        refrescarFormulario: typeof window.refrescarFormulario
    };
    
    // Si cargarSeccionFormularios no está disponible, crear fallback
    if (funcionesDisponibles.cargarSeccionFormularios !== 'function') {
        window.cargarSeccionFormularios = function() {
            location.reload();
        };
    }
}, 500);

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    
    // Inicializar PDF cuando esté disponible
    if (window.pdfSystemLoaded && typeof window.generatePDF === 'function') {
        setTimeout(inicializarSistemaPDF, 100);
    } else {
        setTimeout(inicializarSistemaPDF, 1500);
    }
});

// También ejecutar si el DOM ya está listo
if (document.readyState === 'complete' || document.readyState === 'interactive') { 
    setTimeout(() => {
        if (window.pdfSystemLoaded && typeof window.generatePDF === 'function') {
            inicializarSistemaPDF();
        }
        formularioCompletoInstance.init();
    }, 100);
} else {
    setTimeout(() => {
        window.inicializarFormularioCompleto();
    }, 1000);
}


window.recargarFormularioCompleto = function() {
    if (formularioCompletoInstance) {
        formularioCompletoInstance.recargarFormulario();
    }
};

window.reinicializarFormularioCompleto = function() {
    if (formularioCompletoInstance) {
        formularioCompletoInstance.reinicializar();
    } else {
        window.inicializarFormularioCompleto();
    }
};

// Inicialización automática cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para que otros scripts se carguen
    setTimeout(() => {
        window.inicializarFormularioCompleto();
    }, 500);
});

// Inicializar si el DOM ya está listo
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => {
        window.inicializarFormularioCompleto();
    }, 100);
}