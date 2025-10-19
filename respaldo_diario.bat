@echo off
REM ============================================
REM Script de Respaldo Diario de Base de Datos
REM ============================================

echo ========================================
echo  RESPALDO AUTOMATICO BD EMERGENCIAS
echo ========================================
echo.

REM Cambiar al directorio del proyecto
cd /d "C:\xampp\htdocs\Formulario-Digital"

REM Ejecutar comando de respaldo diario
php spark respaldo:automatico diario

REM Mostrar resultado
if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Respaldo completado exitosamente
) else (
    echo.
    echo [ERROR] El respaldo fallo con codigo: %ERRORLEVEL%
)

echo.
echo Presione cualquier tecla para cerrar...
pause > nul
