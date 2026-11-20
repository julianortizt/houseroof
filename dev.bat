@echo off
REM Arranque local de Costa Resin usando siempre PHP 8.4 (requerido por Turso).
setlocal

set "PHP84=C:\Users\Julian\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if exist "%PHP84%" (
    set "PHP=%PHP84%"
) else (
    set "PHP=php"
)

echo Usando: %PHP%
"%PHP%" -v

cd /d "%~dp0"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
