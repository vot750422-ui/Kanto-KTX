<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../models/ApprovalEmail.php';
$db = createDatabaseConnection();
$emails = new ApprovalEmail($db);
// Một đợt tối đa 50 email; có thể chạy bằng Task Scheduler/cron khi triển khai.
$ids = $db->query("SELECT MaDonDangKy FROM emailthongbao WHERE TrangThai IN ('Chờ gửi', 'Lỗi') ORDER BY ThoiGianTao, MaDonDangKy LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$failed = false;
foreach ($ids as $id) {
    try {
        $status = $emails->deliver($id);
        echo $id . ': ' . $status . PHP_EOL;
        $failed = $failed || $status === 'Lỗi';
    } catch (Throwable $error) {
        fwrite(STDERR, $id . ": Chưa gửi được; kiểm tra kết nối và cấu hình.\n");
        $failed = true;
    }
}
exit($failed ? 1 : 0);
