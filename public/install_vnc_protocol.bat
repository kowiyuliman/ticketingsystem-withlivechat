@echo off
setlocal
echo ====================================================
echo   INSTALLER PROTOKOL TIGHTVNC 1-KLIK (LAPTOP ADMIN)
echo ====================================================
echo.

set "DEST_DIR=%LocalAppData%\TightVNC"
if not exist "%DEST_DIR%" mkdir "%DEST_DIR%"

copy /y "%~dp0TightVNCLauncher.exe" "%DEST_DIR%\TightVNCLauncher.exe" >nul
if not exist "%DEST_DIR%\TightVNCLauncher.exe" (
    echo [ERROR] Gagal menyalin TightVNCLauncher.exe
    pause
    exit /b 1
)

:: Daftarkan protokol vnc:// ke TightVNCLauncher.exe
reg add "HKCU\Software\Classes\vnc" /ve /t REG_SZ /d "URL:TightVNC Protocol" /f >nul
reg add "HKCU\Software\Classes\vnc" /v "URL Protocol" /t REG_SZ /d "" /f >nul
reg add "HKCU\Software\Classes\vnc\DefaultIcon" /ve /t REG_SZ /d "\"%DEST_DIR%\TightVNCLauncher.exe\",0" /f >nul
reg add "HKCU\Software\Classes\vnc\shell" /ve /t REG_SZ /d "open" /f >nul
reg add "HKCU\Software\Classes\vnc\shell\open" /ve /t REG_SZ /d "" /f >nul
reg add "HKCU\Software\Classes\vnc\shell\open\command" /ve /t REG_SZ /d "\"%DEST_DIR%\TightVNCLauncher.exe\" \"%%1\"" /f >nul

:: Hilangkan popup konfirmasi pada Google Chrome & Microsoft Edge (Auto Launch)
reg add "HKCU\Software\Policies\Google\Chrome" /v "AutoLaunchProtocolsFromOrigins" /t REG_SZ /d "[{\"allowed_origins\":[\"*\"],\"protocol\":\"vnc\"}]" /f >nul 2>&1
reg add "HKCU\Software\Policies\Microsoft\Edge" /v "AutoLaunchProtocolsFromOrigins" /t REG_SZ /d "[{\"allowed_origins\":[\"*\"],\"protocol\":\"vnc\"}]" /f >nul 2>&1

echo [BERHASIL] Protokol TightVNC (vnc://) berhasil didaftarkan dan popup otomatis di-bypass!
echo Sekarang saat Anda mengklik tombol IP Address di web, TightVNC akan LANGSUNG TERBUKA seketika.
echo.
pause

