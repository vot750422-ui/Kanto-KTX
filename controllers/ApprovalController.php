<?php
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
require_once __DIR__ . '/../config/app.php';

class ApprovalController
{
    public function __construct(private PDO $db) {}

    public function handle(string $action): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $write = in_array($action, ['approval-approve', 'approval-reject'], true);
        if ($method !== ($write ? 'POST' : 'GET')) {
            http_response_code(405);
            header('Allow: ' . ($write ? 'POST' : 'GET'));
            return;
        }
        header('Cache-Control: no-store');
        $_SESSION['approval_token'] ??= bin2hex(random_bytes(32));
        $model = new Approval($this->db);
        $idInput = $write ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
        $id = is_string($idInput) ? trim($idInput) : '';
        $validId = preg_match('/^[A-Za-z0-9_-]{1,10}$/D', $id) === 1;
        $error = '';
        $reason = is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '';
        if ($write) {
            $token = $_POST['csrf_token'] ?? null;
            if (!is_string($token) || !hash_equals($_SESSION['approval_token'], $token)) {
                http_response_code(403);
                echo 'Phiên xác nhận không hợp lệ. Vui lòng tải lại trang.';
                return;
            }
            if (!$validId) {
                http_response_code(400);
                echo 'Mã đơn đăng ký không hợp lệ.';
                return;
            }
            try {
                if ($action === 'approval-reject') {
                    $model->reject($id, $reason);
                    $message = 'Từ chối đơn đăng ký thành công.';
                } else {
                    $result = $model->approve($id);
                    $message = 'Duyệt đơn đăng ký thành công. Tên đăng nhập: ' . $result['username']
                        . '. Mã hóa đơn tiền phòng: ' . $result['invoice'];
                }
                $_SESSION['approval_success'] = $message;
                $_SESSION['approval_token'] = bin2hex(random_bytes(32));
                header('Location: ' . appUrl('index.php?action=approval'), true, 303);
                return;
            } catch (DomainException $exception) {
                http_response_code(409);
                $error = $exception->getMessage();
            } catch (Throwable $exception) {
                http_response_code(500);
                error_log('UC03 xét duyệt thất bại (' . get_class($exception) . ').');
                $error = 'Chưa thể xử lý đơn đăng ký. Vui lòng thử lại sau.';
            }
        }
        if ($action === 'approval') {
            $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
            $listing = $model->pending($page);
            $success = $_SESSION['approval_success'] ?? '';
            unset($_SESSION['approval_success']);
            $application = null;
            $content = __DIR__ . '/../views/approval/list.php';
        } else {
            $application = $validId ? $model->find($id) : null;
            if (!$application) {
                http_response_code(404);
                echo 'Không tìm thấy đơn đăng ký.';
                return;
            }
            $proof = Approval::proof($application['FileMinhChung']);
            if ($action === 'approval-proof') {
                if ($proof['status'] !== 'available') {
                    http_response_code(404);
                    echo 'Ảnh minh chứng không có hoặc tệp không còn.';
                    return;
                }
                header('Content-Type: ' . $proof['mime']);
                header('X-Content-Type-Options: nosniff');
                header('Content-Disposition: inline; filename="minh-chung.' . ($proof['mime'] === 'image/png' ? 'png' : 'jpg') . '"');
                $file = @fopen($proof['path'], 'rb');
                if ($file === false) {
                    http_response_code(404);
                    return;
                }
                fpassthru($file);
                fclose($file);
                return;
            }
            $billing = null;
            $billingError = '';
            if ($application['TrangThai'] === 'Chờ duyệt') {
                try {
                    $billing = $model->billingContext();
                } catch (DomainException $exception) {
                    $billingError = $exception->getMessage();
                }
            }
            $content = __DIR__ . '/../views/approval/detail.php';
        }
        $displayName = (new StaffDashboard($this->db))->nameForAccount((int) $_SESSION['user']['MaTK'])
            ?? $_SESSION['user']['TenDangNhap'];
        require __DIR__ . '/../views/approval/layout.php';
    }
}
