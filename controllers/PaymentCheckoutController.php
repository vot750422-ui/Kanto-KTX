<?php
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../services/PaymentLink.php';

final class PaymentCheckoutController
{
    public function handle(string $action): void
    {
        // Anonymous session only protects the form; no student login is required.
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        header('X-Frame-Options: DENY');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $confirm = $action === 'payment-checkout-confirm';
        $allowed = $confirm ? ['POST'] : ['GET','POST'];
        if (!in_array($method, $allowed, true)) {
            header('Allow: ' . implode(', ', $allowed));
            http_response_code(405);
            echo 'Phương thức không được hỗ trợ.';
            return;
        }
        $_SESSION['checkout_csrf'] ??= bin2hex(random_bytes(32));
        $csrf = $_SESSION['checkout_csrf'];
        $bill = null;
        $receipt = null;
        $token = '';
        $error = '';
        try {
            if ($method === 'GET' && isset($_GET['receipt'])) {
                $id = $_GET['receipt'];
                $receipt = is_string($id) ? ($_SESSION['checkout_receipts'][$id] ?? null) : null;
                if (!$receipt) {
                    http_response_code(404);
                    throw new DomainException('Không tìm thấy kết quả thanh toán trên thiết bị này.');
                }
            } elseif ($method === 'POST') {
                if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals($csrf, $_POST['csrf_token'])) {
                    http_response_code(403);
                    throw new DomainException('Phiên xác nhận không hợp lệ. Vui lòng mở lại liên kết QR.');
                }
                $token = $_POST['link_token'] ?? null;
                require __DIR__ . '/../config/db.php';
                $bill = (new PaymentLink($pdo))->resolve($token);
                if ($confirm) {
                    (new Payment($pdo))->confirmLink($token);
                    $id = bin2hex(random_bytes(12));
                    if (count($_SESSION['checkout_receipts'] ?? []) >= 5) { array_shift($_SESSION['checkout_receipts']); }
                    $_SESSION['checkout_receipts'][$id] = [
                        'MaHoaDon'=>$bill['MaHoaDon'], 'TongTien'=>$bill['TongTien'],
                    ];
                    header('Location: ' . appUrl('index.php?action=payment-checkout&receipt=' . $id), true, 303);
                    return;
                }
            }
        } catch (DomainException $exception) {
            if (http_response_code() === false || http_response_code() === 200) { http_response_code(409); }
            $error = $exception->getMessage();
            $bill = null;
            $token = '';
        } catch (Throwable $exception) {
            http_response_code(500);
            $error = 'Chưa thể xử lý thanh toán. Vui lòng mở lại QR và thử lại.';
            $bill = null;
            $token = '';
            error_log('UC17 QR thất bại (' . get_class($exception) . ').');
        }
        require __DIR__ . '/../views/payments/checkout.php';
    }
}
