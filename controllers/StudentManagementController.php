<?php

require_once __DIR__ . '/../models/StudentManagement.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
require_once __DIR__ . '/../config/app.php';

// UC04 cho nhân viên quản lý tra cứu và cập nhật thông tin cá nhân của sinh viên.
class StudentManagementController
{
    public function __construct(private PDO $db) {}

    public function handle(string $action): void
    {
        $write = ($action === 'student-update');
        $expectedMethod = $write ? 'POST' : 'GET';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $expectedMethod) {
            http_response_code(405);
            header('Allow: ' . $expectedMethod);
            return;
        }

        $model = new StudentManagement($this->db);

        if ($write) {
            $id = $this->id($_POST['student'] ?? null);
            if ($id === null) {
                http_response_code(400);
                echo 'MSSV không hợp lệ.';
                return;
            }

            [$form, $errors] = self::validate($_POST);
            if ($errors) {
                http_response_code(400);
                $student = $model->find($id);
                if (!$student) {
                    http_response_code(404);
                    echo 'Không tìm thấy sinh viên.';
                    return;
                }
                $student = array_merge($student, $form);
                $message = '';
                $content = __DIR__ . '/../views/students/detail.php';
                $pageTitle = 'Thông tin sinh viên';
            } else {
                try {
                    $changed = $model->update($id, $form);
                    $_SESSION['student_notice'] = $changed
                        ? 'Cập nhật thông tin sinh viên thành công.'
                        : 'Thông tin sinh viên chưa được thay đổi.';
                    header('Location: ' . appUrl('index.php?action=student-detail&id=' . rawurlencode($id)), true, 303);
                    return;
                } catch (DomainException $error) {
                    http_response_code(404);
                    echo $error->getMessage();
                    return;
                } catch (Throwable $error) {
                    http_response_code(500);
                    error_log('UC04 cập nhật thất bại (' . get_class($error) . ').');
                    echo 'Chưa thể cập nhật thông tin sinh viên.';
                    return;
                }
            }
        } elseif ($action === 'student-detail') {
            $id = $this->id($_GET['id'] ?? null);
            if ($id === null) {
                http_response_code(400);
                echo 'MSSV không hợp lệ.';
                return;
            }

            $student = $model->find($id);
            if (!$student) {
                http_response_code(404);
                echo 'Không tìm thấy sinh viên.';
                return;
            }

            $errors = [];
            $message = $_SESSION['student_notice'] ?? '';
            unset($_SESSION['student_notice']);
            $content = __DIR__ . '/../views/students/detail.php';
            $pageTitle = 'Thông tin sinh viên';
        } else {
            [$filters, $errors] = $this->filters();
            $pageInput = $_GET['page'] ?? '1';
            $page = is_scalar($pageInput) ? filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

            if ($page === false) {
                $errors['page'] = 'Trang không hợp lệ.';
                $page = 1;
            }

            if ($errors) {
                http_response_code(400);
                $listing = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
            } else {
                $listing = $model->listing($filters, (int) $page);
            }

            $content = __DIR__ . '/../views/students/list.php';
            $pageTitle = 'Quản lý sinh viên';
        }

        $displayName = (new StaffDashboard($this->db))->nameForAccount((int) $_SESSION['user']['MaTK'])
            ?? $_SESSION['user']['TenDangNhap'];

        require __DIR__ . '/../views/students/layout.php';
    }

    public static function validate(array $input): array
    {
        $data = [];
        $errors = [];
        $limits = [
            'HoTen' => 100,
            'NgaySinh' => 10,
            'QueQuan' => 150,
            'SDT' => 15,
            'Lop' => 30,
        ];

        foreach ($limits as $field => $limit) {
            $value = $input[$field] ?? '';
            if (!is_string($value)) {
                $errors[$field] = 'Dữ liệu không hợp lệ.';
                $value = '';
            }
            $trimmed = trim($value);
            $data[$field] = $trimmed;

            if ($trimmed === '') {
                $errors[$field] = 'Vui lòng nhập thông tin này.';
            } elseif (mb_strlen($trimmed, 'UTF-8') > $limit) {
                $errors[$field] = 'Thông tin vượt quá độ dài cho phép.';
            }
        }

        if (!preg_match('/^0[0-9]{9}$/D', $data['SDT'])) {
            $errors['SDT'] = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        }

        $date = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $data['NgaySinh'])
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $data['NgaySinh'])
            : false;

        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh'));
        if (!$date || $date->format('Y-m-d') !== $data['NgaySinh'] || $date >= $today) {
            $errors['NgaySinh'] = 'Ngày sinh phải hợp lệ và trước ngày hiện tại.';
        }

        return [$data, $errors];
    }

    private function id(mixed $value): ?string
    {
        if (is_string($value) && preg_match('/^[A-Za-z0-9_-]{1,15}$/D', trim($value))) {
            return trim($value);
        }
        return null;
    }

    private function filters(): array
    {
        $filters = [];
        $errors = [];
        $limits = [
            'student' => 15,
            'name' => 100,
            'room' => 20,
            'class' => 30,
            'status' => 20,
        ];

        foreach ($limits as $key => $limit) {
            $value = $_GET[$key] ?? '';
            if (!is_string($value) || mb_strlen($value, 'UTF-8') > $limit || preg_match('/[\x00-\x1f]/', $value)) {
                $errors[$key] = 'Tiêu chí không hợp lệ.';
                $value = '';
            }
            $filters[$key] = trim($value);
        }

        if ($filters['student'] !== '' && !preg_match('/^[A-Za-z0-9_-]{1,15}$/D', $filters['student'])) {
            $errors['student'] = 'MSSV không hợp lệ.';
        }

        if (!in_array($filters['status'], ['', 'Đang ở', 'Đã rời đi'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        return [$filters, $errors];
    }
}
