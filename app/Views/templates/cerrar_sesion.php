<!-- Cerrar la sesión con confirmación -->
<div class="flex justify-end w-full py-2">
    <form id="logoutForm" action="<?= base_url('logout') ?>" method="get">
        <button id="btn-logout" type="button" onclick="confirmLogout()" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-1.5 px-4 rounded-md shadow">
            Cerrar sesión
        </button>
    </form>
</div>

<script>
function confirmLogout() {
    const currentUser = '<?= session()->get('usu_nombre') ?>';
    const currentRole = '<?= session()->get('rol_nombre') ?>';

    Swal.fire({
        icon: 'question',
        title: '¿Cerrar sesión?',
        html: `
            <p>¿Estás seguro de que deseas cerrar sesión?</p>
            <div class="mt-3 p-3 bg-gray-100 rounded">
                <p class="text-sm"><strong>Usuario:</strong> ${currentUser}</p>
                <p class="text-sm"><strong>Rol:</strong> ${currentRole}</p>
            </div>
            <p class="text-xs text-gray-500 mt-3">Esto cerrará tu sesión solo en esta pestaña.</p>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const message = `Cerrando sesión de: ${currentUser} (${currentRole})`;
            console.log(message);
            document.getElementById('logoutForm').submit();
        }
    });
}

// Opcional: Detectar cuando la ventana se cierra para limpiar la sesión
window.addEventListener('beforeunload', function(event) {
    // Opcional: Enviar una señal al servidor para limpiar la sesión específica
    navigator.sendBeacon('<?= base_url('logout/cleanup') ?>', 
        JSON.stringify({
            user_namespace: '<?= session()->get('user_namespace') ?>'
        })
    );
});
</script>