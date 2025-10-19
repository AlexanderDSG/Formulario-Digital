@echo off
REM ============================================
REM Script de Respaldo Semanal de Base de Datos
REM ============================================

echo ========================================
echo  RESPALDO SEMANAL BD EMERGENCIAS
echo ========================================
echo.

REM Cambiar al directorio del proyecto
cd /d "C:\xampp\htdocs\Formulario-Digital"

REM Ejecutar comando de respaldo semanal
php spark respaldo:automatico semanal

REM Mostrar resultado
if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Respaldo semanal completado exitosamente
) else (
    echo.
    echo [ERROR] El respaldo semanal fallo con codigo: %ERRORLEVEL%
)

echo.
echo Presione cualquier tecla para cerrar...
pause > nul
