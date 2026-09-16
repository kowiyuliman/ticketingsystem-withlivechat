@echo off
title Ticketing System Server (192.168.200.6:8000)
cd /d %~dp0
echo ========================================================
echo   Starting IT Helpdesk Ticketing System
echo   Server URL: http://192.168.200.6:8000
echo ========================================================
php artisan serve --host=0.0.0.0 --port=8000
pause

start_server.bat