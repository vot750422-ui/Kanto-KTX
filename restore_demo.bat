@echo off
chcp 65001 >nul
title KANTO KTX - KHOI PHUC CSDL SAU DEMO

echo ============================================================================
echo      KANTO KTX - KHOI PHUC CO SO DU LIEU VE TRANG THAI BAN DAU
echo ============================================================================
echo.

set "WAMP_DIR="
if exist "C:\wamp64" set "WAMP_DIR=C:\wamp64"
if "%WAMP_DIR%"=="" if exist "F:\wamp64" set "WAMP_DIR=F:\wamp64"
if "%WAMP_DIR%"=="" if exist "D:\wamp64" set "WAMP_DIR=D:\wamp64"

set "MYSQL_EXE="
if not "%WAMP_DIR%"=="" (
    for /d %%D in ("%WAMP_DIR%\bin\mysql\mysql8.*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL_EXE=%%D\bin\mysql.exe"
    )
)
if "%MYSQL_EXE%"=="" (
    where mysql.exe >nul 2>&1 && set "MYSQL_EXE=mysql.exe"
)

if "%MYSQL_EXE%"=="" (
    echo [LOI] Khong tim thay mysql.exe de khoi phuc CSDL!
    pause
    exit /b 1
)

set "BACKUP_FILE=%~dp0backup_truoc_demo.sql"
if not exist "%BACKUP_FILE%" (
    if exist "C:\Users\Vot75\Downloads\DEMO_TIMER\backup_truoc_demo.sql" (
        set "BACKUP_FILE=C:\Users\Vot75\Downloads\DEMO_TIMER\backup_truoc_demo.sql"
    )
)

if not exist "%BACKUP_FILE%" (
    echo [LOI] Khong tim thay file backup_truoc_demo.sql!
    pause
    exit /b 1
)

echo [*] Dang khoi phuc CSDL qlktx_v2 tu file backup...
"%MYSQL_EXE%" -u root qlktx_v2 < "%BACKUP_FILE%"

if exist "%~dp0storage\demo_session.json" (
    del "%~dp0storage\demo_session.json" 2>nul
)

echo.
echo ============================================================================
echo [THANH CONG] CSDL qlktx_v2 da duoc khoi phuc ve trang thai sach ban dau!
echo So luong hop dong va trang thai tai khoan da tro ve nguyen trang.
echo ============================================================================
pause
