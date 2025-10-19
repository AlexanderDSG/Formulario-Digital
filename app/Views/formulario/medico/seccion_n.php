<!-- SECCION N ESPECIALIDADES -->
<div class="diagnostico-table-container bg-white shadow-xl rounded-lg overflow-hidden mt-6">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="7" class="header-main">N. PLAN DE TRATAMIENTO</th>
            </tr>
            <tr>
                <th class="subheader text-center" style="width:2em;"></th>
                <th class="subheader">Medicamentos</th>
                <th class="subheader">Vía</th>
                <th class="subheader">Dosis</th>
                <th class="subheader">Posología</th>
                <th class="subheader">Días</th>
                <th class="subheader text-center" style="width:120px;">ADMINISTRADO</th>
            </tr>
        </thead>
        <tbody id="tratamientos-tbody">
            <script>
                (function () {
                    const tbody = document.getElementById('tratamientos-tbody');
                    if (!tbody) return;

                    // Generar 7 filas de tratamientos
                    for (let i = 1; i <= 7; i++) {
                        const fila = crearFilaTratamiento(i);
                        tbody.appendChild(fila);
                    }

                    // Agregar fila de observaciones al final
                    const filaObservaciones = document.createElement('tr');
                    filaObservaciones.innerHTML = `
                    <td colspan="7">
                        <textarea id="plan_tratamiento" name="plan_tratamiento" class="form-textarea" rows="1"
                            placeholder="Observaciones..."></textarea>
                    </td>
                `;
                    tbody.appendChild(filaObservaciones);

                    function crearFilaTratamiento(numero) {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                        <td class="examen-fisico-numeral text-center">${numero}</td>
                        <td><input type="text" id="trat_med${numero}" name="trat_med${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_via${numero}" name="trat_via${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_dosis${numero}" name="trat_dosis${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_posologia${numero}" name="trat_posologia${numero}" class="form-input"></td>
                        <td><input type="number" id="trat_dias${numero}" name="trat_dias${numero}" class="form-input" min="1"></td>
                        <td class="text-center">
                            <button type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 bg-green-100 hover:bg-green-200 text-green-600 hover:text-green-700 rounded-full transition-colors btn-administrado-tratamiento"
                                    id="btn_administrado${numero}"
                                    data-row="${numero}"
                                    title="Marcar como administrado"
                                    onclick="toggleAdministradoTratamiento(this)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <input type="hidden" name="trat_administrado${numero}" id="trat_administrado${numero}" value="0">
                            <input type="hidden" name="trat_id${numero}" id="trat_id${numero}" value="">
                        </td>
                    `;
                        return fila;
                    }
                })();
            </script>
        </tbody>


    </table>

</div>
<!-- JavaScript para preview de imágenes -->
<script>
    // Función para toggle del estado "administrado" en tratamientos (igual que en evolución y prescripciones)
    function toggleAdministradoTratamiento(button) {
        const row = button.getAttribute('data-row');
        const hiddenInput = document.getElementById(`trat_administrado${row}`);

        if (button.classList.contains('bg-green-100')) {
            // Cambiar a administrado (verde activo)
            button.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
            button.setAttribute('title', 'Administrado');

            // Cambiar icono a check relleno
            const svg = button.querySelector('svg path');
            if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');

            // Actualizar valor hidden
            hiddenInput.value = '1';
        } else {
            // Cambiar a no administrado (verde claro)
            button.classList.remove('bg-green-600', 'hover:bg-green-700', 'text-white');
            button.classList.add('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
            button.setAttribute('title', 'Marcar como administrado');

            // Cambiar icono a check simple
            const svg = button.querySelector('svg path');
            if (svg) svg.setAttribute('d', 'M5 13l4 4L19 7');

            // Actualizar valor hidden
            hiddenInput.value = '0';
        }
    }
</script>