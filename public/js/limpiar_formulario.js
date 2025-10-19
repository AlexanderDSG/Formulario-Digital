// Escucha cambios en el radio "fuente_datos" y limpia el formulario
document.querySelectorAll('input[name="fuente_datos"]').forEach(radio => {
    radio.addEventListener('change', () => {
        limpiarFormulario();
        const fuente = radio.value === 'local' ? 'la base local' : 'la base del hospital';
        mostrarAlerta('info', `Formulario limpio. Ahora se buscará desde ${fuente}.`);
    });
});

function limpiarFormulario() {
    const campos = [
        'pac_apellido1', 'pac_apellido2', 'pac_nombre1', 'pac_nombre2', 'estab_historia_clinica',
        'pac_fecha_nacimiento', 'pac_lugar_nacimiento', 'pac_nacionalidad', 'pac_ocupacion',
        'pac_telefono_fijo', 'pac_telefono_celular',
        'res_calle_principal', 'res_calle_secundaria', 'res_referencia',
        'res_provincia', 'res_canton', 'res_parroquia', 'res_barrio_sector',
        'pac_etnia', 'pac_nacionalidad_indigena', 'pac_pueblo_indigena',
        'pac_tipo_empresa_trabaja', 'pac_edad_valor',
        'contacto_emerg_nombre', 'contacto_emerg_parentesco', 'contacto_emerg_direccion',
        'contacto_emerg_telefono', 'fuente_informacion', 'entrega_paciente_nombre_inst',
        'entrega_paciente_telefono', 'pac_tipo_empresa', 'pac_seguro'
    ];


    campos.forEach(id => {
        const input = document.getElementById(id);
        if (input) input.value = '';
    });

    // Desmarcar radios
    document.querySelectorAll('input[type="radio"]:not([name="fuente_datos"])').forEach(radio => {
        radio.checked = false;
    });

    // Resetear selects
    document.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });
}
