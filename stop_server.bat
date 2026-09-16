@echo off
title Stop Ticketing System Server
cd /d "%~dp0"
echo ========================================================
echo   Stopping Ticketing System Server...
echo ========================================================
powershell -Command "Stop-ScheduledTask -TaskName 'TicketingSystem_AutoStart' -ErrorAction SilentlyContinue"
taskkill /F /IM php.exe /T 2>nul
echo Server stopped.
pause

