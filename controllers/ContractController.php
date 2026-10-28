<?php

require_once __DIR__ . '/../models/Contract.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
require_once __DIR__ . '/../config/app.php';

class ContractController
{
    private const STATUSES = ['Còn hạn', 'Hết hạn', 'Đã thanh lý'];

    public function __construct(private PDO $db) {}

    public function handle(string $action): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Các hành động tra cứu hợp đồng chỉ chấp nhận GET
        if ($method !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }

        $model = new Contract($this->db);

        if ($action === 'contract-detail') {
            $id = $this->scalar('id');
            if ($id === null || preg_match('/^[A-Za-z0-9_-]{1,10}$/D', $id) !== 1) {
                http_response_code(400);
                echo 'Mã hợp đồng không hợp lệ.';
                return;
            }

            $contract = $model->find($id);
            if ($contract === null) {
                http_response_code(404);
                echo 'Không tìm thấy hợp đồng.';
                return;
            }

            $content = __DIR__ . '/../views/contracts/detail.php';
            $pageTitle = 'Chi tiết hợp đồng';
        } else {
            [$filters, $errors] = $this->filters();
            $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

            if ($errors) {
                http_response_code(400);
                $listing = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
            } else {
                $listing = $model->listing($filters, $page);
            }

            $content = __DIR__ . '/../views/contracts/list.php';
            $pageTitle = 'Quản lý hợp đồng';
        }

        $displayName = (new StaffDashboard($this->db))->nameForAccount((int) $_SESSION['user']['MaTK'])
            ?? $_SESSION['user']['TenDangNhap'];

        require __DIR__ . '/../views/contracts/layout.php';
    }

    private function filters(): array
    {
        $filters = [];
        $errors = [];

        foreach (['contract', 'student', 'name', 'room', 'status'] as $key) {
            $value = $this->scalar($key);
            if ($value === null) {
                $errors[$key] = 'Giá trị lọc không hợp lệ.';
                $value = '';
            }
            $filters[$key] = $value;
        }

        if ($filters['contract'] !== '' && preg_match('/^[A-Za-z0-9_-]{1,10}$/D', $filters['contract']) !== 1) {
            $errors['contract'] = 'Mã hợp đồng chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới và tối đa 10 ký tự.';
        }

        if ($filters['student'] !== '' && preg_match('/^[A-Za-z0-9_-]{1,15}$/D', $filters['student']) !== 1) {
            $errors['student'] = 'MSSV chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới và tối đa 15 ký tự.';
        }

        if ($filters['name'] !== '' && (mb_strlen($filters['name']) > 100 || preg_match('/[\x00-\x1F\x7F]/u', $filters['name']))) {
            $errors['name'] = 'Họ tên không được vượt quá 100 ký tự hoặc chứa ký tự điều khiển.';
        }

        if ($filters['room'] !== '' && (mb_strlen($filters['room']) > 20 || preg_match('/^[\p{L}\p{N} ._-]+$/u', $filters['room']) !== 1)) {
            $errors['room'] = 'Số phòng không hợp lệ.';
        }

        if ($filters['status'] !== '' && !in_array($filters['status'], self::STATUSES, true)) {
            $errors['status'] = 'Trạng thái hợp đồng không hợp lệ.';
        }

        return [$filters, $errors];
    }

    private function scalar(string $key): ?string
    {
        $value = $_GET[$key] ?? '';
        return is_string($value) ? trim($value) : null;
    }
}
