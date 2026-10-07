<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
// Bảng TEMPORARY che bảng thật trong kết nối này; tự mất khi đóng kết nối.
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/RegistrationController.php';
session_set_save_handler(fn($p, $n) => true, fn() => true, fn($id) => '', fn($id, $data) => true, fn($id) => true, fn($ttl) => 0);
session_start();
ob_start();
$passed = 0;
function verify(bool $condition, string $label): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($label);
    }
    $passed++;
    echo "[PASS] $label\n";
}
function page(RegistrationController $controller, array $post = []): string
{
    $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
    $_POST = $post;
    ob_start();
    $controller->show();
    return ob_get_clean();
}
function rejected(callable $action, int $code): bool
{
    try {
        $action();
        return false;
    } catch (DomainException $error) {
        return $error->getCode() === $code;
    }
}
try {
    foreach (['toanha', 'phong', 'dienuutien', 'hopdong', 'dondangky'] as $table) {
        $definition = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        // Không sao chép khóa ngoại: bảng fixture chỉ tồn tại trong kết nối test.
        $definition = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $definition);
        $definition = preg_replace('/,\s*\n\)/', "\n)", $definition);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $definition));
    }
    $pdo->exec("INSERT INTO toanha (MaToa, TenToa) VALUES ('TA', 'Dãy A')");
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/update-priorities.sql'));
    verify(array_column((new Registration($pdo))->priorities(), 'MaUuTien') === ['UT00', 'UT01', 'UT02', 'UT03'], 'Dropdown gồm Không có và các diện ưu tiên theo thứ tự 1, 2, 3');
    $pdo->exec("INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua) VALUES ('P1','TA','101',1,'Nam',2),('P2','TA','102',1,'Nam',2),('P3','TA','103',1,'Nữ',2)");
    $pdo->exec("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai) VALUES ('H1','OLD1','P1','2026-01-01','2026-12-31','Còn hạn'),('H2','OLD2','P1','2025-01-01','2025-12-31','Hết hạn')");
    $data = ['HoTen' => 'Nguyễn Văn A', 'NgaySinh' => '2006-09-12', 'CCCD' => '079204000001', 'GioiTinh' => 'Nam', 'QueQuan' => 'Đồng Tháp', 'SDT' => '0912345678', 'MSSV' => 'KTPM2411001', 'Lop' => 'DHKTPM24A', 'NienKhoa' => '2024-2028', 'MaUuTien' => 'UT00', 'FileMinhChung' => null];
    [, $errors] = RegistrationController::validate($data);
    verify(!$errors, 'Thông tin hợp lệ được chấp nhận');
    [, $errors] = RegistrationController::validate(array_replace($data, ['MSSV' => [], 'NgaySinh' => '2006-02-30', 'SDT' => 'abc', 'CCCD' => '12', 'GioiTinh' => 'khác']));
    verify(count($errors) === 5, 'Từ chối dữ liệu dạng mảng, ngày sai và định dạng sai');
    $model = new Registration($pdo);
    $rooms = $model->rooms('Nam');
    verify(count($rooms) === 2 && (int) $rooms[0]['ConCho'] === 1, 'Lọc giới tính và chỉ trừ hợp đồng Còn hạn');
    verify(rejected(fn() => $model->submit($data, 'P3'), 2), 'Không gửi đơn vào phòng khác giới tính');
    $id = $model->submit($data, 'P1');
    verify(strlen($id) === 10 && $model->hasPending($data['MSSV']), 'Gửi đơn tạo mã và trạng thái Chờ duyệt');
    verify(count($model->rooms('Nam')) === 1, 'Đơn Chờ duyệt giữ đúng một chỗ, phòng đầy bị loại');
    verify(rejected(fn() => $model->submit($data, 'P2'), 1), 'Không tạo đơn trùng MSSV tại phòng khác');
    $next = array_replace($data, ['MSSV' => 'NEXT']);
    verify(rejected(fn() => $model->submit($next, 'P1'), 2), 'Phòng vừa hết chỗ bị từ chối khi gửi');
    verify((int) $pdo->query('SELECT COUNT(*) FROM dondangky')->fetchColumn() === 1 && !$pdo->inTransaction(), 'Thao tác lỗi rollback, không tạo đơn và không giữ thêm chỗ');
    $pdo->exec("UPDATE dondangky SET TrangThai = 'Từ chối' WHERE MaPhong = 'P1'");
    verify(count($model->rooms('Nam')) === 2, 'Đơn bị từ chối giải phóng chỗ khả dụng');
    $controller = new RegistrationController($pdo);
    verify(str_contains(page($controller), 'Thông tin đăng ký'), 'Khách mở được bước nhập thông tin');
    $token = $_SESSION['registration_token'];
    $html = page($controller, ['operation' => 'information', 'csrf_token' => 'invalid'] + $next);
    verify(http_response_code() === 403 && !isset($_SESSION['registration_draft']), 'CSRF sai không thay đổi hồ sơ');
    http_response_code(200);
    $_FILES = ['proof' => ['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__, 'size' => 100, 'name' => 'fake.jpg']];
    $html = page($controller, ['operation' => 'information', 'csrf_token' => $token] + $next);
    verify(str_contains($html, 'Chọn ảnh JPG/PNG') && $_SESSION['registration_draft']['step'] === 1, 'Từ chối ảnh giả, giữ tại bước nhập thông tin');
    $_FILES = [];
    page($controller, ['operation' => 'information', 'csrf_token' => $token] + $next);
    verify($_SESSION['registration_draft']['step'] === 2, 'Thông tin hợp lệ chuyển sang chọn phòng');
    page($controller, ['operation' => 'room', 'csrf_token' => $token, 'MaPhong' => 'P2']);
    verify($_SESSION['registration_draft']['step'] === 3 && $_SESSION['registration_draft']['room'] === 'P2', 'Chọn phòng chuyển sang xác nhận');
    page($controller, ['operation' => 'back', 'csrf_token' => $token]);
    verify($_SESSION['registration_draft']['step'] === 2 && $_SESSION['registration_draft']['data']['MSSV'] === 'NEXT', 'Quay lại giữ thông tin đã nhập');
    page($controller, ['operation' => 'room', 'csrf_token' => $token, 'MaPhong' => 'P2']);
    ob_start(); // Tránh output trước header chuyển hướng trong CLI.
    page($controller, ['operation' => 'submit', 'csrf_token' => $token]);
    ob_end_clean();
    verify(isset($_SESSION['registration_success']) && !isset($_SESSION['registration_draft']), 'Gửi thành công xóa hồ sơ nháp, lưu mã đơn');
    $before = (int) $pdo->query('SELECT COUNT(*) FROM dondangky')->fetchColumn();
    page($controller, ['operation' => 'submit', 'csrf_token' => $token]);
    verify((int) $pdo->query('SELECT COUNT(*) FROM dondangky')->fetchColumn() === $before, 'Gửi lại bằng token cũ không tạo đơn thêm');
    $token = $_SESSION['registration_token'];
    page($controller, ['operation' => 'information', 'csrf_token' => $token] + array_replace($next, ['MSSV' => 'LAST']));
    page($controller, ['operation' => 'room', 'csrf_token' => $token, 'MaPhong' => 'P2']);
    $model->submit(array_replace($data, ['MSSV' => 'COMPETING']), 'P2');
    $html = page($controller, ['operation' => 'submit', 'csrf_token' => $token]);
    verify($_SESSION['registration_draft']['step'] === 2 && str_contains($html, 'vừa hết chỗ') && !$model->hasPending('LAST'), 'Phòng hết chỗ sau bước chọn: quay lại bước 2 và không tạo đơn');
    echo "\n$passed kiểm thử UC02 đạt.\n";
    ob_end_flush();
} catch (Throwable $error) {
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
