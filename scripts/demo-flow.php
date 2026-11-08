<?php
/**
 * CLI Helper Script phục vụ buổi Demo tác vụ Timer (UC10.1, UC10.2, UC18)
 * và quy trình Mở khóa tài khoản khi nộp phí quá hạn (UC07).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only\n";
    exit(1);
}

require_once __DIR__ . '/../config/db.php';

$action = $argv[1] ?? '--help';
$sessionFile = __DIR__ . '/../storage/demo_session.json';

switch ($action) {
    case '--select-pair':
        selectPair($pdo, $sessionFile);
        break;
    case '--show-guide':
        showGuide($sessionFile);
        break;
    default:
        echo "Sử dụng: php scripts/demo-flow.php [--select-pair | --show-guide]\n";
        break;
}

function selectPair(PDO $pdo, string $sessionFile): void
{
    // Tìm học kỳ mới nhất (học kỳ vừa sinh hóa đơn)
    $stmt = $pdo->query("SELECT p.MaHocKy, hk.TenHocKy 
        FROM hoadontienphong p 
        JOIN hocky hk ON hk.MaHocKy = p.MaHocKy 
        JOIN hoadon h ON h.MaHoaDon = p.MaHoaDon 
        WHERE h.TrangThai = 'Chưa thu' 
        ORDER BY hk.NgayBatDau DESC LIMIT 1");
    $sem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sem) {
        echo "[!] Chưa có hóa đơn học kỳ mới. Vui lòng chạy Bước 1 trước.\n";
        return;
    }

    $maHocKy = $sem['MaHocKy'];
    $tenHocKy = $sem['TenHocKy'];

    // Chọn ngẫu nhiên 2 sinh viên có hóa đơn chưa thu trong kỳ này
    $stmt = $pdo->prepare("SELECT s.MSSV, s.HoTen, p.MaPhong, h.MaHoaDon, h.TongTien, t.TenDangNhap 
        FROM sinhvien s 
        JOIN hoadontienphong p ON p.MSSV = s.MSSV 
        JOIN hoadon h ON h.MaHoaDon = p.MaHoaDon 
        JOIN taikhoan t ON t.MaTK = s.MaTK 
        WHERE p.MaHocKy = ? AND h.TrangThai = 'Chưa thu' AND t.TrangThai = 'Hoạt động' 
        ORDER BY RAND() LIMIT 2");
    $stmt->execute([$maHocKy]);
    $pairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($pairs) < 2) {
        echo "[!] Không đủ 2 sinh viên hợp lệ để thiết lập demo.\n";
        return;
    }

    $stPaid = $pairs[0];
    $stUnpaid = $pairs[1];

    // Đánh dấu Sinh viên 1 là ĐÃ NỘP TIỀN
    $updateStmt = $pdo->prepare("UPDATE hoadon SET TrangThai = 'Đã thu', ThoiGianThanhToan = NOW() WHERE MaHoaDon = ?");
    $updateStmt->execute([$stPaid['MaHoaDon']]);

    // Lưu vào file session
    $demoData = [
        'semester' => ['code' => $maHocKy, 'name' => $tenHocKy],
        'paid' => $stPaid,
        'unpaid' => $stUnpaid,
        'created_at' => date('Y-m-d H:i:s')
    ];
    @mkdir(dirname($sessionFile), 0777, true);
    file_put_contents($sessionFile, json_encode($demoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "\n";
    echo "================================================================================\n";
    echo "   [HỆ THỐNG ĐÃ CHỌN NGẪU NHIÊN 2 SINH VIÊN ĐỂ BẠN DEMO VỚI GIẢNG VIÊN]\n";
    echo "================================================================================\n\n";

    echo " 1. SINH VIÊN 1 (ĐÃ NỘP TIỀN PHÒNG):\n";
    echo "    - MSSV        : " . $stPaid['MSSV'] . "\n";
    echo "    - Họ và tên   : " . $stPaid['HoTen'] . "\n";
    echo "    - Phòng       : " . $stPaid['MaPhong'] . "\n";
    echo "    - Hóa đơn     : " . $stPaid['MaHoaDon'] . " (Số tiền: " . number_format($stPaid['TongTien'], 0, ',', '.') . " đ)\n";
    echo "    - Trạng thái  : ĐÃ THU (Hệ thống giả lập sinh viên này đã nộp đúng hạn)\n";
    echo "    -> KẾT QUẢ BƯỚC 3: Tự động GIA HẠN HỢP ĐỒNG LƯU TRÚ (Tài khoản KHÔNG bị khóa)\n\n";

    echo " 2. SINH VIÊN 2 (CHƯA NỘP TIỀN PHÒNG):\n";
    echo "    - MSSV        : " . $stUnpaid['MSSV'] . "\n";
    echo "    - Họ và tên   : " . $stUnpaid['HoTen'] . "\n";
    echo "    - Phòng       : " . $stUnpaid['MaPhong'] . "\n";
    echo "    - Hóa đơn     : " . $stUnpaid['MaHoaDon'] . " (Số tiền: " . number_format($stUnpaid['TongTien'], 0, ',', '.') . " đ)\n";
    echo "    - Trạng thái  : CHƯA THU (Quá hạn thanh toán)\n";
    echo "    -> KẾT QUẢ BƯỚC 3: Tự động BỊ KHÓA TÀI KHOẢN & ĐÁNH DẤU 'Đã rời đi'\n";
    echo "    -> BẠN SẼ DÙNG SINH VIÊN NÀY ĐỂ DEMO 'XÁC NHẬN MỞ KHÓA' TRÊN GIAO DIỆN KẾ TOÁN!\n";
    echo "================================================================================\n";
}

function showGuide(string $sessionFile): void
{
    if (!file_exists($sessionFile)) {
        echo "[!] Chưa có phiên demo nào được khởi tạo.\n";
        return;
    }

    $data = json_decode(file_get_contents($sessionFile), true);
    $unpaid = $data['unpaid'];
    $paid = $data['paid'];

    echo "\n";
    echo "================================================================================\n";
    echo "         QUY TRÌNH THỰC HÀNH DEMO MỞ KHÓA TÀI KHOẢN TRÊN WEB CHO THẦY XEM\n";
    echo "================================================================================\n\n";

    echo ">>> BƯỚC A: CHỨNG MINH TÀI KHOẢN NỢ PHÍ ĐÃ BỊ HỆ THỐNG KHÓA\n";
    echo "  1. Mở trình duyệt vào trang đăng nhập: http://localhost/Kanto-KTX/index.php?action=login\n";
    echo "  2. Đăng nhập bằng tài khoản Sinh viên 2 (người nợ phí):\n";
    echo "     - Tên đăng nhập : " . $unpaid['MSSV'] . "\n";
    echo "     - Mật khẩu      : 123456\n";
    echo "  3. Kết quả hiển thị: Thông báo lỗi màu đỏ:\n";
    echo "     'Tài khoản đã bị khóa.'\n\n";

    echo ">>> BƯỚC B: NHÂN VIÊN KẾ TOÁN THU TIỀN VÀ MỞ KHÓA TÀI KHOẢN\n";
    echo "  1. Đăng nhập tài khoản Nhân viên Kế toán:\n";
    echo "     - Tên đăng nhập : nvkt001\n";
    echo "     - Mật khẩu      : 123456\n";
    echo "  2. Vào menu bên trái: 'Quản lý Hóa đơn' (Payments)\n";
    echo "  3. Ở ô tìm kiếm MSSV, nhập: " . $unpaid['MSSV'] . " (hoặc mã HĐ: " . $unpaid['MaHoaDon'] . ")\n";
    echo "  4. Bấm nút: 'Xác nhận thu'\n";
    echo "  5. Quan sát thông báo màu xanh trên đầu trang:\n";
    echo "     'Xác nhận thành công. Đã mở khóa tài khoản " . $unpaid['MSSV'] . " và khôi phục hợp đồng lưu trú...'\n\n";

    echo ">>> BƯỚC C: SINH VIÊN ĐĂNG NHẬP LẠI THÀNH CÔNG BÌNH THƯỜNG\n";
    echo "  1. Đăng xuất tài khoản Kế toán.\n";
    echo "  2. Đăng nhập lại bằng: " . $unpaid['MSSV'] . " / Mật khẩu: 123456\n";
    echo "  3. Đăng nhập thành công ngay lập tức! Vào trang Dashboard sinh viên:\n";
    echo "     - Trạng thái lưu trú: 'Đang ở'\n";
    echo "     - Mục 'Thông báo mới nhất' có thông báo: 'Mở khóa tài khoản và khôi phục lưu trú'\n";
    echo "     - Hợp đồng lưu trú phòng " . $unpaid['MaPhong'] . " đã được kích hoạt lại đầy đủ!\n";
    echo "================================================================================\n";
}
