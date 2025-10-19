function mostrarAlerta(tipo, mensaje) {
    const container = document.getElementById('contenedor-alertas');
    if (!container) return;

    const tipos = {
        error: {
            bg: 'bg-red-100',
            border: 'border-red-400',
            text: 'text-red-700',
            icon: '⚠ Error:'
        },
        exito: {
            bg: 'bg-green-100',
            border: 'border-green-400',
            text: 'text-green-700',
            icon: '✔ ¡Éxito!'
        },
        info: {
            bg: 'bg-blue-100',
            border: 'border-blue-400',
            text: 'text-blue-700',
            icon: 'ℹ Info:'
        }
    };

    const t = tipos[tipo] || tipos.info;
    const texto = mensaje ?? 'Mensaje no disponible.';

    const alerta = document.createElement('div');
    alerta.className = `${t.bg} border ${t.border} ${t.text} px-4 py-3 rounded relative my-2`;
    alerta.innerHTML = `
        <strong class="font-bold">${t.icon}</strong>
        <span class="block sm:inline ml-2">${texto}</span>
    `;

    container.innerHTML = '';
    container.appendChild(alerta);

    setTimeout(() => {
        alerta.classList.add('opacity-0');
        setTimeout(() => alerta.remove(), 500);
    }, 5000);
}
