<?php
require_once __DIR__ . '/middleware/Role.php';
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : '';

// Định tuyến request đến đúng controller theo nghiệp vụ mà người dùng yêu cầu.
if ($action === 'utility-meters') {
    requireRole('Nhân viên quản lý KTX'); require __DIR__ . '/config/db.php'; require_once __DIR__ . '/controllers/UtilityMeterController.php';
    (new UtilityMeterController($pdo))->handle(); exit;
}
if ($action === 'statistics') {
    requireRole('Quản lý KTX'); require __DIR__ . '/config/db.php'; require_once __DIR__ . '/controllers/StatisticsController.php';
    (new StatisticsController($pdo))->show(); exit;
}
if (in_array($action, ['notifications','notification-detail'], true)) {
    requireRole('Sinh viên'); require __DIR__ . '/config/db.php'; require_once __DIR__ . '/controllers/NotificationController.php';
    (new NotificationController($pdo))->handle($action); exit;
}
if ($action === 'incident-report') {
    requireRole('Sinh viên'); require __DIR__ . '/config/db.php'; require_once __DIR__ . '/controllers/IncidentController.php';
    (new IncidentController($pdo))->student(); exit;
}
if (in_array($action, ['incidents','incident-detail','incident-resolve'], true)) {
    requireRole('Nhân viên quản lý KTX'); require __DIR__ . '/config/db.php'; require_once __DIR__ . '/controllers/IncidentController.php';
    (new IncidentController($pdo))->staff($action); exit;
}
if (in_array($action, ['students', 'student-detail', 'student-update'], true)) {
    requireRole('Nhân viên quản lý KTX');
    require __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/StudentManagementController.php';
    (new StudentManagementController($pdo))->handle($action);
    exit;
}
if (in_array($action, ['contracts', 'contract-detail', 'contract-terminate'], true)) {
    requireRole('Nhân viên quản lý KTX');
    require __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/ContractController.php';
    (new ContractController($pdo))->handle($action);
    exit;
}
if (in_array($action, ['payment-checkout', 'payment-checkout-confirm'], true)) {
    require_once __DIR__ . '/controllers/PaymentCheckoutController.php';
    (new PaymentCheckoutController())->handle($action);
    exit;
}
if (in_array($action, ['student-payments', 'student-invoice', 'student-qr-create', 'student-payment', 'student-payment-confirm'], true)) {
    require_once __DIR__ . '/controllers/StudentPaymentController.php';
    (new StudentPaymentController())->handle($action);
    exit;
}
if (in_array($action, ['payments', 'payment-confirm'], true)) {
    require_once __DIR__ . '/controllers/PaymentController.php';
    (new PaymentController())->handle($action);
    exit;
}
if ($action === 'accommodation') {
    require_once __DIR__ . '/controllers/AccommodationController.php';
    (new AccommodationController())->show();
    exit;
}
if ($action === 'guide') {
    require_once __DIR__ . '/controllers/GuideController.php';
    (new GuideController())->show();
    exit;
}
if ($action === 'chatbot-send') {
    require_once __DIR__ . '/controllers/ChatbotController.php';
    (new ChatbotController())->send();
    exit;
}
if ($action === 'rooms') {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/RoomController.php';
    (new RoomController($pdo))->show();
    exit;
}
if (in_array($action, ['approval', 'approval-detail', 'approval-proof', 'approval-approve', 'approval-reject', 'approval-email-retry'], true)) {
    requireRole('Nhân viên quản lý KTX');
    require __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/ApprovalController.php';
    (new ApprovalController($pdo))->handle($action);
    exit;
}
if ($action === 'register') {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/controllers/RegistrationController.php';
    (new RegistrationController($pdo))->show();
    exit;
}
if ($action === 'logout') {
    require __DIR__ . '/controllers/logout.php';
    exit;
}
if ($action === 'login') {
    // Xử lý form trước khi chuyển hướng theo phiên đăng nhập có sẵn.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/config/db.php';
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController($pdo);
        $controller->login();
    }
    if (isset($_SESSION['user'])) {
        $user = requireAuth();
        $dashboard = dashboardForRole($user['VaiTro']);
        if ($dashboard !== null) {
            header('Location: ' . $dashboard);
            exit;
        }
        unset($_SESSION['user']);
    }
    require __DIR__ . '/views/Auth/login.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ — Kanto KTX</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="home-page">
    <div class="home-shell">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="home-hero">
            <div class="home-hero-content">
                <p class="home-eyebrow"><span aria-hidden="true"></span> KANTO · KÝ TÚC XÁ SINH VIÊN</p>
                <h1>Nơi khởi đầu<br><span>hành trình đại học</span><br>của bạn</h1>
                <div class="home-hero-actions">
                    <a class="home-register" href="<?= htmlspecialchars(appUrl('index.php?action=register'), ENT_QUOTES, 'UTF-8') ?>" style="text-decoration: none">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                        Đăng ký lưu trú
                        <span aria-hidden="true">→</span>
                    </a>
                    <a class="home-secondary" href="<?= htmlspecialchars(appUrl('index.php?action=login'), ENT_QUOTES, 'UTF-8') ?>">Đăng nhập tài khoản <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <?php require __DIR__ . '/includes/chatbot.php'; ?>
        </main>
    </div>
</body>
</html>
