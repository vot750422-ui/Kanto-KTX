<?php
require_once __DIR__ . '/../middleware/Role.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../services/StudentPaymentIntent.php';
require_once __DIR__ . '/../services/PaymentLink.php';

class StudentPaymentController
{
    public function handle(string $action): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $write = in_array($action, ['student-payment-confirm', 'student-qr-create'], true);
        $invoice = ($write ? $_POST : $_GET)['invoice'] ?? '';
        $validInvoice = is_string($invoice) && preg_match('/^[A-Za-z0-9_-]{1,12}$/D', $invoice);
        requireRole('Sinh viên');
        if ($method !== ($write ? 'POST' : 'GET')) {
            header('Allow: ' . ($write ? 'POST' : 'GET'));
            http_response_code(405);
            echo 'Phương thức không được hỗ trợ.';
            return;
        }
        require __DIR__ . '/../config/db.php';
        $model = new Payment($pdo);
        $accountId = (int)$_SESSION['user']['MaTK'];
        $screen = $action;
        $error = '';
        $success = $_SESSION['student_payment_success'] ?? '';
        unset($_SESSION['student_payment_success']);
        $profile = null;
        $bill = null;
        $listing = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
        $status = $_GET['status'] ?? 'unpaid';
        $intent = null;
        $checkoutUrl = '';
        $checkoutOrigin = '';
        $qrState = ['active' => false, 'token' => null, 'expires' => null];
        try {
            $profile = $model->studentForAccount($accountId);
            if ($action === 'student-payments') {
                $pageInput = $_GET['page'] ?? '1';
                $page = is_scalar($pageInput) ? filter_var($pageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
                if (!is_string($status) || !in_array($status, ['unpaid', 'paid', 'all'], true) || $page === false) {
                    http_response_code(400);
                    $status = 'unpaid';
                    throw new DomainException('Bộ lọc hóa đơn không hợp lệ.');
                }
                if ($profile) {
                    $listing = $model->listing($profile['MSSV'], ['unpaid' => 'Chưa thu', 'paid' => 'Đã thu', 'all' => ''][$status], (int)$page);
                }
            } else {
                if (!$validInvoice) {
                    http_response_code(400);
                    throw new DomainException('Mã hóa đơn không hợp lệ.');
                }
                $bill = $model->studentInvoice($invoice, $accountId);
                if (!$bill) {
                    http_response_code(404);
                    throw new DomainException('Hóa đơn không tồn tại hoặc không thuộc về bạn.');
                }
                if ($write) {
                    if ($action === 'student-qr-create') {
                        $saved = $_SESSION['student_qr_tokens'][$invoice] ?? null;
                        $known = is_array($saved) && ($saved['account'] ?? null) === $accountId ? ($saved['token'] ?? null) : null;
                        $token = (new PaymentLink($pdo))->issue($invoice, $accountId, $known);
                        if ($token !== null) {
                            $_SESSION['student_qr_tokens'][$invoice] = ['account' => $accountId, 'token' => $token];
                        }
                    } else {
                        $amount = StudentPaymentIntent::amount($invoice, $accountId, $_POST['transaction_token'] ?? null);
                        $result = $model->confirmForStudent($invoice, $accountId, $amount);
                        StudentPaymentIntent::forget($invoice);
                        $_SESSION['student_payment_success'] = 'Thanh toán mô phỏng thành công. Hóa đơn ' . $invoice . ' đã được ghi nhận Đã thu.'
                            . ($result['contract'] ? ' Hợp đồng lưu trú đã được tạo.' : '');
                    }
                    header('Location: ' . appUrl('index.php?action=student-invoice&invoice=' . rawurlencode($invoice)), true, 303);
                    return;
                }
                if ($action === 'student-payment' && $bill['TrangThai'] === 'Chưa thu') {
                    $intent = StudentPaymentIntent::issue($invoice, $accountId, (string)$bill['TongTien']);
                }
                if ($action === 'student-invoice' && $bill['TrangThai'] === 'Chưa thu') {
                    $saved = $_SESSION['student_qr_tokens'][$invoice] ?? null;
                    $known = is_array($saved) && ($saved['account'] ?? null) === $accountId ? ($saved['token'] ?? null) : null;
                    $qrState = (new PaymentLink($pdo))->current($invoice, $accountId, $known);
                    if ($qrState['token'] !== null) {
                        // Fragment is never sent in the initial HTTP URL or Referer/access log.
                        $checkoutOrigin = paymentPublicOrigin();
                        $checkoutUrl = $checkoutOrigin . appUrl('index.php?action=payment-checkout') . '#' . $qrState['token'];
                    }
                }
            }
        } catch (DomainException $exception) {
            if (http_response_code() === false || http_response_code() === 200) { http_response_code(409); }
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            http_response_code(500);
            $error = 'Chưa thể xử lý hóa đơn. Vui lòng thử lại sau.';
            error_log('UC17 thất bại (' . get_class($exception) . ').');
        }
        require __DIR__ . '/../views/student/payments.php';
    }
}
