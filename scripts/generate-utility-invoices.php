<?php

// Script CLI: Tự động lập hóa đơn điện nước (UC08 / UC10.2)
// Được gọi bởi scheduler / timer vào ngày đầu tháng, hoặc chạy thủ công có tham số.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    echo "Lệnh này chỉ có thể thực thi từ giao diện dòng lệnh (CLI).\n";
    exit(1);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UtilityInvoiceGenerator.php';

// Phân tích tham số linh hoạt: --month=2026-11, --month=11 --year=2026, hoặc -m 11 -y 2026
$options = getopt('m:y:', ['month:', 'year:']);
$rawMonth = $options['month'] ?? ($options['m'] ?? null);
$rawYear = $options['year'] ?? ($options['y'] ?? null);

$month = null;
$year = $rawYear !== null ? (int) $rawYear : null;

if (is_string($rawMonth) && preg_match('/^(\d{4})-(\d{1,2})$/', trim($rawMonth), $matches)) {
    $year = (int) $matches[1];
    $month = (int) $matches[2];
} elseif (is_string($rawMonth) && preg_match('/^(\d{1,2})[\/-](\d{4})$/', trim($rawMonth), $matches)) {
    $month = (int) $matches[1];
    $year = (int) $matches[2];
} elseif ($rawMonth !== null && is_numeric($rawMonth)) {
    $month = (int) $rawMonth;
}

$generator = new UtilityInvoiceGenerator($pdo);

try {
    $result = $generator->generateForMonth($month, $year);

    $period = $result['period'];
    echo sprintf("=== KẾT QUẢ TỰ ĐỘNG LẬP HÓA ĐƠN ĐIỆN NƯỚC THÁNG %02d/%d ===\n", $period['month'], $period['year']);
    echo sprintf("- Đơn giá Điện: %s đ/kWh (mã: %s, áp dụng từ: %s)\n",
        number_format((float) $result['rates']['dien']['GiaTri'], 0, ',', '.'),
        $result['rates']['dien']['MaDonGia'],
        $result['rates']['dien']['NgayApDung']
    );
    echo sprintf("- Đơn giá Nước: %s đ/m³ (mã: %s, áp dụng từ: %s)\n",
        number_format((float) $result['rates']['nuoc']['GiaTri'], 0, ',', '.'),
        $result['rates']['nuoc']['MaDonGia'],
        $result['rates']['nuoc']['NgayApDung']
    );
    echo sprintf("- Số phòng đã tạo hóa đơn: %d\n", $result['processed']);
    echo sprintf("- Số phòng bỏ qua: %d\n", $result['skipped']);
    echo sprintf("- Số phòng thất bại: %d\n", $result['failed']);

    if (!empty($result['details'])) {
        echo "\nChi tiết xử lý:\n";
        foreach ($result['details'] as $item) {
            if ($item['status'] === 'created') {
                echo sprintf("  [TẠO MỚI] Phòng %s: Hóa đơn %s, tổng %s đ, liên kết %d sinh viên\n",
                    $item['room'],
                    $item['invoiceId'],
                    number_format($item['total'], 0, ',', '.'),
                    $item['studentsCount']
                );
            } elseif ($item['status'] === 'skipped') {
                echo sprintf("  [BỎ QUA]  Phòng %s: %s\n", $item['room'], $item['reason']);
            } else {
                echo sprintf("  [LỖI]     Phòng %s: %s\n", $item['room'], $item['reason']);
            }
        }
    }

    echo "\nHoàn thành tiến trình.\n";
    exit($result['failed'] > 0 ? 1 : 0);
} catch (DomainException $e) {
    echo "LỖI NGHIỆP VỤ: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    echo "LỖI HỆ THỐNG: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
