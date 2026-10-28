<?php

require_once __DIR__ . '/../models/qlSinhvien.php';

class QlSinhvienController
{
    private QlSinhvien $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new QlSinhvien($pdo);
    }

    // UC: Tra cứu sinh viên
    public function traCuu(): void
    {
        $filters = [
            'mssv' => trim($_GET['mssv'] ?? ''),
            'ho_ten' => trim($_GET['ho_ten'] ?? ''),
            'lop' => trim($_GET['lop'] ?? ''),
        ];

        $daTimKiem = isset($_GET['btn_search']);
        $sinhviens = [];

        if ($daTimKiem) {
            $sinhviens = $this->model->layDanhSach($filters);
        }

        require __DIR__ . '/../views/nvql/qlSinhvien/tracuu.php';
    }

    // UC: Hiển thị form và cập nhật sinh viên
    public function capNhat(): void
    {
        $thongBao = '';
        $loi = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mssv = trim($_POST['MSSV'] ?? '');
            $sinhVien = $mssv !== ''
                ? $this->model->layChiTiet($mssv)
                : null;

            if (!$sinhVien) {
                http_response_code(404);
                $loi = 'Không tìm thấy sinh viên cần cập nhật.';
            } else {
                $data = [
                    'MSSV' => $mssv,
                    'MaUuTien' => trim($_POST['MaUuTien'] ?? ''),
                    'HoTen' => trim($_POST['HoTen'] ?? ''),
                    'NgaySinh' => trim($_POST['NgaySinh'] ?? ''),
                    'CCCD' => trim($_POST['CCCD'] ?? ''),
                    'GioiTinh' => trim($_POST['GioiTinh'] ?? ''),
                    'QueQuan' => trim($_POST['QueQuan'] ?? ''),
                    'SDT' => trim($_POST['SDT'] ?? ''),
                    'Lop' => trim($_POST['Lop'] ?? ''),
                    'NienKhoa' => trim($_POST['NienKhoa'] ?? ''),
                    'TrangThaiLuuTru' => trim(
                        $_POST['TrangThaiLuuTru'] ?? ''
                    ),
                ];

                foreach ($data as $key => $value) {
                    if ($key !== 'MSSV' && $value === '') {
                        $loi = 'Vui lòng nhập đầy đủ thông tin.';
                        break;
                    }
                }

                if (
                    $loi === '' &&
                    !in_array($data['GioiTinh'], ['Nam', 'Nữ'], true)
                ) {
                    $loi = 'Giới tính không hợp lệ.';
                }

                if (
                    $loi === '' &&
                    !in_array(
                        $data['TrangThaiLuuTru'],
                        ['Đang ở', 'Đã rời đi'],
                        true
                    )
                ) {
                    $loi = 'Trạng thái lưu trú không hợp lệ.';
                }

                if (
                    $loi === '' &&
                    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['NgaySinh'])
                ) {
                    $loi = 'Ngày sinh không hợp lệ.';
                }

                if ($loi === '') {
                    $ngaySinh = DateTime::createFromFormat(
                        '!Y-m-d',
                        $data['NgaySinh']
                    );

                    if (
                        !$ngaySinh ||
                        $ngaySinh->format('Y-m-d') !== $data['NgaySinh']
                    ) {
                        $loi = 'Ngày sinh không hợp lệ.';
                    }
                }

                if ($loi === '') {
                    try {
                        $this->model->capNhat($data);

                        header(
                            'Location: index.php?action=ql-sinhvien'
                            . '&btn_search=1'
                            . '&mssv=' . urlencode($mssv)
                            . '&thong_bao=cap_nhat_thanh_cong'
                        );
                        exit;
                    } catch (PDOException $e) {
                        $loi = 'Cập nhật thất bại. Hãy kiểm tra mã ưu tiên, '
                             . 'CCCD và dữ liệu nhập có hợp lệ không.';
                    }
                }

                // Giữ lại dữ liệu vừa nhập nếu có lỗi
                $sinhVien = array_merge($sinhVien, $data);
            }
        } else {
            $mssv = trim($_GET['mssv'] ?? '');
            $sinhVien = $mssv !== ''
                ? $this->model->layChiTiet($mssv)
                : null;

            if (!$sinhVien) {
                http_response_code(404);
                $loi = 'Không tìm thấy sinh viên cần cập nhật.';
            }
        }

        require __DIR__ . '/../views/nvql/qlSinhvien/capnhat.php';
    }
}