<?php
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/../models/StaffDashboard.php';
require_once __DIR__ . '/../config/app.php';

class ApprovalController
{
    public function __construct(private PDO $db, private ?ApprovalEmail $email = null)
    {
        $this->email ??= new ApprovalEmail($db);
    }

    public function handle(string $action): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $write = in_array($action, ['approval-approve', 'approval-reject', 'approval-email-retry'], true);
        if ($method !== ($write ? 'POST' : 'GET')) {
            http_response_code(405);
            header('Allow: ' . ($write ? 'POST' : 'GET'));
            return;
        }
        $model = new Approval($this->db);
        $idInput = $write ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null);
        $id = is_string($idInput) ? trim($idInput) : '';
        $validId = preg_match('/^[A-Za-z0-9_-]{1,10}$/D', $id) === 1;
        $error = '';
        $reason = is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '';
        if ($write) {
            if (!$validId) {
                http_response_code(400);
                echo 'Mã đơn đăng ký không hợp lệ.';
                return;
            }
            try {
                if ($action === 'approval-email-retry') {
                    if (!$this->email->find($id)) {
                        throw new DomainException('Đơn chưa có thông báo email.');
                    }
                    $message = 'Đã kiểm tra thông báo email của đơn ' . $id . '.';
                } elseif ($action === 'approval-reject') {
                    $model->reject($id, $reason);
                    $message = 'Từ chối đơn đăng ký thành công.';
                } else {
                    $result = $model->approve($id);
                    $message = 'Duyệt đơn đăng ký thành công. Tên đăng nhập: ' . $result['username']
                        . '. Mã hóa đơn tiền phòng: ' . $result['invoice'];
                }
                // Kết quả nghiệp vụ đã commit; lỗi gửi thư không được báo là lỗi xét duyệt.
                try {
                    $mailStatus = $this->email->deliver($id);
                } catch (Throwable $exception) {
                    error_log('UC03 xử lý thông báo thất bại (' . get_class($exception) . ').');
                    $mailStatus = 'Lỗi';
                }
                $_SESSION['approval_success'] = $message;
                $_SESSION['approval_email_notice'] = match ($mailStatus) {
                    'Đã gửi' => 'Máy chủ email đã tiếp nhận thông báo gửi đến sinh viên.',
                    'Không có email' => 'Hồ sơ cũ chưa có email hợp lệ. Vui lòng liên hệ sinh viên qua thông tin trong hồ sơ.',
                    'Đang gửi' => 'Thông báo đang được gửi trong một yêu cầu khác. Vui lòng tải lại trang để kiểm tra.',
                    default => 'Kết quả xét duyệt đã được lưu, nhưng email chưa gửi được. Kiểm tra cấu hình SMTP và chọn Gửi lại email trong chi tiết đơn.',
                };
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
            $emailNotice = $_SESSION['approval_email_notice'] ?? '';
            $outstandingEmails = $this->email->outstanding();
            unset($_SESSION['approval_success'], $_SESSION['approval_email_notice']);
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
            $emailNotification = $this->email->find($id);
            if ($action === 'approval-proof') {
                if ($proof['status'] !== 'available') {
                    http_response_code(404);
                    echo 'Ảnh minh chứng không có hoặc tệp không còn.';
                    return;
                }
                header('Content-Type: ' . $proof['mime']);
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
