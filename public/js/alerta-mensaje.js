// Oculta los mensajes luego de 5 segundos
setTimeout(() => {
    const alerta = document.getElementById('alerta-mensaje');
    if (alerta) {
        alerta.style.opacity = '0';
        setTimeout(() => alerta.remove(), 500);
    }

    const alertaError = document.getElementById('alerta-error');
    if (alertaError) {
        alertaError.style.opacity = '0';
        setTimeout(() => alertaError.remove(), 500);
    }
}, 5000);