@echo off
echo === Comandos de Desarrollo Concesionario ===
echo.
echo 1. Ejecutar contenedores
echo 2. Acceder al contenedor app
echo 3. Instalar dependencias
echo 4. Ejecutar migraciones
echo 5. Ver logs
echo 6. Detener contenedores
echo.
set /p choice="Selecciona una opcion (1-6): "

if "%choice%"=="1" (
    echo Iniciando contenedores...
    docker-compose up -d
    pause
) else if "%choice%"=="2" (
    echo Accediendo al contenedor app...
    docker-compose exec app bash
) else if "%choice%"=="3" (
    echo Instalando dependencias...
    docker-compose exec app composer install
    pause
) else if "%choice%"=="4" (
    echo Ejecutando migraciones...
    docker-compose exec app php artisan migrate
    pause
) else if "%choice%"=="5" (
    echo Mostrando logs...
    docker-compose logs app -f
) else if "%choice%"=="6" (
    echo Deteniendo contenedores...
    docker-compose down
    pause
) else (
    echo Opcion no valida
    pause
)