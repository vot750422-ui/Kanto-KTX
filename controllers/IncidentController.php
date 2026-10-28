<?php

require_once __DIR__ . '/../models/Incident.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
require_once __DIR__ . '/../config/app.php';

class IncidentController
{
    public function __construct(private PDO $db) {}

    public function student(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }

        $model = new Incident($this->db);
        $accountId = (int) $_SESSION['user']['MaTK'];
        $stay = $model->accommodationForAccount($accountId);
        $errors = [];
        $type = is_string($_POST['type'] ?? null) ? trim($_POST['type']) : '';
        $description = is_string($_POST['description'] ?? null) ? trim($_POST['description']) : '';
        $message = $_SESSION['incident_notice'] ?? '';
        unset($_SESSION['incident_notice']);

        if ($method === 'POST') {
            // Kiểm tra dữ liệu phản ánh trước khi ghi nhận sự cố vào cơ sở dữ liệu.
            if (!in_array($type, ['Điện', 'Nước', 'Nội thất', 'Khác'], true)) {
                $errors['type'] = 'Vui lòng chọn loại sự cố.';
            }
            if ($description === '' || mb_strlen($description, 'UTF-8') > 65535) {
                $errors['description'] = 'Vui lòng nhập mô tả sự cố hợp lệ.';
            }
            if (!$stay) {
                $errors['stay'] = 'Bạn chưa có hợp đồng lưu trú còn hạn để gửi phản ánh.';
            }

            if (!$errors) {
                try {
                    // Model tự lấy MSSV/phòng từ hợp đồng hiện tại, không tin dữ liệu phòng từ form.
                    $id = $model->createForAccount($accountId, $type, $description);
                    $_SESSION['incident_notice'] = 'Gửi phản ánh sự cố thành công. Mã phản ánh: ' . $id;
                    header('Location: ' . appUrl('index.php?action=incident-report'), true, 303);
                    return;
                } catch (DomainException $error) {
                    $errors['stay'] = $error->getMessage();
                } catch (Throwable $error) {
                    http_response_code(500);
                    error_log('UC10 gửi phản ánh thất bại (' . get_class($error) . ').');
                    $errors['general'] = 'Chưa thể gửi phản ánh. Vui lòng thử lại sau.';
                }
            }
            http_response_code(400);
        }

        require __DIR__ . '/../views/incidents/student.php';
    }

    public function staff(string $action): void
    {
        $write = $action === 'incident-resolve';
        $expectedMethod = $write ? 'POST' : 'GET';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $expectedMethod) {
            http_response_code(405);
            header('Allow: ' . $expectedMethod);
            return;
        }

        $model = new Incident($this->db);
        $accountId = (int) $_SESSION['user']['MaTK'];
        $id = $this->id($write ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null));

        if ($write) {
            // Chỉ nhân viên đang làm việc mới được chốt chi phí và trạng thái sự cố.
            $cost = is_string($_POST['cost'] ?? null) ? trim($_POST['cost']) : '';
            if ($id === null || preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D', $cost) !== 1) {
                http_response_code(400);
                echo 'Chi phí sửa chữa không hợp lệ. Vui lòng nhập giá trị lớn hơn hoặc bằng 0.';
                return;
            }

            try {
                $model->resolve($id, (int) $_SESSION['user']['MaTK'], $cost);
                $_SESSION['incident_staff_notice'] = 'Cập nhật xử lý sự cố thành công.';
                header('Location: ' . appUrl('index.php?action=incidents'), true, 303);
                return;
            } catch (DomainException $error) {
                http_response_code(409);
                echo htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8');
                return;
            } catch (Throwable $error) {
                http_response_code(500);
                error_log('UC10 xử lý phản ánh thất bại (' . get_class($error) . ').');
                echo 'Chưa thể xử lý phản ánh.';
                return;
            }
        }

        if ($action === 'incident-detail') {
            // Chi tiết chỉ được đọc sau khi kiểm tra mã phản ánh hợp lệ.
            if ($id === null) {
                http_response_code(400);
                echo 'Mã phản ánh không hợp lệ.';
                return;
            }
            $incident = $model->find($id);
            if (!$incident) {
                http_response_code(404);
                echo 'Không tìm thấy phản ánh.';
                return;
            }
            $content = __DIR__ . '/../views/incidents/detail.php';
            $pageTitle = 'Chi tiết phản ánh';
        } else {
            // Danh sách xử lý chỉ lấy các phản ánh đang chờ theo trang được yêu cầu.
            $pageInput = $_GET['page'] ?? '1';
            $page = is_scalar($pageInput)
                ? filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            if ($page === false) {
                http_response_code(400);
                echo 'Trang không hợp lệ.';
                return;
            }
            $listing = $model->pending((int) $page);
            $message = $_SESSION['incident_staff_notice'] ?? '';
            unset($_SESSION['incident_staff_notice']);
            $content = __DIR__ . '/../views/incidents/list.php';
            $pageTitle = 'Xử lý phản ánh sự cố';
        }

        $displayName = (new StaffDashboard($this->db))->nameForAccount($accountId)
            ?? $_SESSION['user']['TenDangNhap'];
        require __DIR__ . '/../views/incidents/layout.php';
    }

    private function id(mixed $value): ?string
    {
        // Chỉ chấp nhận mã nghiệp vụ dạng chữ/số để tránh truyền chuỗi tùy ý vào Model.
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]{1,10}$/D', trim($value)) === 1
            ? trim($value)
            : null;
    }
}
