@echo off
chcp 65001 >nul
title KANTO KTX - DEMO 3 TAC VU TIMER VA MO KHOA TAI KHOAN (CMD NATIVE)

echo ============================================================================
echo      HE THONG QUAN LY KY TUC XA KANTO KTX - KICH BAN DEMO TAC VU TIMER
echo ============================================================================
echo.

:: 1. Nhan dien thu muc du an
set "REPO="
if exist "%~dp0config\db.php" set "REPO=%~dp0"
if "%REPO%"=="" if exist "C:\wamp64\www\Kanto-KTX\config\db.php" set "REPO=C:\wamp64\www\Kanto-KTX"
if "%REPO%"=="" if exist "F:\wamp64\www\Kanto-KTX\config\db.php" set "REPO=F:\wamp64\www\Kanto-KTX"
if "%REPO%"=="" if exist "F:\Kanto-KTX\config\db.php" set "REPO=F:\Kanto-KTX"
if "%REPO%"=="" if exist "D:\wamp64\www\Kanto-KTX\config\db.php" set "REPO=D:\wamp64\www\Kanto-KTX"

if "%REPO%"=="" (
    echo [LOI] Khong tim thay thu muc ma nguon Kanto-KTX!
    echo Vui long dat file batch trong thu muc du an hoac thu muc wamp64\www\Kanto-KTX.
    pause
    exit /b 1
)

:: 2. Nhan dien WAMP va cong cu PHP, MySQL
set "WAMP_DIR="
if exist "C:\wamp64" set "WAMP_DIR=C:\wamp64"
if "%WAMP_DIR%"=="" if exist "F:\wamp64" set "WAMP_DIR=F:\wamp64"
if "%WAMP_DIR%"=="" if exist "D:\wamp64" set "WAMP_DIR=D:\wamp64"

set "PHP_EXE="
if not "%WAMP_DIR%"=="" (
    if exist "%WAMP_DIR%\bin\php\php8.2.29\php.exe" set "PHP_EXE=%WAMP_DIR%\bin\php\php8.2.29\php.exe"
    if "%PHP_EXE%"=="" if exist "%WAMP_DIR%\bin\php\php8.3.28\php.exe" set "PHP_EXE=%WAMP_DIR%\bin\php\php8.3.28\php.exe"
    if "%PHP_EXE%"=="" if exist "%WAMP_DIR%\bin\php\php8.4.15\php.exe" set "PHP_EXE=%WAMP_DIR%\bin\php\php8.4.15\php.exe"
    if "%PHP_EXE%"=="" (
        for /d %%D in ("%WAMP_DIR%\bin\php\php8.*") do (
            if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
        )
    )
)
if "%PHP_EXE%"=="" (
    where php.exe >nul 2>&1 && set "PHP_EXE=php.exe"
)

if "%PHP_EXE%"=="" (
    echo [LOI] Khong tim thay trinh thuc thi php.exe!
    echo Vui long khoi dong WAMP hoac them PHP vao PATH he thong.
    pause
    exit /b 1
)

set "MYSQL_EXE="
set "DUMP_EXE="
if not "%WAMP_DIR%"=="" (
    for /d %%D in ("%WAMP_DIR%\bin\mysql\mysql8.*") do (
        if exist "%%D\bin\mysql.exe" set "MYSQL_EXE=%%D\bin\mysql.exe"
        if exist "%%D\bin\mysqldump.exe" set "DUMP_EXE=%%D\bin\mysqldump.exe"
    )
)
if "%MYSQL_EXE%"=="" (
    where mysql.exe >nul 2>&1 && set "MYSQL_EXE=mysql.exe"
    where mysqldump.exe >nul 2>&1 && set "DUMP_EXE=mysqldump.exe"
)

echo [*] Thu muc ma nguon : %REPO%
echo [*] PHP thuc thi     : %PHP_EXE%
echo [*] Web dang nhap    : http://localhost/Kanto-KTX/index.php?action=login
echo.

:: 3. Sao luu CSDL truoc khi demo
set "BACKUP_FILE=%~dp0backup_truoc_demo.sql"
if not "%DUMP_EXE%"=="" (
    echo [*] Dang tao ban sao luu an toan CSDL qlktx_v2...
    "%DUMP_EXE%" -u root --single-transaction --skip-lock-tables qlktx_v2 > "%BACKUP_FILE%" 2>nul
    if exist "%BACKUP_FILE%" (
        echo [OK] Da sao luu CSDL vao: backup_truoc_demo.sql
    )
)
echo.

:: ============================================================================
:: BUOC 1: UC10.1 - LAP HOA DON TIEN PHONG DINH KY
:: ============================================================================
echo ============================================================================
echo [BUOC 1/3] UC10.1 - LAP HOA DON TIEN PHONG CHO TOAN BO SINH VIEN
echo ============================================================================
echo Lenh thuc thi: php scripts\generate-room-invoices.php --date=2027-01-06
echo Giai thich   : He thong tu dong quet toan bo hop dong con han va sinh hoa don
echo               tien phong cho hoc ky tiep theo (khong can con nguoi can thiep).
echo.
"%PHP_EXE%" "%REPO%\scripts\generate-room-invoices.php" --date=2027-01-06
echo.

:: BUOC 1.5: HE THONG TU DONG CHON NGAU NHIEN 2 SINH VIEN CHO BUOI DEMO
echo [*] Dang chon ngau nhien 2 sinh vien de chuan bi cho Buoc 3...
"%PHP_EXE%" "%REPO%\scripts\demo-flow.php" --select-pair
echo.
echo Nhan phim bat ky de chuyen sang Buoc 2...
pause >nul
echo.

:: ============================================================================
:: BUOC 2: UC10.2 - LAP HOA DON DIEN NUOC THEO CHI SO PHONG
:: ============================================================================
echo ============================================================================
echo [BUOC 2/3] UC10.2 - LAP HOA DON DIEN NUOC HANG THANG
echo ============================================================================
echo Lenh thuc thi: php scripts\generate-utility-invoices.php --month=2026-11
echo Giai thich   : Tu dong tinh toan san luong tieu thu dien nuoc cua tung phong,
echo               ap don gia dinh muc va phan bo so tien deu cho cac sinh vien.
echo.
"%PHP_EXE%" "%REPO%\scripts\generate-utility-invoices.php" --month=2026-11
echo.
echo Nhan phim bat ky de chuyen sang Buoc 3 (Gia han ^& Khoa tai khoan no phi)...
pause >nul
echo.

:: ============================================================================
:: BUOC 3: UC18 - GIA HAN LUU TRU VA KHOA TAI KHOAN NO PHI
:: ============================================================================
echo ============================================================================
echo [BUOC 3/3] UC18 - TIEN TRINH GIA HAN LUU TRU VA XU LY NO PHI
echo ============================================================================
echo Lenh thuc thi: php scripts\run-renewal.php
echo Giai thich   : 
echo   - Sinh vien DA NOP TIEN: Tu dong cap hop dong moi, gia han luu tru thanh cong.
echo   - Sinh vien CHUA NOP TIEN: Tai khoan bi KHOA, trang thai luu tru 'Da roi di'.
echo.
"%PHP_EXE%" "%REPO%\scripts\run-renewal.php"
echo.

:: BUOC 4: IN HUONG DAN CHI TIET QUY TRINH MO KHOA TREN GIAO DIEN WEB
"%PHP_EXE%" "%REPO%\scripts\demo-flow.php" --show-guide

echo.
echo ============================================================================
echo [HOAN TAT] Kich ban Demo Timer da thuc hien xong!
echo Ban co the mo trinh duyet lam theo huong dan tren de trinh dien cho thay xem.
echo Sau khi demo xong, chay file 'restore_demo.bat' de khoi phuc CSDL sach ban dau.
echo ============================================================================
pause
