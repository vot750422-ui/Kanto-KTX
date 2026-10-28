<?php
require_once __DIR__ . '/../models/StatisticsReport.php';

class StatisticsController
{
    public function __construct(private PDO $db) {}

    public function show(): void
    {
        // Báo cáo chỉ đọc dữ liệu, vì vậy không nhận request ghi.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }

        $type = trim((string)($_GET['type'] ?? 'accommodation'));
        $startDate = $this->dateOrNull($_GET['start_date'] ?? null);
        $endDate = $this->dateOrNull($_GET['end_date'] ?? null);
        $errors = [];
        if (!in_array($type, StatisticsReport::TYPES, true)) { $errors[] = 'Loại thống kê không hợp lệ.'; }
        if (trim((string)($_GET['start_date'] ?? '')) !== '' && $startDate === null) { $errors[] = 'Ngày bắt đầu không hợp lệ.'; }
        if (trim((string)($_GET['end_date'] ?? '')) !== '' && $endDate === null) { $errors[] = 'Ngày kết thúc không hợp lệ.'; }
        if ($startDate !== null && $endDate !== null && $endDate < $startDate) { $errors[] = 'Khoảng thời gian không hợp lệ. Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'; }
        $result = ['summary' => [], 'items' => []];
        if (!$errors) {
            try { $result = (new StatisticsReport($this->db))->generate($type, $startDate, $endDate); }
            catch (Throwable $error) { http_response_code(500); error_log('UC14 thống kê thất bại (' . get_class($error) . ').'); $errors[] = 'Chưa thể tải dữ liệu thống kê. Vui lòng thử lại sau.'; }
        }
        $displayName=$_SESSION['user']['TenDangNhap'];
        require __DIR__ . '/../views/statistics/index.php';
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = trim((string)$value); if ($value === '') { return null; }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }
}
