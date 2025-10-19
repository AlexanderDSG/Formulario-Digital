/**
 * MÓDULO DE GESTIÓN DE USUARIOS
 * Maneja la carga y visualización de usuarios por tipo
 */

// ===== FUNCIONES PRINCIPALES =====
// NOTA: La función showDashboard ahora está en panelCoordinator.js

function showUsers(tipo) {
    
    const dashboardContent = document.getElementById('dashboard-content');
    const userTableContent = document.getElementById('user-table-content');
    
    // Ocultar dashboard y mostrar tabla de usuarios
    if (dashboardContent) {
        dashboardContent.classList.add('hidden');
    }
    
    if (userTableContent) {
        userTableContent.classList.remove('hidden');
    }

    mostrarCarga();

    fetch(BASE_URL + 'administrador/panel/filtrarUsuarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'tipo=' + encodeURIComponent(tipo)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Respuesta no JSON recibida en showUsers:', text.substring(0, 200) + '...');
                throw new Error('El servidor no devolvió JSON válido. Revisa la consola para más detalles.');
            });
        }
    })
    .then(data => {
        if (data.success) {
            actualizarTablaUsuarios(data);
        } else {
            mostrarError(data.message || 'Error al cargar usuarios');
        }
    })
    .catch(error => {
        console.error('Error completo en showUsers:', error);
        mostrarError('Error de conexión: ' + error.message);
    });
}

// Exportar showUsers globalmente
window.showUsers = showUsers;

function actualizarTablaUsuarios(data) {
    document.getElementById('table-title').textContent = data.nombreTipo;
    document.getElementById('card-title').textContent = `Lista de ${data.nombreTipo}`;

    let html = '';

    if (data.usuarios.length === 0) {
        html = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                        <p>No hay usuarios de tipo ${data.nombreTipo}</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        data.usuarios.forEach(usuario => {
            const esUsuarioActual = usuario.usu_id == CURRENT_USER_ID;

            html += `
                <tr class="border-t hover:bg-gray-100">
                    <td class="px-4 py-2">
                        ${escapeHtml(usuario.usu_nombre)} ${escapeHtml(usuario.usu_apellido)}
                    </td>
                    <td class="px-4 py-2">
                        ${escapeHtml(usuario.usu_nro_documento || 'No registrado')}
                    </td>
                    <td class="px-4 py-2">
                        ${escapeHtml(usuario.usu_usuario)}
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            ${escapeHtml(usuario.rol_nombre || 'Sin rol')}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="${BASE_URL}administrador/usuarios/editar/${usuario.usu_id}"
                                class="text-blue-600 hover:text-blue-800 px-2 py-1 text-sm border border-blue-600 rounded hover:bg-blue-50">
                                ✏️ Editar
                            </a>
                            ${esUsuarioActual ?
                    '<span class="text-gray-400 px-2 py-1 text-sm">(Tu cuenta)</span>' :
                    `<button onclick="confirmarDesactivacion(${usuario.usu_id}, '${escapeHtml(usuario.usu_nombre + ' ' + usuario.usu_apellido)}')"
                                        class="text-red-600 hover:text-red-800 px-2 py-1 text-sm border border-red-600 rounded hover:bg-red-50">
                                        🗑️ Desactivar
                                    </button>`
                }
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    const tbody = document.querySelector('#user-table-content table tbody');
    if (tbody) {
        tbody.innerHTML = html;
    }
}

// ===== FUNCIONES DE DESACTIVACIÓN =====
function confirmarDesactivacion(id, nombre) {
    Swal.fire({
        title: '¿Desactivar Usuario?',
        html: `
            <p class="text-gray-700 mb-2">¿Estás seguro de que deseas desactivar al usuario:</p>
            <p class="text-lg font-semibold text-gray-900 mb-3">"${nombre}"</p>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-sm text-orange-800">
                <i class="fas fa-info-circle mr-1"></i>
                Este usuario será movido a la sección de usuarios desactivados y podrá ser reactivado en cualquier momento.
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-user-slash mr-2"></i>Sí, Desactivar',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg',
            confirmButton: 'px-4 py-2 rounded-lg',
            cancelButton: 'px-4 py-2 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            desactivarUsuario(id, nombre);
        }
    });
}

function desactivarUsuario(id, nombre) {
    // Mostrar indicador de carga
    const tbody = document.querySelector('#user-table-content table tbody');
    const filaUsuario = tbody ? tbody.querySelector(`button[onclick*="${id}"]`)?.closest('tr') : null;
    if (filaUsuario) {
        filaUsuario.style.opacity = '0.5';
    }

    fetch(BASE_URL + 'administrador/usuarios/desactivar/' + id, {
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
                console.error('Respuesta no JSON recibida en desactivarUsuario:', text.substring(0, 200) + '...');
                throw new Error('El servidor no devolvió JSON válido');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarMensajeExito(`Usuario "${nombre}" desactivado exitosamente`);
            
            // Remover la fila de la tabla
            if (filaUsuario) {
                filaUsuario.remove();
            }

            // Actualizar contador en el título de la tabla
            actualizarContadorTabla(-1);
            
        } else {
            // Restaurar opacidad y mostrar error
            if (filaUsuario) {
                filaUsuario.style.opacity = '1';
            }
            mostrarMensajeError(data.message || 'Error al desactivar usuario');
        }
    })
    .catch(error => {
        console.error('Error completo en desactivarUsuario:', error);
        if (filaUsuario) {
            filaUsuario.style.opacity = '1';
        }
        mostrarMensajeError('Error de conexión al desactivar usuario: ' + error.message);
    });
}

// ===== FUNCIONES AUXILIARES =====
function mostrarCarga() {
    const tbody = document.querySelector('#user-table-content table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        <span>Cargando usuarios...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

function mostrarError(mensaje) {
    const tbody = document.querySelector('#user-table-content table tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-600">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>${escapeHtml(mensaje)}</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function mostrarMensajeExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-lg'
        }
    });
}

function mostrarMensajeError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-lg'
        }
    });
}

function actualizarContadorTabla(cambio) {
    const cardTitle = document.getElementById('card-title');
    if (cardTitle) {
        const texto = cardTitle.textContent;
        const match = texto.match(/\((\d+)\)$/);
        if (match) {
            const nuevoContador = parseInt(match[1]) + cambio;
            const nuevoTexto = texto.replace(/\(\d+\)$/, `(${nuevoContador})`);
            cardTitle.textContent = nuevoTexto;
        }
    }
}