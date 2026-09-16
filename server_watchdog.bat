@echo off
title Ticketing System Watchdog (Auto-Restart)
cd /d "C:\laragon\www\ticketingsystem-withlivechat"

:loop
echo [%date% %time%] Starting Laravel server on 0.0.0.0:8000...
"C:\xampp\php\php.exe" artisan serve --host=0.0.0.0 --port=8000
echo [%date% %time%] Server process stopped or died. Auto-restarting in 3 seconds...
timeout /t 3 /nobreak >nul
goto loop

