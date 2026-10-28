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

// Phân tích tham số: --month=M --year=YYYY hoặc -m M -y YYYY
$options = getopt('m:y:', ['month:', 'year:']);
$month = isset($options['month']) ? (int) $options['month'] : (isset($options['m']) ? (int) $options['m'] : null);
$year = isset($options['year']) ? (int) $options['year'] : (isset($options['y']) ? (int) $options['y'] : null);

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
