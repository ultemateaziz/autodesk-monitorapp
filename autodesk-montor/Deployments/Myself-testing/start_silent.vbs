Set WshShell = CreateObject("WScript.Shell")
Set objWMI   = GetObject("winmgmts:\\.\root\cimv2")

WshShell.CurrentDirectory = "C:\AutodeskMonitor\"

' Check if already running — prevent duplicate instances
Dim running
running = False
Dim procs
Set procs = objWMI.ExecQuery("SELECT * FROM Win32_Process WHERE Name = 'hazemonitor.exe'")
If procs.Count > 0 Then running = True

If Not running Then
    WshShell.Run "C:\AutodeskMonitor\hazemonitor.exe", 0, False
End If

' Watchdog loop — check every 60 seconds and restart if crashed
Do While True
    WScript.Sleep 60000
    Set procs = objWMI.ExecQuery("SELECT * FROM Win32_Process WHERE Name = 'hazemonitor.exe'")
    If procs.Count = 0 Then
        WshShell.Run "C:\AutodeskMonitor\hazemonitor.exe", 0, False
    End If
Loop
