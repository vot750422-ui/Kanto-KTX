<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only\n";
    exit(1);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Renewal.php';

$semesterId = $argv[1] ?? null;

if (!$semesterId) {
    // Tự động tìm học kỳ có hóa đơn tiền phòng được tạo gần nhất
    $stmt = $pdo->query("SELECT p.MaHocKy FROM hoadontienphong p 
        JOIN hoadon h ON h.MaHoaDon = p.MaHoaDon 
        ORDER BY h.ThoiGianLap DESC LIMIT 1");
    $semesterId = $stmt->fetchColumn() ?: 'HK22627';
}

echo "[*] Chạy tiến trình gia hạn cho học kỳ: $semesterId\n";

try {
    $renewal = new Renewal($pdo);
    $result = $renewal->run($semesterId);
    echo "Tiến trình gia hạn lưu trú hoàn tất:\n";
    echo "- Gia hạn thành công : " . $result['renewed'] . " sinh viên (đã hoàn tất nộp tiền)\n";
    echo "- Tài khoản bị khóa  : " . $result['locked'] . " sinh viên (quá hạn chưa nộp tiền)\n";
    echo "- Bỏ qua             : " . $result['skipped'] . " trường hợp\n";
} catch (Throwable $e) {
    echo "[LỖI] Tiến trình thất bại: " . $e->getMessage() . "\n";
    exit(1);
}
