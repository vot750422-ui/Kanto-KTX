<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Registration.php';

class RegistrationController
{
    public function __construct(private PDO $db) {}

    public static function validate(array $input): array
    {
        $limits = ['HoTen' => 100, 'NgaySinh' => 10, 'CCCD' => 20, 'GioiTinh' => 3, 'QueQuan' => 150, 'SDT' => 15, 'MSSV' => 15, 'Lop' => 30, 'NienKhoa' => 20, 'MaUuTien' => 10];
        $data = $errors = [];
        foreach ($limits as $field => $limit) {
            $data[$field] = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
            if ($data[$field] === '' || mb_strlen($data[$field]) > $limit) {
                $errors[$field] = 'Vui lòng nhập đầy đủ thông tin, đúng độ dài cho phép.';
            }
        }
        foreach (['MSSV' => '/^[A-Za-z0-9_-]{1,15}$/D', 'CCCD' => '/^[0-9]{12}$/D', 'SDT' => '/^0[0-9]{9}$/D', 'NienKhoa' => '/^[0-9]{4}\s*[-–]\s*[0-9]{4}$/uD'] as $field => $pattern) {
            if (!preg_match($pattern, $data[$field])) {
                $errors[$field] = match ($field) {
                    'CCCD' => 'CCCD phải gồm 12 chữ số.',
                    'SDT' => 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.',
                    'NienKhoa' => 'Nhập niên khóa dạng 2024-2028.',
                    default => 'MSSV chỉ gồm chữ, số, dấu gạch ngang hoặc gạch dưới.',
                };
            }
        }
        if (!in_array($data['GioiTinh'], ['Nam', 'Nữ'], true)) {
            $errors['GioiTinh'] = 'Vui lòng chọn giới tính hợp lệ.';
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['NgaySinh']);
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh'));
        if (!$date || $date->format('Y-m-d') !== $data['NgaySinh'] || $date >= $today) {
            $errors['NgaySinh'] = 'Ngày sinh phải hợp lệ và trước ngày hiện tại.';
        }
        return [$data, $errors];
    }

    public function show(): void
    {
        header('Cache-Control: no-store');
        $model = new Registration($this->db);
        $priorities = $model->priorities();
        $_SESSION['registration_token'] ??= bin2hex(random_bytes(32));
        $draft = $_SESSION['registration_draft'] ?? [];
        $data = $draft['data'] ?? [];
        $errors = [];
        $message = '';
        $success = $_SESSION['registration_success'] ?? null;
        unset($_SESSION['registration_success']);
        $step = (int) ($draft['step'] ?? 1);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!is_string($token) || !hash_equals($_SESSION['registration_token'], $token)) {
                http_response_code(403);
                $message = 'Phiên xác nhận không hợp lệ. Vui lòng thử lại.';
            } else {
                $operation = $_POST['operation'] ?? '';
                if ($operation === 'information') {
                    [$data, $errors] = self::validate($_POST);
                    if (!array_filter($priorities, fn($p) => $p['MaUuTien'] === $data['MaUuTien'])) {
                        $errors['MaUuTien'] = 'Vui lòng chọn diện ưu tiên hợp lệ.';
                    }
                    if (!$errors && $model->hasPending($data['MSSV'])) {
                        $errors['MSSV'] = 'MSSV đã có hồ sơ đang được xử lý.';
                    }
                    if (!$errors && ($conflict = $model->studentConflict($data['MSSV'], $data['CCCD']))) {
                        $errors[str_starts_with($conflict, 'CCCD') ? 'CCCD' : 'MSSV'] = $conflict;
                    }
                    $file = $_FILES['proof'] ?? null;
                    $proof = $draft['data']['FileMinhChung'] ?? null;
                    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $tmp = $file['tmp_name'] ?? '';
                        $valid = is_string($tmp) && is_uploaded_file($tmp);
                        $mime = $valid ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : '';
                        if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || !$valid || filesize($tmp) >= 5 * 1024 * 1024 || !in_array($mime, ['image/jpeg', 'image/png'], true) || !@getimagesize($tmp)) {
                            $errors['proof'] = 'Chọn ảnh JPG/PNG có dung lượng dưới 5 MB.';
                        } elseif (!$errors) {
                            $dir = __DIR__ . '/../storage/registration';
                            if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
                                throw new RuntimeException('Không thể lưu ảnh minh chứng.');
                            }
                            $name = bin2hex(random_bytes(24)) . ($mime === 'image/png' ? '.png' : '.jpg');
                            if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
                                throw new RuntimeException('Không thể lưu ảnh minh chứng.');
                            }
                            if ($proof) {
                                @unlink(__DIR__ . '/../' . $proof);
                            }
                            $proof = 'storage/registration/' . $name;
                        }
                    }
                    $data['FileMinhChung'] = $proof;
                    $step = $errors ? 1 : 2;
                    $draft = ['data' => $data, 'step' => $step];
                } elseif ($operation === 'back' && $draft) {
                    $step = max(1, $step - 1);
                } elseif ($operation === 'room' && $draft && $step === 2) {
                    $id = is_string($_POST['MaPhong'] ?? null) ? $_POST['MaPhong'] : '';
                    if (!array_filter($model->rooms($data['GioiTinh']), fn($r) => $r['MaPhong'] === $id)) {
                        $message = 'Vui lòng chọn phòng phù hợp còn chỗ.';
                    } else {
                        $draft['room'] = $id;
                        $step = 3;
                    }
                } elseif ($operation === 'submit' && $draft && $step === 3) {
                    [, $errors] = self::validate($data);
                    if ($errors) {
                        $step = 1;
                    } else {
                        try {
                            $id = $model->submit($data, $draft['room']);
                            unset($_SESSION['registration_draft']);
                            $_SESSION['registration_success'] = $id;
                            $_SESSION['registration_token'] = bin2hex(random_bytes(32));
                            header('Location: ' . appUrl('index.php?action=register'), true, 303);
                            return;
                        } catch (DomainException $error) {
                            $message = $error->getMessage();
                            $step = $error->getCode() === 2 ? 2 : 1;
                        } catch (Throwable $error) {
                            $message = 'Chưa thể gửi đăng ký. Vui lòng thử lại sau.';
                        }
                    }
                } else {
                    $message = 'Vui lòng thực hiện lần lượt các bước đăng ký.';
                }
                $draft['step'] = $step;
                $_SESSION['registration_draft'] = $draft;
            }
        }
        $rooms = $step > 1 ? $model->rooms($data['GioiTinh']) : [];
        $selected = null;
        foreach ($rooms as $room) {
            if ($room['MaPhong'] === ($draft['room'] ?? null)) {
                $selected = $room;
            }
        }
        if ($step === 3 && !$selected) {
            $step = 2;
            $_SESSION['registration_draft']['step'] = 2;
            $message = 'Phòng bạn chọn vừa hết chỗ. Vui lòng chọn phòng khác.';
        }
        require __DIR__ . '/../views/registration/form.php';
    }
}
