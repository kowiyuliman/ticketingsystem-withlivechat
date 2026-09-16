# PowerShell script to register Ticketing System AutoStart in Windows
$ProjectDir = "C:\laragon\www\ticketingsystem-withlivechat"
$ScriptPath = "$ProjectDir\start_background.ps1"
$StartupFolder = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup"
$ShortcutPath = "$StartupFolder\TicketingSystem.lnk"
$TaskName = "TicketingSystem_AutoStart"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "  Setting up Ticketing System AutoStart (192.168.200.6:8000)" -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan

# 1. Register in Windows Startup Folder (Guaranteed on boot / login)
try {
    $WshShell = New-Object -ComObject WScript.Shell
    $Shortcut = $WshShell.CreateShortcut($ShortcutPath)
    $Shortcut.TargetPath = "powershell.exe"
    $Shortcut.Arguments = "-WindowStyle Hidden -ExecutionPolicy Bypass -File `"$ScriptPath`""
    $Shortcut.WorkingDirectory = $ProjectDir
    $Shortcut.Description = "AutoStart IT Helpdesk Ticketing System"
    $Shortcut.Save()
    Write-Host "[OK] Windows Startup Shortcut created at:" -ForegroundColor Green
    Write-Host "     $ShortcutPath" -ForegroundColor Gray
} catch {
    Write-Host "[WARN] Could not create Startup shortcut: $_" -ForegroundColor Yellow
}

# 2. Try registering in Windows Task Scheduler (for system boot trigger)
try {
    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false -ErrorAction SilentlyContinue
    $Action = New-ScheduledTaskAction -Execute "wscript.exe" -Argument "`"$ScriptPath`"" -WorkingDirectory $ProjectDir
    $TriggerLogon = New-ScheduledTaskTrigger -AtLogOn
    $Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Days 0) -RestartCount 5 -RestartInterval (New-TimeSpan -Minutes 1)
    
    Register-ScheduledTask -TaskName $TaskName -Action $Action -Trigger @($TriggerLogon) -Settings $Settings -Description "AutoStarts IT Helpdesk Ticketing System" -ErrorAction SilentlyContinue | Out-Null
    Write-Host "[OK] Task Scheduler job registered." -ForegroundColor Green
} catch {
    Write-Host "[INFO] Task Scheduler registration skipped (Startup folder is active)." -ForegroundColor Gray
}

# 3. Launch background service now if not running
$existing = Get-Process -Name "php" -ErrorAction SilentlyContinue
if (!$existing) {
    Write-Host "[INFO] Starting background server watchdog..." -ForegroundColor Cyan
    Start-Process "wscript.exe" -ArgumentList "`"$ScriptPath`"" -WorkingDirectory $ProjectDir
} else {
    Write-Host "[OK] PHP Server process is already running." -ForegroundColor Green
}

Write-Host "========================================================" -ForegroundColor Green
Write-Host "  AutoStart Setup Complete!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
