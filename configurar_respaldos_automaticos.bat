@echo off
REM ============================================================
REM Script de Configuración Automática de Respaldos
REM Este script configura el Programador de Tareas de Windows
REM ============================================================

echo =========================================================
echo   CONFIGURACION AUTOMATICA DE RESPALDOS BD EMERGENCIAS
echo =========================================================
echo.
echo Este script configurara 3 tareas en el Programador:
echo   1. Respaldo DIARIO (cada 6 horas: 2AM, 8AM, 2PM, 8PM)
echo   2. Respaldo SEMANAL (Domingos 2AM)
echo   3. Respaldo MENSUAL (Dia 1 del mes, 2AM)
echo.
echo Presione cualquier tecla para continuar...
pause > nul

REM Verificar si se ejecuta como Administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo [ERROR] Este script debe ejecutarse como Administrador
    echo.
    echo Por favor:
    echo 1. Click derecho en el archivo
    echo 2. Seleccionar "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] Ejecutando con privilegios de administrador
echo.

REM Crear carpeta de respaldos si no existe
if not exist "C:\Respaldos_BD" (
    echo Creando carpeta de respaldos...
    mkdir "C:\Respaldos_BD"
    echo [OK] Carpeta creada
) else (
    echo [OK] Carpeta de respaldos ya existe
)

echo.
echo ========================================
echo   CONFIGURANDO RESPALDO DIARIO
echo ========================================

REM Eliminar tarea existente si existe
schtasks /delete /tn "Respaldo BD Emergencias - Diario" /f >nul 2>&1

REM Crear tarea diaria con múltiples horarios
schtasks /create /tn "Respaldo BD Emergencias - Diario" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_diario.bat" /sc daily /st 02:00 /ru SYSTEM /rl HIGHEST /f

if %errorLevel% equ 0 (
    echo [OK] Tarea diaria configurada: 02:00 AM

    REM Agregar horarios adicionales (8AM, 2PM, 8PM)
    schtasks /create /tn "Respaldo BD Emergencias - Diario 8AM" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_diario.bat" /sc daily /st 08:00 /ru SYSTEM /rl HIGHEST /f >nul 2>&1
    echo [OK] Tarea diaria configurada: 08:00 AM

    schtasks /create /tn "Respaldo BD Emergencias - Diario 2PM" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_diario.bat" /sc daily /st 14:00 /ru SYSTEM /rl HIGHEST /f >nul 2>&1
    echo [OK] Tarea diaria configurada: 02:00 PM

    schtasks /create /tn "Respaldo BD Emergencias - Diario 8PM" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_diario.bat" /sc daily /st 20:00 /ru SYSTEM /rl HIGHEST /f >nul 2>&1
    echo [OK] Tarea diaria configurada: 08:00 PM
) else (
    echo [ERROR] No se pudo crear la tarea diaria
)

echo.
echo ========================================
echo   CONFIGURANDO RESPALDO SEMANAL
echo ========================================

REM Eliminar tarea existente si existe
schtasks /delete /tn "Respaldo BD Emergencias - Semanal" /f >nul 2>&1

REM Crear tarea semanal (Domingos)
schtasks /create /tn "Respaldo BD Emergencias - Semanal" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_semanal.bat" /sc weekly /d SUN /st 02:00 /ru SYSTEM /rl HIGHEST /f

if %errorLevel% equ 0 (
    echo [OK] Tarea semanal configurada: Domingos 02:00 AM
) else (
    echo [ERROR] No se pudo crear la tarea semanal
)

echo.
echo ========================================
echo   CONFIGURANDO RESPALDO MENSUAL
echo ========================================

REM Eliminar tarea existente si existe
schtasks /delete /tn "Respaldo BD Emergencias - Mensual" /f >nul 2>&1

REM Crear tarea mensual (Dia 1)
schtasks /create /tn "Respaldo BD Emergencias - Mensual" /tr "C:\xampp\htdocs\Formulario-Digital\respaldo_mensual.bat" /sc monthly /d 1 /st 02:00 /ru SYSTEM /rl HIGHEST /f

if %errorLevel% equ 0 (
    echo [OK] Tarea mensual configurada: Dia 1 del mes, 02:00 AM
) else (
    echo [ERROR] No se pudo crear la tarea mensual
)

echo.
echo =========================================================
echo   CONFIGURACION COMPLETADA
echo =========================================================
echo.
echo Tareas programadas creadas exitosamente:
echo   - Respaldo Diario: 2AM, 8AM, 2PM, 8PM (cada dia)
echo   - Respaldo Semanal: Domingos 2AM
echo   - Respaldo Mensual: Dia 1 del mes, 2AM
echo.
echo Ruta de respaldos: C:\Respaldos_BD\
echo.
echo =========================================================
echo   PROBAR RESPALDO AHORA?
echo =========================================================
echo.
echo Desea ejecutar un respaldo de prueba ahora? (S/N)
set /p respuesta=

if /i "%respuesta%"=="S" (
    echo.
    echo Ejecutando respaldo de prueba...
    echo.
    cd /d "C:\xampp\htdocs\Formulario-Digital"
    php spark respaldo:automatico diario
    echo.
    echo Verifique la carpeta: C:\Respaldos_BD\
    echo.
)

echo.
echo Para ver las tareas programadas:
echo   1. Presione Windows + R
echo   2. Escriba: taskschd.msc
echo   3. Busque "Respaldo BD Emergencias"
echo.
echo Presione cualquier tecla para salir...
pause > nul
