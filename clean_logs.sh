#!/bin/bash

# Script to remove all log_message statements from PHP files

files=(
    "app/Controllers/Medicos/DatosMedicosController.php"
    "app/Controllers/Medicos/MedicosController.php"
    "app/Controllers/Medicos/ModalEvolucionPrescripcionesController.php"
    "app/Controllers/Especialidades/DatosEspecialidadController.php"
    "app/Controllers/Especialidades/EnfermeriaEspecialidadController.php"
    "app/Controllers/Especialidades/EspecialidadController.php"
    "app/Controllers/Especialidades/ListaEspecialidadesController.php"
    "app/Controllers/Especialidades/ModalEvolucionPrescripcionesController.php"
    "app/Controllers/Especialidades/ObservacionController.php"
    "app/Controllers/Especialidades/ProcesoEspecialidadController.php"
    "app/Controllers/Especialidades/ReportesController.php"
)

total_removed=0

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        # Count log statements before
        before=$(grep -c "log_message" "$file" 2>/dev/null || echo 0)
        
        # Remove log_message lines using sed
        # This removes lines containing log_message (including multi-line)
        sed -i '/log_message(/d' "$file"
        
        # Count after
        after=$(grep -c "log_message" "$file" 2>/dev/null || echo 0)
        
        removed=$((before - after))
        total_removed=$((total_removed + removed))
        
        echo "✓ $(basename $file): $removed logs removed"
    else
        echo "✗ File not found: $file"
    fi
done

echo ""
echo "Total: $total_removed log statements removed across ${#files[@]} files"
