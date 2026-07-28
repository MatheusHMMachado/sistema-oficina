@echo off
REM Inicia o servidor Laravel com PHP 8.4 (nao use o PHP do XAMPP)
set PATH=C:\php;%PATH%
cd /d "%~dp0"
php -v
php artisan serve --host=127.0.0.1 --port=8000
pause
