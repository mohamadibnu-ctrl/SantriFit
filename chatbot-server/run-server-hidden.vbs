Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "C:\laragon\www\SantriFit\chatbot-server"
nodeBin = "C:\laragon\bin\nodejs\node-v18\node.exe"
serverJs = "C:\laragon\www\SantriFit\chatbot-server\server.js"
cmd = Chr(34) & nodeBin & Chr(34) & " " & Chr(34) & serverJs & Chr(34)
WshShell.Run cmd, 0, False
