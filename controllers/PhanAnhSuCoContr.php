<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/PhanAnhSuCo.php';

class PhanAnhSuCoContr
{
    // Các loại sự cố (enum): Điện, Nước, Nội thất, Khác.
    private array $loaiSuCo = [
        'Điện',
        'Nước',
        'Nội thất',
        'Khác',
    ];

    public function __construct(private PDO $db) {}

    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Chỉ cho phép truy cập bằng GET hoặc POST.
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Phương thức không được hỗ trợ.');
        }

        // Bắt buộc đăng nhập: requireAuth() tự chuyển về trang đăng nhập
        // nếu chưa đăng nhập hoặc tài khoản không còn "Hoạt động".
        $user = requireAuth();

        // Dự phòng, thường không chạy tới vì requireAuth() đã exit.
        if (!is_array($user) || empty($user['MaTK'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để gửi phản ánh sự cố.';
            header('Location: /Kanto-KTX/index.php?action=login', true, 302);
            exit;
        }

        $model = new PhanAnhSuCo($this->db);

        // Dữ liệu dùng để hiển thị trên View.
        $sinhVien    = $model->layThongTinSinhVien((int) $user['MaTK']);
        $danhSach    = [];
        $loi         = '';
        $loiLoaiSuCo = '';
        $loiMoTa     = '';
        $thongBao    = '';
        $loaiDaChon  = '';
        $moTaNhap    = '';

        // Tạo CSRF token để bảo vệ biểu mẫu.
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $csrfToken = $_SESSION['csrf_token'];

        if (!$sinhVien) {
            http_response_code(404);
            $loi = 'Không tìm thấy hồ sơ sinh viên.';
        } else {
            if ($method === 'POST') {
                $token = $_POST['csrf_token'] ?? '';
                $loai  = $_POST['LoaiSuCo'] ?? '';
                $moTa  = $_POST['MoTa'] ?? '';

                $tokenHopLe = is_string($token)
                    && hash_equals($csrfToken, $token);

                $loaiDaChon = is_string($loai) ? $loai : '';
                $moTaNhap   = is_string($moTa) ? $moTa : '';

                if (!$tokenHopLe) {
                    http_response_code(403);
                    $loi = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
                } elseif (empty($sinhVien['MaPhong'])) {
                    // BR-UC08-01: phải có hợp đồng còn hạn và xác định được phòng.
                    $loi = 'Bạn chưa có hợp đồng lưu trú còn hiệu lực nên không thể gửi phản ánh.';
                } else {
                    // Kiểm tra loại sự cố.
                    if (!in_array($loaiDaChon, $this->loaiSuCo, true)) {
                        $loiLoaiSuCo = 'Vui lòng chọn loại sự cố.';
                    }

                    // Kiểm tra mô tả.
                    $moTaDaTrim = trim($moTaNhap);
                    $doDaiMoTa  = mb_strlen($moTaDaTrim, 'UTF-8');

                    if ($doDaiMoTa === 0) {
                        $loiMoTa = 'Vui lòng nhập mô tả sự cố.';
                    } elseif ($doDaiMoTa < 10) {
                        $loiMoTa = 'Mô tả phải có ít nhất 10 ký tự.';
                    } elseif ($doDaiMoTa > 2000) {
                        $loiMoTa = 'Mô tả không được vượt quá 2.000 ký tự.';
                    }

                    // Chỉ lưu khi cả hai trường đều hợp lệ.
                    if ($loiLoaiSuCo === '' && $loiMoTa === '') {
                        try {
                            $maPhanAnh = $model->taoPhanAnh(
                                $sinhVien['MSSV'],
                                $sinhVien['MaPhong'],
                                $loaiDaChon,
                                $moTaDaTrim
                            );

                            $_SESSION['thongBaoPhanAnh'] =
                                'Gửi phản ánh thành công. Mã phản ánh: '
                                . $maPhanAnh;

                            // Chuyển hướng để tránh gửi lại biểu mẫu.
                           header('Location: ' . appUrl('index.php?action=phan-anh'), true, 303);
                            exit;
                        } catch (Throwable $e) {
                            error_log((string) $e);
                            http_response_code(500);
                            $loi = 'Chưa thể gửi phản ánh. Vui lòng thử lại sau.';
                        }
                    }
                }
            }

            // Lấy lịch sử sau khi xử lý POST.
            $danhSach = $model->layDanhSach($sinhVien['MSSV']);
        }

        // Hiển thị thông báo sau khi chuyển hướng thành công.
        if (isset($_SESSION['thongBaoPhanAnh'])) {
            $thongBao = $_SESSION['thongBaoPhanAnh'];
            unset($_SESSION['thongBaoPhanAnh']);
        }

        require __DIR__ . '/../views/student/PhanAnh.php';
    }
}