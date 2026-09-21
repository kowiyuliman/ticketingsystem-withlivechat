Set args = WScript.Arguments
If args.Count > 0 Then
    rawUrl = args(0)
    rawUrl = Replace(rawUrl, "vnc://", "", 1, -1, 1)
    rawUrl = Replace(rawUrl, "vnc:", "", 1, -1, 1)
    rawUrl = Replace(rawUrl, "/", "")
    rawUrl = Replace(rawUrl, "\", "")
    rawUrl = Replace(rawUrl, """", "")
    rawUrl = Trim(rawUrl)
    
    If Len(rawUrl) > 0 Then
        Set objShell = CreateObject("WScript.Shell")
        cmd = """C:\Program Files\TightVNC\tvnviewer.exe"" -host=" & rawUrl
        objShell.Run cmd, 1, False
    End If
End If

