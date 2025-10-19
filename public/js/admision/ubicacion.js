// Función para obtener la ruta base automáticamente
function obtenerRutaBase() {
  const pathname = window.location.pathname;
  const match = pathname.match(/^(\/[^\/]+)/);
  if (match && match[1] !== "/index.php") {
    return match[1];
  }
  return "";
}

// JAVASCRIPT PARA CASCADA DE UBICACIONES
document.addEventListener("DOMContentLoaded", function () {
  const provinciaSelect = document.getElementById("nac_provincia");
  const cantonSelect = document.getElementById("nac_canton");
  const parroquiaSelect = document.getElementById("nac_parroquia");

  const nacionalidadSelect = document.getElementById("pac_nacionalidad");
  const lugarEcuador = document.getElementById("lugar_nac_ecuador");
  const lugarExtranjero = document.getElementById("lugar_nac_extranjero");
  const lugarPlaceholder = document.getElementById("lugar_nac_placeholder");

  const resProvinciaInput = document.getElementById("res_provincia");
  const resCantonInput = document.getElementById("res_canton");
  const resParroquiaInput = document.getElementById("res_parroquia");

  // Cuando cambia la nacionalidad
  if (nacionalidadSelect) {
    nacionalidadSelect.addEventListener("change", function () {
      const nacionalidadTexto =
        this.options[this.selectedIndex].text.toUpperCase();

      // Ocultar todo primero
      lugarEcuador.style.display = "none";
      lugarExtranjero.style.display = "none";
      lugarPlaceholder.style.display = "none";

      // Limpiar valores previos
      provinciaSelect.value = "";
      cantonSelect.innerHTML =
        '<option value="">Primero seleccione provincia</option>';
      cantonSelect.disabled = true;
      parroquiaSelect.innerHTML =
        '<option value="">Primero seleccione cantón</option>';
      parroquiaSelect.disabled = true;
      document.getElementById("pac_lugar_nacimiento").value = "";

      // Limpiar campos de residencia
      resProvinciaInput.value = "";
      resCantonInput.value = "";
      resParroquiaInput.value = "";

      // Mostrar según nacionalidad
      if (nacionalidadTexto.includes("ECUATORIAN")) {
        lugarEcuador.style.display = "block";
      } else if (this.value) {
        lugarExtranjero.style.display = "block";
      } else {
        lugarPlaceholder.style.display = "block";
      }
    });
  }

  // Cuando cambia la provincia
  provinciaSelect.addEventListener("change", async function () {
    const provCodigo = this.value;

    // Resetear cantón y parroquia
    cantonSelect.innerHTML = '<option value="">Cargando...</option>';
    cantonSelect.disabled = true;
    parroquiaSelect.innerHTML =
      '<option value="">Primero seleccione cantón</option>';
    parroquiaSelect.disabled = true;

    if (!provCodigo) {
      cantonSelect.innerHTML =
        '<option value="">Primero seleccione provincia</option>';
      // Limpiar residencia si se deselecciona
      resProvinciaInput.value = "";
      resCantonInput.value = "";
      resParroquiaInput.value = "";
      return;
    }

    try {
      // Usar ruta base dinámica
      const rutaBase = obtenerRutaBase();
      const url = `${window.location.origin}${rutaBase}/api/ubicacion/cantones/${provCodigo}`;

      const response = await fetch(url);
      const data = await response.json();

      if (data.status === "success" && data.data.length > 0) {
        cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';
        data.data.forEach((canton) => {
          const option = document.createElement("option");
          option.value = canton.cant_codigo;
          option.textContent = canton.cant_nombre;
          cantonSelect.appendChild(option);
        });
        cantonSelect.disabled = false;

        // Llenar campo de residencia con el NOMBRE de la provincia
        const provinciaTexto = this.options[this.selectedIndex].text;
        resProvinciaInput.value = provinciaTexto;
      } else {
        cantonSelect.innerHTML =
          '<option value="">No hay cantones disponibles</option>';
      }
    } catch (error) {
      console.error("Error al cargar cantones:", error);
      cantonSelect.innerHTML =
        '<option value="">Error al cargar cantones</option>';
    }
  });

  // Cuando cambia el cantón
  cantonSelect.addEventListener("change", async function () {
    const cantCodigo = this.value;

    // Resetear parroquia
    parroquiaSelect.innerHTML = '<option value="">Cargando...</option>';
    parroquiaSelect.disabled = true;

    if (!cantCodigo) {
      parroquiaSelect.innerHTML =
        '<option value="">Primero seleccione cantón</option>';
      resCantonInput.value = "";
      resParroquiaInput.value = "";
      return;
    }

    try {
      // Usar ruta base dinámica
      const rutaBase = obtenerRutaBase();
      const url = `${window.location.origin}${rutaBase}/api/ubicacion/parroquias/${cantCodigo}`;

      const response = await fetch(url);
      const data = await response.json();

      if (data.status === "success" && data.data.length > 0) {
        parroquiaSelect.innerHTML =
          '<option value="">Seleccione parroquia</option>';
        data.data.forEach((parroquia) => {
          const option = document.createElement("option");
          option.value = parroquia.codigo;
          option.textContent = parroquia.nombre;
          parroquiaSelect.appendChild(option);
        });
        parroquiaSelect.disabled = false;

        const cantonTexto = this.options[this.selectedIndex].text;
        resCantonInput.value = cantonTexto;
      } else {
        parroquiaSelect.innerHTML =
          '<option value="">No hay parroquias disponibles</option>';
      }
    } catch (error) {
      console.error("Error al cargar parroquias:", error);
      parroquiaSelect.innerHTML =
        '<option value="">Error al cargar parroquias</option>';
    }
  });

  // Cuando cambia la parroquia
  parroquiaSelect.addEventListener("change", function () {
    const parroquiaTexto = this.options[this.selectedIndex].text;

    if (parroquiaTexto && parroquiaTexto !== "Seleccione parroquia") {
      resParroquiaInput.value = parroquiaTexto;
    } else {
      resParroquiaInput.value = "";
    }
  });

  // Habilitar campos disabled antes de enviar
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", function (e) {
      if (cantonSelect && cantonSelect.disabled && cantonSelect.value) {
        cantonSelect.disabled = false;
      }

      if (
        parroquiaSelect &&
        parroquiaSelect.disabled &&
        parroquiaSelect.value
      ) {
        parroquiaSelect.disabled = false;
      }
    });
  }
});