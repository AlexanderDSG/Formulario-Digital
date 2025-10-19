
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    dropdown.classList.toggle('hidden');
}

function reactivarUsuario(id, nombre) {
    Swal.fire({
        title: '¿Reactivar Usuario?',
        html: `
            <p class="text-gray-700 mb-2">¿Estás seguro de que deseas reactivar al usuario:</p>
            <p class="text-lg font-semibold text-gray-900 mb-3">"${nombre}"</p>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                <i class="fas fa-info-circle mr-1"></i>
                El usuario volverá a tener acceso completo al sistema.
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-undo mr-2"></i>Sí, Reactivar',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg',
            confirmButton: 'px-4 py-2 rounded-lg',
            cancelButton: 'px-4 py-2 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const fila = document.getElementById('usuario-' + id);
            if (fila) {
                fila.style.opacity = '0.5';
            }

            fetch(BASE_URL + 'administrador/usuarios/reactivar/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('Respuesta no JSON recibida:', text);
                        throw new Error('El servidor no devolvió JSON válido');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    if (fila) {
                        fila.remove();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Usuario Reactivado!',
                        text: `El usuario "${nombre}" ha sido reactivado exitosamente`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });

                    actualizarContador(-1);

                } else {
                    if (fila) {
                        fila.style.opacity = '1';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al reactivar usuario',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                if (fila) {
                    fila.style.opacity = '1';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error al reactivar usuario: ' + error.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            });
        }
    });
}

// Función eliminada - Ya no se permite eliminar definitivamente usuarios

function actualizarContador(cambio) {
    const titulo = document.querySelector('h3');
    if (titulo) {
        const texto = titulo.textContent;
        const match = texto.match(/\((\d+)\)$/);
        if (match) {
            const nuevoContador = parseInt(match[1]) + cambio;
            const nuevoTexto = texto.replace(/\(\d+\)$/, `(${nuevoContador})`);
            titulo.textContent = nuevoTexto;

            // Si llega a 0, recargar la página para mostrar el mensaje de "no hay usuarios"
            if (nuevoContador === 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        }
    }
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function (event) {
    const userDropdown = document.getElementById('user-dropdown');
    const userButton = event.target.closest('[onclick="toggleUserDropdown()"]');

    if (!userButton && userDropdown && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }
});
