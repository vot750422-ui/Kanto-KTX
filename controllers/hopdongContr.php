
<?php
require_once __DIR__ . '/../../models/HopDong.php';

class HopDongController
{
    private PDO $db;
    private HopDong $hopDongModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->hopDongModel = new HopDong($db);
    }

    // UC05.1: Tra cứu hợp đồng
    public function traCuu(): void
    {
        // Lấy dữ liệu tìm kiếm từ form
        $ma_hd = trim($_GET['ma_hd'] ?? '');
        $mssv = trim($_GET['mssv'] ?? '');
        $ho_ten = trim($_GET['ho_ten'] ?? '');
        $phong = trim($_GET['phong'] ?? '');
        $trang_thai = trim($_GET['trang_thai'] ?? '');

        // Kiểm tra người dùng đã nhấn Tìm kiếm chưa
        $is_searched = isset($_GET['btn_search']);
        $hopdongs = [];

        // Chỉ truy vấn khi người dùng nhấn Tìm kiếm
        if ($is_searched) {
            $filters = [
                'ma_hd' => $ma_hd,
                'mssv' => $mssv,
                'ho_ten' => $ho_ten,
                'phong' => $phong,
                'trang_thai' => $trang_thai
            ];

            $hopdongs = $this->hopDongModel->layDanhSach($filters);
        }

        require __DIR__ . '/../../views/nvql/hopdong/tracuu.php';
    }

    // UC05.2: Xem chi tiết hợp đồng
    public function chiTiet(): void
    {
        $maHopDong = trim($_GET['ma_hd'] ?? '');
        $hopDong = null;
        $loi = '';

        if ($maHopDong === '') {
            http_response_code(400);
            $loi = 'Vui lòng cung cấp mã hợp đồng.';
        } else {
            $hopDong = $this->hopDongModel->layChiTiet($maHopDong);

            if ($hopDong === null) {
                http_response_code(404);
                $loi = 'Không tìm thấy hợp đồng cần xem.';
            }
        }

        require __DIR__ . '/../../views/nvql/hopdong/chitiet.php';
    }
}
