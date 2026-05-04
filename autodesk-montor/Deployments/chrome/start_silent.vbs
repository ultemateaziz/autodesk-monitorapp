Set WshShell = CreateObject("WScript.Shell")

' Force Windows to look in the correct folder
WshShell.CurrentDirectory = "C:\ChromeTestMonitor\"

' Run the test monitor hidden (0) and don't wait for it to finish (False)
WshShell.Run "C:\ChromeTestMonitor\chrome.exe", 0, False

Set WshShell = Nothing
