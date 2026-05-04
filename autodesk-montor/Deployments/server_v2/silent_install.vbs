'' ─────────────────────────────────────────────
''  HazeMonitor v2 - Silent Installer
''  Run this to install without showing a CMD window
''  Must be run as Administrator
'' ─────────────────────────────────────────────

Set objShell = CreateObject("Shell.Application")
Set objFSO   = CreateObject("Scripting.FileSystemObject")

'' Get the folder this VBS file is in
strFolder = objFSO.GetParentFolderName(WScript.ScriptFullName)
strBat    = strFolder & "\install.bat"

'' Run install.bat as Administrator silently (runas = elevated)
objShell.ShellExecute "cmd.exe", "/c """ & strBat & """", strFolder, "runas", 0

Set objShell = Nothing
Set objFSO   = Nothing
