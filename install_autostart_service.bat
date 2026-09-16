@echo off
title Install AutoStart Service
cd /d "%~dp0"
echo ========================================================
echo   Installing Ticketing System AutoStart (Windows Task)
echo ========================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup_autostart.ps1"
echo.
echo ========================================================
echo   AutoStart Service Installed Successfully!
echo   The server will now auto-start when Windows boots
echo   and auto-restart if it ever crashes.
echo ========================================================
pause

