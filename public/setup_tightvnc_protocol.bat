@echo off
echo ===================================================
echo   SETUP PROTOKOL TIGHTVNC 1-KLIK UNTUK LAPTOP ADMIN
echo ===================================================
echo.
reg add "HKCU\Software\Classes\vnc" /ve /t REG_SZ /d "URL:TightVNC Protocol" /f
reg add "HKCU\Software\Classes\vnc" /v "URL Protocol" /t REG_SZ /d "" /f
reg add "HKCU\Software\Classes\vnc\DefaultIcon" /ve /t REG_SZ /d "\"C:\Program Files\TightVNC\tvnviewer.exe\",0" /f
reg add "HKCU\Software\Classes\vnc\shell\open\command" /ve /t REG_SZ /d "cmd.exe /c powershell.exe -WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -Command \"\"\"& { $a = '%%1' -replace '^vnc://','' -replace '^vnc:','' -replace '/',''; if (Test-Path 'C:\Program Files\TightVNC\tvnviewer.exe') { Start-Process 'C:\Program Files\TightVNC\tvnviewer.exe' $a } elseif (Test-Path 'C:\Program Files (x86)\TightVNC\tvnviewer.exe') { Start-Process 'C:\Program Files (x86)\TightVNC\tvnviewer.exe' $a } }\"\"\"" /f
echo.
echo [SUKSES] Protokol TightVNC (vnc://) berhasil didaftarkan di laptop ini!
echo Sekarang Anda dapat mengklik tombol IP Address di web untuk langsung membuka TightVNC.
echo.

