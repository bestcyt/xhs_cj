@echo off
chcp 65001 > nul
setlocal enabledelayedexpansion

start "" /B "C:\Program Files\Google\Chrome\Application\chrome.exe" --disable-background-timer-throttling --disable-renderer-backgrounding --enable-aggressive-domstorage-flushing --disable-backgrounding-occluded-windows --profile-directory="%~n0"

timeout /t 2 > nul
exit