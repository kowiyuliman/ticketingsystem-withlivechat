Set args = WScript.Arguments
Set objShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

exePath = "C:\Program Files\TightVNC\tvnviewer.exe"
If Not fso.FileExists(exePath) Then
    exePath = "C:\Program Files (x86)\TightVNC\tvnviewer.exe"
End If

If fso.FileExists(exePath) Then
    rawUrl = ""
    If args.Count > 0 Then
        rawUrl = args(0)
        rawUrl = Replace(rawUrl, "vnc://", "", 1, -1, 1)
        rawUrl = Replace(rawUrl, "vnc:", "", 1, -1, 1)
        rawUrl = Replace(rawUrl, "/", "")
        rawUrl = Replace(rawUrl, "\", "")
        rawUrl = Replace(rawUrl, """", "")
        rawUrl = Trim(rawUrl)
    End If
    
    If Len(rawUrl) > 0 Then
        cmd = """" & exePath & """ " & rawUrl
    Else
        cmd = """" & exePath & """"
    End If
    
    objShell.Run cmd, 1, False
End If
