# Script: scripts/register-room-invoice-task.ps1
# Mục đích: Đăng ký Task Scheduler chạy kiểm tra UC10.1 mỗi ngày.
# Không tự chạy task sau khi đăng ký.

[CmdletBinding()]
param()

$taskName = "KantoKTX-GenerateRoomInvoices"
$wrapperScript = "C:\wamp64\www\Kanto-KTX\scripts\run-room-invoice-task.bat"
$repoDir = "C:\wamp64\www\Kanto-KTX"

if (-not (Test-Path $repoDir)) {
    Write-Error "Khong tim thay thu muc du an tai: $repoDir"
    exit 1
}
if (-not (Test-Path $wrapperScript)) {
    Write-Error "Khong tim thay wrapper script: $wrapperScript"
    exit 1
}

# Chạy hằng ngày để không bỏ lỡ mốc; generator tự kiểm tra ngày nghiệp vụ.
$cmdString = "`"$wrapperScript`""
$schtasksArgs = @('/create', '/tn', $taskName, '/tr', $cmdString, '/sc', 'daily', '/st', '00:05', '/f')
$result = & schtasks.exe $schtasksArgs 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Error "Loi khi tao task qua schtasks: $result"
    exit 1
}

# Cập nhật cấu hình để task không chạy trùng và có thể chạy bù sau khi máy bật lại.
try {
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -RestartCount 0
    Set-ScheduledTask -TaskName $taskName -Settings $settings | Out-Null
} catch {
    Write-Warning "Khong the cap nhat SettingsSet qua PowerShell: $_"
}

# Chỉ đọc lại cấu hình để xác nhận task đã được đăng ký đúng.
$task = Get-ScheduledTask -TaskName $taskName -ErrorAction Stop
Write-Host "Da dang ky $($task.TaskName), trang thai: $($task.State)."
