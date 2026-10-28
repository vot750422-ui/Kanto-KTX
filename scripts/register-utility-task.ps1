# Script: scripts/register-utility-task.ps1
# Mục đích: Đăng ký/cập nhật Windows Task Scheduler cho UC08 (Tự động lập hóa đơn điện nước)
# Lịch chạy: 00:10 ngày 1 hàng tháng, StartWhenAvailable = true, MultipleInstances = IgnoreNew

[CmdletBinding()]
param()

$taskName = "KantoKTX-GenerateUtilityInvoices"
$phpPath = "C:\wamp64\bin\php\php8.3.28\php.exe"
$repoDir = "C:\wamp64\www\Kanto-KTX"
$businessScript = "C:\wamp64\www\Kanto-KTX\scripts\generate-utility-invoices.php"
$wrapperScript = "C:\wamp64\www\Kanto-KTX\scripts\run-utility-task.bat"
$logDir = "C:\ProgramData\KantoKTX"

Write-Host "=== KIEM TRA DIEU KIEN TRUOC KHI DANG KY TASK SCHEDULER ==="

# 1. Kiểm tra sự tồn tại của các thành phần
if (-not (Test-Path $phpPath)) {
    Write-Error "Khong tim thay PHP CLI tai: $phpPath"
    exit 1
}
Write-Host "[OK] Da xac nhan PHP CLI: $phpPath"

if (-not (Test-Path $repoDir)) {
    Write-Error "Khong tim thay thu muc du an tai: $repoDir"
    exit 1
}
Write-Host "[OK] Da xac nhan thu muc du an: $repoDir"

if (-not (Test-Path $businessScript)) {
    Write-Error "Khong tim thay script nghiep vu: $businessScript"
    exit 1
}
Write-Host "[OK] Da xac nhan script nghiep vu: $businessScript"

if (-not (Test-Path $wrapperScript)) {
    Write-Error "Khong tim thay wrapper script: $wrapperScript"
    exit 1
}
Write-Host "[OK] Da xac nhan wrapper script: $wrapperScript"

# Tạo thư mục log ngoài webroot/git nếu chưa có
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
    Write-Host "[OK] Da tao thu muc log: $logDir"
}

# 2. Đăng ký task chạy vào 00:10 ngày 1 hàng tháng
Write-Host "`n=== DANG KY TASK SCHEDULER: $taskName ==="
$cmdString = "`"$wrapperScript`""

# Tạo task bằng schtasks để có trigger MONTHLY ngày 1 lúc 00:10
$schtasksArgs = @(
    "/create",
    "/tn", $taskName,
    "/tr", $cmdString,
    "/sc", "monthly",
    "/d", "1",
    "/st", "00:10",
    "/f"
)

$res = & schtasks.exe $schtasksArgs 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Error "Loi khi tao task qua schtasks: $res"
    Write-Host "Vui long mo PowerShell voi quyen phu hop de dang ky task neu bi tu choi quyen."
    exit 1
}
Write-Host "[OK] Da dang ky task lich chay: ngay 1 hang thang luc 00:10."

# 3. Cập nhật settings: StartWhenAvailable = true, MultipleInstances = IgnoreNew, RestartCount = 0
try {
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -RestartCount 0
    Set-ScheduledTask -TaskName $taskName -Settings $settings | Out-Null
    Write-Host "[OK] Da cap nhat cau hinh: StartWhenAvailable = True, MultipleInstances = IgnoreNew, Khong co retry."
} catch {
    Write-Warning "Khong the cap nhat SettingsSet qua PowerShell: $_"
}

# 4. Xác minh lại task vừa đăng ký (Read-only, tuyệt đối không chạy task)
Write-Host "`n=== XAC MINH CAU HINH TASK ==="
$task = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($task) {
    Write-Host "- Ten Task: $($task.TaskName)"
    Write-Host "- Trang thai: $($task.State)"
    Write-Host "- Action Execute: $($task.Actions[0].Execute)"
    Write-Host "- StartWhenAvailable: $($task.Settings.StartWhenAvailable)"
    Write-Host "- MultipleInstances: $($task.Settings.MultipleInstances)"
    Write-Host "- RestartCount (Retry): $($task.Settings.RestartCount)"
    Write-Host "`nDang ky Task Scheduler thanh cong."
} else {
    Write-Error "Khong the doc thong tin task sau khi dang ky."
    exit 1
}
