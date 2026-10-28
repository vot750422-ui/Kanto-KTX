@echo off
setlocal
if not exist "C:\ProgramData\KantoKTX" mkdir "C:\ProgramData\KantoKTX"
set "LOGFILE=C:\ProgramData\KantoKTX\room-invoice-task.log"

echo ============================================================ >> "%LOGFILE%"
echo [%DATE% %TIME%] Task KantoKTX-GenerateRoomInvoices started >> "%LOGFILE%"

cd /d "C:\wamp64\www\Kanto-KTX"
"C:\wamp64\bin\php\php8.3.28\php.exe" -d xdebug.mode=off "C:\wamp64\www\Kanto-KTX\scripts\generate-room-invoices.php" >> "%LOGFILE%" 2>&1
set "EXIT_CODE=%ERRORLEVEL%"

echo [%DATE% %TIME%] Task finished with exit code %EXIT_CODE% >> "%LOGFILE%"
exit /b %EXIT_CODE%
