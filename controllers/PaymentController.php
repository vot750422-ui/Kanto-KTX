<?php
require_once __DIR__ . '/../middleware/Role.php';
require_once __DIR__ . '/../models/Payment.php';

// UC07 cho nhân viên kế toán tra cứu và xác nhận hóa đơn tiền phòng đầu tiên.
class PaymentController
{
    public function handle(string $action): void
    {
        requireRole('Nhân viên kế toán');
        $write = $action === 'payment-confirm';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== ($write ? 'POST' : 'GET')) {
            header('Allow: ' . ($write ? 'POST' : 'GET')); http_response_code(405); return;
        }
        $error = '';
        require __DIR__ . '/../config/db.php';
        $model = new Payment($pdo);
        if ($write) {
            $invoice = $_POST['invoice'] ?? '';
            if (!is_string($invoice) || !preg_match('/^[A-Za-z0-9_-]{1,12}$/D', $invoice)) {
                http_response_code(400); echo 'Vui lòng chọn hóa đơn cần xác nhận thanh toán.'; return;
            }
            $studentForUtility = $_POST['student'] ?? null;
            if ($studentForUtility !== null && (!is_string($studentForUtility) || preg_match('/^[A-Za-z0-9_-]{1,15}$/D', $studentForUtility) !== 1)) {
                http_response_code(400); echo 'MSSV thanh toán không hợp lệ.'; return;
            }
            try {
                $result = $model->confirm($invoice, $studentForUtility);
                // Hiển thị kết quả theo nghiệp vụ: chỉ hóa đơn phòng đầu tiên mới tạo hợp đồng.
                if ($result['contract'] !== null) {
                    $_SESSION['payment_success'] = 'Xác nhận thành công. Hợp đồng: ' . $result['contract'] . '. Tài khoản sinh viên: ' . $result['username'] . ' (đã cấp khi duyệt đơn).';
                } else {
                    $_SESSION['payment_success'] = 'Xác nhận thành công. Hóa đơn đã được ghi nhận Đã thu.';
                }
                header('Location: ' . appUrl('index.php?action=payments&status=paid'), true, 303); return;
            } catch (DomainException $exception) {
                http_response_code(409); $error = $exception->getMessage();
            } catch (Throwable $exception) {
                http_response_code(500); $error = 'Chưa thể xác nhận thanh toán. Vui lòng thử lại sau.';
                error_log('UC07 thanh toán thất bại (' . get_class($exception) . ').');
            }
        }
        $student = $_GET['student'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $filters = [];
        foreach (['invoice'=>12,'name'=>100,'room'=>20,'type'=>20,'period'=>7] as $key=>$length) {
            $value = $_GET[$key] ?? '';
            if (!is_string($value) || mb_strlen($value, 'UTF-8') > $length || preg_match('/[\x00-\x1f]/', $value)) {
                http_response_code(400); echo 'Tiêu chí tra cứu không hợp lệ.'; return;
            }
            $filters[$key] = trim($value);
        }
        if (!in_array($filters['type'], ['', 'Tiền phòng', 'Điện nước'], true)
            || ($filters['period'] !== '' && !preg_match('/^[1-9][0-9]{3}-(0[1-9]|1[0-2])$/D', $filters['period']))) {
            http_response_code(400); echo 'Tiêu chí tra cứu không hợp lệ.'; return;
        }
        $pageInput = $_GET['page'] ?? '1';
        $page = is_scalar($pageInput) ? filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
        if (!is_string($student) || ($student !== '' && !preg_match('/^[A-Za-z0-9_-]{1,15}$/D', $student)) || !is_string($status) || !in_array($status, ['unpaid','paid','all'], true) || $page === false) {
            http_response_code(400); echo 'Tiêu chí tra cứu không hợp lệ.'; return;
        }
        try {
            $listing = $model->listing($student, ['unpaid'=>'Chưa thu','paid'=>'Đã thu','all'=>''][$status], (int)$page, $filters);
        } catch (Throwable $exception) {
            http_response_code(500); echo 'Không thể tải danh sách hóa đơn. Vui lòng thử lại sau.'; return;
        }
        $success = $_SESSION['payment_success'] ?? '';
        unset($_SESSION['payment_success']);
        require_once __DIR__ . '/../models/StaffDashboard.php';
        $displayName = (new StaffDashboard($pdo))->nameForAccount((int) $_SESSION['user']['MaTK']) ?? $_SESSION['user']['TenDangNhap'];
        require __DIR__ . '/../views/payments/list.php';
    }
}
