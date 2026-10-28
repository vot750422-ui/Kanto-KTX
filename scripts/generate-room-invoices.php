<?php

// Script CLI: Timer lập hóa đơn tiền phòng kỳ kế tiếp theo UC10.1.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/RoomInvoiceGenerator.php';

$options = getopt('', ['date:']);
$date = $options['date'] ?? null;
$now = null;
if ($date !== null) {
    $timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
    $now = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date, $timezone);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$now || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        fwrite(STDERR, "Ngày chạy phải có dạng YYYY-MM-DD.\n");
        exit(1);
    }
}

try {
    $result = (new RoomInvoiceGenerator($pdo))->generate($now);
    echo sprintf("Đã lập %d hóa đơn, bỏ qua %d, lỗi %d.\n", $result['processed'], $result['skipped'], $result['failed']);
    exit($result['failed'] > 0 ? 1 : 0);
} catch (DomainException $error) {
    fwrite(STDERR, 'Lỗi nghiệp vụ: ' . $error->getMessage() . PHP_EOL);
    exit(1);
} catch (Throwable $error) {
    fwrite(STDERR, "Lỗi hệ thống khi lập hóa đơn tiền phòng.\n");
    error_log('UC10.1 thất bại (' . get_class($error) . ').');
    exit(1);
}
