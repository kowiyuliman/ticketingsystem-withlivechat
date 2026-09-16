# Start Server Watchdog hidden in background
$ProjectDir = "C:\laragon\www\ticketingsystem-withlivechat"
$BatchPath = "$ProjectDir\server_watchdog.bat"

# Check if port 8000 is already active
$isListening = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue

if (!$isListening) {
    Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"$BatchPath`"" -WorkingDirectory $ProjectDir -WindowStyle Hidden
    Start-Sleep -Seconds 2
}

