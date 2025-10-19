@echo off
REM ============================================
REM Script de Respaldo Mensual de Base de Datos
REM ============================================

echo ========================================
echo  RESPALDO MENSUAL BD EMERGENCIAS
echo ========================================
echo.

REM Cambiar al directorio del proyecto
cd /d "C:\xampp\htdocs\Formulario-Digital"

REM Ejecutar comando de respaldo mensual
php spark respaldo:automatico mensual

REM Mostrar resultado
if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Respaldo mensual completado exitosamente
) else (
    echo.
    echo [ERROR] El respaldo mensual fallo con codigo: %ERRORLEVEL%
)

echo.
echo Presione cualquier tecla para cerrar...
pause > nul
