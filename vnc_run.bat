@echo off
set "raw=%~1"
set "raw=%raw:vnc://=%"
set "raw=%raw:vnc:=%"
set "raw=%raw:/=%"
set "raw=%raw:\=%"
set "raw=%raw:"=%"

if exist "C:\Program Files\TightVNC\tvnviewer.exe" (
    start "" "C:\Program Files\TightVNC\tvnviewer.exe" %raw%
) else if exist "C:\Program Files (x86)\TightVNC\tvnviewer.exe" (
    start "" "C:\Program Files (x86)\TightVNC\tvnviewer.exe" %raw%
)

