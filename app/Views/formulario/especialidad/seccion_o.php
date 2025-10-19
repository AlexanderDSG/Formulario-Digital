<!-- SECCION O ESPECIALIDADES - VERSIÓN ACTUALIZADA -->
<div class="diagnostico-table-container bg-white shadow-xl rounded-lg overflow-hidden mt-6 seccion-o">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="7" class="header-main">O. CONDICIÓN AL EGRESO DE EMERGENCIA</th>
            </tr>
            <tr>
                <th class="subheader">Vivo</th>
                <th class="subheader">Estable</th>
                <th class="subheader">Inestable</th>
                <th class="subheader">Fallecido</th>
                <th class="subheader">Alta definitiva</th>
                <th class="subheader">Consulta externa</th>
                <th class="subheader">Observación de emergencia</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="checkbox" id="egreso_vivo" name="estados_egreso[]" value="1" class="form-checkbox">
                </td>
                <td><input type="checkbox" id="egreso_estable" name="estados_egreso[]" value="2" class="form-checkbox">
                </td>
                <td><input type="checkbox" id="egreso_inestable" name="estados_egreso[]" value="3"
                        class="form-checkbox">
                </td>
                <td><input type="checkbox" id="egreso_fallecido" name="estados_egreso[]" value="4"
                        class="form-checkbox">
                </td>
                <td><input type="checkbox" id="egreso_alta_definitiva" name="modalidades_egreso[]" value="1"
                        class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_consulta_externa" name="modalidades_egreso[]" value="2"
                        class="form-checkbox"></td>
                <td>
                    <!-- ✅ CHECKBOX CON MANEJO DINÁMICO -->
                    <input type="checkbox" id="egreso_observacion_emergencia" name="modalidades_egreso[]" value="3"
                           class="form-checkbox" onchange="manejarObservacionEmergencia(this)">
                </td>
            </tr>
            <tr>
                <th class="subheader">Hospitalización</th>
                <th class="subheader">Referencia</th>
                <th class="subheader">Referencia inversa</th>
                <th class="subheader">Derivación</th>
                <th class="subheader">Establecimiento</th>
                <th class="subheader" colspan="2"></th>
            </tr>
            <tr>
                <td><input type="checkbox" id="egreso_hospitalizacion" name="tipos_egreso[]" value="1"
                        class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_referencia" name="tipos_egreso[]" value="2" class="form-checkbox">
                </td>
                <td><input type="checkbox" id="egreso_referencia_inversa" name="tipos_egreso[]" value="3"
                        class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_derivacion" name="tipos_egreso[]" value="4" class="form-checkbox">
                </td>
                <td><input type="text" id="egreso_establecimiento" name="egreso_establecimiento" class="form-input"
                        placeholder="Nombre establecimiento"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th class="subheader" colspan="5">Observación</th>
                <th class="subheader" colspan="2" style="text-align:left;">Días de reposo</th>
            </tr>
            <tr>
                <td colspan="5">
                    <textarea id="egreso_observacion" name="egreso_observacion" class="form-textarea" rows="1"
                        placeholder="Observaciones..."></textarea>
                </td>
                <td colspan="2">
                    <input type="number" id="egreso_dias_reposo" name="egreso_dias_reposo" class="form-input" min="0"
                        placeholder="Días de reposo">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ✅ SCRIPT PARA CONFIGURAR EL CHECKBOX DINÁMICAMENTE -->
<script>
// Asegurar que la función esté disponible cuando se cargue esta sección
document.addEventListener('DOMContentLoaded', function() {
    // ✅ Si ya está en contexto de observación, bloquear el checkbox
    const esObservacion = window.contextoObservacion === true || window.esObservacionEmergencia === true;
    const checkbox = document.getElementById('egreso_observacion_emergencia');

    if (checkbox && esObservacion) {
        checkbox.disabled = true;
        checkbox.checked = true; // Marcarlo como ya en observación

        // ✅ AGREGAR CAMPO HIDDEN PARA QUE SE ENVÍE EL VALOR
        // Los inputs disabled no se envían con el formulario
        let hiddenInput = document.getElementById('egreso_observacion_emergencia_hidden');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'egreso_observacion_emergencia_hidden';
            hiddenInput.name = 'modalidades_egreso[]';
            hiddenInput.value = '3';
            checkbox.parentElement.appendChild(hiddenInput);
        } else {
            hiddenInput.value = '3';
        }

        // Aplicar estilo amarillo al td contenedor
        const tdContenedor = checkbox.closest('td');
        if (tdContenedor) {
            tdContenedor.style.backgroundColor = '#fef3c7'; // bg-yellow-100
            tdContenedor.style.border = '2px solid #f59e0b'; // border-yellow-500
        }
    }

    // Intentar configurar el checkbox varias veces hasta que funcione
    let intentos = 0;
    const maxIntentos = 10;

    const configurarCheckbox = function() {
        const checkbox = document.getElementById('egreso_observacion_emergencia');

        if (checkbox && typeof window.manejarObservacionEmergencia === 'function') {
            return true; // Configuración exitosa
        }

        intentos++;
        if (intentos < maxIntentos) {
            setTimeout(configurarCheckbox, 200);
        }

        return false;
    };

    // Iniciar configuración
    setTimeout(configurarCheckbox, 100);
});

// Función backup en caso de que la función global no esté disponible
function manejarObservacionEmergencia(checkbox) {
    if (typeof window.manejarObservacionEmergencia === 'function') {
        // Usar la función global si está disponible
        window.manejarObservacionEmergencia(checkbox);
    } else if (typeof window.mostrarModalObservacion === 'function' && checkbox.checked) {
        // Fallback directo
        window.mostrarModalObservacion();
    } else if (checkbox.checked) {
        // Último recurso - alert simple
        const confirmacion = confirm(
            'ENVÍO A OBSERVACIÓN\n\n' +
            'Esta funcionalidad requiere completar un motivo.\n' +
            '¿Desea continuar?'
        );

        if (!confirmacion) {
            checkbox.checked = false;
        } else {
            const motivo = prompt('Ingrese el motivo para enviar a observación:');
            if (!motivo || motivo.trim().length < 5) {
                alert('Debe ingresar un motivo válido (mínimo 5 caracteres)');
                checkbox.checked = false;
            }
        }
    }

    // ✅ CARGAR FIRMA Y SELLO cuando se marca el checkbox
    if (checkbox.checked) {
        setTimeout(() => {
            if (typeof window.cargarFirmaYSelloParaObservacion === 'function') {
                window.cargarFirmaYSelloParaObservacion();
            }
        }, 300);
    }
}
</script>