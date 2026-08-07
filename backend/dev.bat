@echo off
REM ---------------------------------------------------------------------
REM  Double-click shim for dev.ps1.
REM
REM  Windows opens a double-clicked .ps1 in Notepad instead of running it,
REM  and the default execution policy blocks local scripts, so this batch
REM  file invokes PowerShell properly. All arguments are passed through:
REM
REM    dev.bat                - serve on port 8000
REM    dev.bat -Port 8080     - serve on port 8080
REM
REM  From a PowerShell prompt you can skip this file and run:
REM    .\dev.ps1 -Port 8080
REM ---------------------------------------------------------------------

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0dev.ps1" %*
