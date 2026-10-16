
<?php
// ===== UC07.1 - TRA CỨU HÓA ĐƠN =====
require_once "config/db.php";

// UC07.2 - XÁC NHẬN THANH TOÁN
// Xử lý POST trước khi xuất HTML để header() hoạt động đúng
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['xac_nhan_thanh_toan'])
) {
    $ma_hd_xac_nhan = trim($_POST['ma_hd'] ?? '');

    if ($ma_hd_xac_nhan === '') {
        exit('Không tìm thấy mã hóa đơn cần xác nhận.');
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        exit('Không kết nối được cơ sở dữ liệu.');
    }

    try {
        $pdo->beginTransaction();

        // Đọc và khóa hóa đơn để tránh xác nhận đồng thời
        $stmt = $pdo->prepare(
            "SELECT TrangThai
             FROM HoaDon
             WHERE MaHoaDon = :ma_hd
             FOR UPDATE"
        );

        $stmt->execute([':ma_hd' => $ma_hd_xac_nhan]);
        $hoa_don_xac_nhan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hoa_don_xac_nhan) {
            $pdo->rollBack();
            exit('Không tìm thấy hóa đơn.');
        }

        if (trim((string)$hoa_don_xac_nhan['TrangThai']) === 'Đã thu') {
            $pdo->rollBack();
            header('Location: uc7.php?btn_search=1&thong_bao=da_thu');
            exit;
        }

        if (trim((string)$hoa_don_xac_nhan['TrangThai']) !== 'Chưa thu') {
            $pdo->rollBack();
            header('Location: uc7.php?btn_search=1&thong_bao=trang_thai_khong_hop_le');
            exit;
        }

        // Chỉ cập nhật hóa đơn chưa thu
        $stmt = $pdo->prepare(
            "UPDATE HoaDon
             SET TrangThai = 'Đã thu'
             WHERE MaHoaDon = :ma_hd
               AND TrangThai = 'Chưa thu'"
        );

        $stmt->execute([':ma_hd' => $ma_hd_xac_nhan]);

        if ($stmt->rowCount() !== 1) {
            $pdo->rollBack();
            header('Location: uc7.php?btn_search=1&thong_bao=that_bai');
            exit;
        }

        $pdo->commit();

        header('Location: uc7.php?btn_search=1&thong_bao=thanh_cong');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('UC07.2 lỗi: ' . $e->getMessage());
        exit('Có lỗi xảy ra khi xác nhận thanh toán. Vui lòng kiểm tra log PHP.');
    }
}

// Danh sách giá trị hợp lệ cho các bộ lọc (BR-UC07.1-02, 03)
$ds_loai       = ['Tất cả', 'Tiền phòng', 'Điện nước'];
$ds_trang_thai = ['Tất cả', 'Chưa thu', 'Đã thu'];
$nam_hien_tai  = (int)date('Y');
$ds_nam        = range($nam_hien_tai, $nam_hien_tai - 3);

// Nhận dữ liệu; nếu không nhập thì dùng giá trị mặc định (A1, BR-UC07.1-05)
$ma_hd      = trim($_GET['ma_hd'] ?? '');
$mssv       = trim($_GET['mssv'] ?? '');
$ho_ten     = trim($_GET['ho_ten'] ?? '');
$phong      = trim($_GET['phong'] ?? '');
$loai       = $_GET['loai'] ?? 'Tất cả';
$thang      = $_GET['thang'] ?? 'Tất cả';
$nam        = $_GET['nam'] ?? (string)$nam_hien_tai;
$trang_thai = $_GET['trang_thai'] ?? 'Tất cả';
$trang      = max(1, (int)($_GET['page'] ?? 1));

$da_tra_cuu = isset($_GET['btn_search']);

// Hàm xuất dữ liệu an toàn ra HTML
function h($s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

function chon($a, $b): string {
    return (string)$a === (string)$b ? 'selected' : '';
}

function lienKetTrang(int $p): string {
    $tham_so = $_GET;
    $tham_so['page'] = $p;
    return '?' . http_build_query($tham_so);
}

// Kiểm tra dữ liệu hợp lệ (E2)
$loi = [];

if ($mssv !== '' && !preg_match('/^[A-Za-z]{2,6}\d{5,9}$/', $mssv)) {
    $loi['mssv'] = 'MSSV không đúng định dạng (ví dụ: KTPM2411001).';
}

if ($phong !== '' && !preg_match('/^[A-Za-z]\d{3}$/', $phong)) {
    $loi['phong'] = 'Mã phòng không đúng định dạng (ví dụ: A101).';
}

if ($ma_hd !== '' && !preg_match('/^[A-Za-z0-9_-]+$/', $ma_hd)) {
    $loi['ma_hd'] = 'Mã hóa đơn không hợp lệ.';
}

if (!in_array($loai, $ds_loai, true)) {
    $loi['loai'] = 'Loại hóa đơn không hợp lệ.';
}

if (!in_array($trang_thai, $ds_trang_thai, true)) {
    $loi['trang_thai'] = 'Trạng thái không hợp lệ.';
}

if (
    $thang !== 'Tất cả'
    && !(ctype_digit((string)$thang)
    && (int)$thang >= 1
    && (int)$thang <= 12)
) {
    $loi['thang'] = 'Tháng không hợp lệ.';
}

if (!ctype_digit((string)$nam) || !in_array((int)$nam, $ds_nam, true)) {
    $loi['nam'] = 'Năm không hợp lệ.';
}

// [SỬA] Tra cứu từ CSDL - chỉ SELECT, không sửa dữ liệu (BR-UC07.1-04)
$moi_trang  = 5;
$tong       = 0;
$tong_trang = 1;
$bat_dau    = 0;
$hien_thi   = [];
$loi_db     = '';

if ($da_tra_cuu && !$loi) {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $loi_db = 'Không kết nối được cơ sở dữ liệu. Vui lòng kiểm tra config/db.php.';
    } else {
        try {
            /*
             * Bảng HoaDon là dữ liệu chung.
             * HoaDonTienPhong chứa MSSV và MaPhong.
             * HoaDonDienNuoc chứa MaPhong, Thang và Nam.
             * HoaDonDienNuocSinhVien liên kết hóa đơn điện nước với sinh viên.
             *
             * Dùng UNION ALL để gom hai loại hóa đơn thành một danh sách tra cứu.
             */
            $goc = "
                SELECT
                    h.MaHoaDon AS ma_hd,
                    tp.MSSV AS mssv,
                    sv.HoTen AS ho_ten,
                    tp.MaPhong AS phong,
                    h.LoaiHoaDon AS loai,
                    MONTH(h.ThoiGianLap) AS thang,
                    YEAR(h.ThoiGianLap) AS nam,
                    h.TongTien AS tong_tien,
                    h.TrangThai AS trang_thai,
                    h.ThoiGianLap AS ngay_lap
                FROM HoaDon h
                JOIN HoaDonTienPhong tp
                    ON tp.MaHoaDon = h.MaHoaDon
                JOIN SinhVien sv
                    ON sv.MSSV = tp.MSSV
                WHERE h.LoaiHoaDon = 'Tiền phòng'

                UNION ALL

                SELECT
                    h.MaHoaDon AS ma_hd,
                    COALESCE(
                        GROUP_CONCAT(
                            DISTINCT dsv.MSSV
                            ORDER BY dsv.MSSV SEPARATOR ', '
                        ), ''
                    ) AS mssv,
                    COALESCE(
                        GROUP_CONCAT(
                            DISTINCT sv.HoTen
                            ORDER BY sv.HoTen SEPARATOR ', '
                        ), ''
                    ) AS ho_ten,
                    dn.MaPhong AS phong,
                    h.LoaiHoaDon AS loai,
                    dn.Thang AS thang,
                    dn.Nam AS nam,
                    h.TongTien AS tong_tien,
                    h.TrangThai AS trang_thai,
                    h.ThoiGianLap AS ngay_lap
                FROM HoaDon h
                JOIN HoaDonDienNuoc dn
                    ON dn.MaHoaDon = h.MaHoaDon
                LEFT JOIN HoaDonDienNuocSinhVien dsv
                    ON dsv.MaHoaDon = dn.MaHoaDon
                LEFT JOIN SinhVien sv
                    ON sv.MSSV = dsv.MSSV
                WHERE h.LoaiHoaDon = 'Điện nước'
                GROUP BY
                    h.MaHoaDon,
                    h.LoaiHoaDon,
                    dn.MaPhong,
                    dn.Thang,
                    dn.Nam,
                    h.TongTien,
                    h.TrangThai,
                    h.ThoiGianLap
            ";

            $dk = ['1 = 1'];
            $tham_so = [];

            if ($ma_hd !== '') {
                $dk[] = 'ma_hd LIKE :ma_hd';
                $tham_so[':ma_hd'] = "%{$ma_hd}%";
            }

            if ($mssv !== '') {
                $dk[] = "FIND_IN_SET(:mssv, REPLACE(mssv, ', ', ',')) > 0";
                $tham_so[':mssv'] = $mssv;
            }

            if ($ho_ten !== '') {
                $dk[] = 'ho_ten LIKE :ho_ten';
                $tham_so[':ho_ten'] = "%{$ho_ten}%";
            }

            if ($phong !== '') {
                $dk[] = 'phong LIKE :phong';
                $tham_so[':phong'] = "%{$phong}%";
            }

            if ($loai !== 'Tất cả') {
                $dk[] = 'loai = :loai';
                $tham_so[':loai'] = $loai;
            }

            if ($thang !== 'Tất cả') {
                $dk[] = 'thang = :thang';
                $tham_so[':thang'] = (int)$thang;
            }

            if ($nam !== 'Tất cả') {
                $dk[] = 'nam = :nam';
                $tham_so[':nam'] = (int)$nam;
            }

            if ($trang_thai !== 'Tất cả') {
                $dk[] = 'trang_thai = :trang_thai';
                $tham_so[':trang_thai'] = $trang_thai;
            }

            $where = 'WHERE ' . implode(' AND ', $dk);

            // Đếm tổng số hóa đơn phù hợp
            $st = $pdo->prepare(
                "SELECT COUNT(*) FROM ($goc) AS t $where"
            );
            $st->execute($tham_so);
            $tong = (int)$st->fetchColumn();

            $tong_trang = max(1, (int)ceil($tong / $moi_trang));
            $trang = min($trang, $tong_trang);
            $bat_dau = ($trang - 1) * $moi_trang;

            // Lấy danh sách hóa đơn theo trang
            $st = $pdo->prepare(
                "SELECT * FROM ($goc) AS t
                 $where
                 ORDER BY nam DESC, thang DESC, ma_hd DESC
                 LIMIT $moi_trang OFFSET $bat_dau"
            );

            $st->execute($tham_so);
            $hien_thi = $st->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('UC07.1 lỗi truy vấn: ' . $e->getMessage());
            $loi_db = 'Có lỗi khi truy vấn dữ liệu hóa đơn. Vui lòng kiểm tra cấu trúc database.';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KANTO - Quản lý hóa đơn</title>
    <link rel="stylesheet" href="assets/css/uc7.css">
</head>
<body>

<header class="topbar">
    <!-- Thanh đầu trang-->
    <div class="brand">
        <img src="assets/images/logo.png" alt="KANTO Logo" class="brand-image">
        <div>
            <h2>KANTO</h2>
            <p>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</p>
        </div>
    </div>

    <div class="account">
        <span>NHÂN VIÊN KẾ TOÁN</span>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <h4>NHÂN VIÊN KẾ TOÁN</h4>
        <a href="#">Trang chủ</a>
        <a href="uc7.php" class="active">Quản lý hóa đơn</a>
    </aside>

    <!--Nội dung chính-->
    <main class="content">
        <h1>Quản lý hóa đơn</h1>
        <p class="subtitle">
            Tra cứu, theo dõi và xử lý các hóa đơn của sinh viên
        </p>

        <section class="filter-box">
            <form method="GET" action="uc7.php" id="searchForm">
                <input type="hidden" name="btn_search" value="1">

                <div class="filter-grid">

                    <div class="form_group">
                        <label for="ma_hd">Mã hóa đơn</label>
                        <input
                            type="text"
                            id="ma_hd"
                            name="ma_hd"
                            placeholder="Nhập mã hóa đơn"
                            value="<?= h($ma_hd) ?>"
                        >
                        <?php if (isset($loi['ma_hd'])): ?>
                            <small class="error"><?= h($loi['ma_hd']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form_group">
                        <label for="mssv">Mã số sinh viên</label>
                        <input
                            type="text"
                            id="mssv"
                            name="mssv"
                            placeholder="Nhập MSSV"
                            value="<?= h($mssv) ?>"
                        >
                        <?php if (isset($loi['mssv'])): ?>
                            <small class="error"><?= h($loi['mssv']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form_group">
                        <label for="ho_ten">Họ và tên</label>
                        <input
                            type="text"
                            id="ho_ten"
                            name="ho_ten"
                            placeholder="Nhập họ tên sinh viên"
                            value="<?= h($ho_ten) ?>"
                        >
                    </div>

                    <div class="form_group">
                        <label for="phong">Số phòng</label>
                        <input
                            type="text"
                            id="phong"
                            name="phong"
                            placeholder="Nhập mã phòng"
                            value="<?= h($phong) ?>"
                        >
                        <?php if (isset($loi['phong'])): ?>
                            <small class="error"><?= h($loi['phong']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form_group">
                        <label for="loai">Loại hóa đơn</label>
                        <select id="loai" name="loai">
                            <?php foreach ($ds_loai as $item): ?>
                                <option value="<?= h($item) ?>"
                                    <?= chon($loai, $item) ?>>
                                    <?= h($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form_group">
                        <label for="trang_thai">Trạng thái</label>
                        <select id="trang_thai" name="trang_thai">
                            <?php foreach ($ds_trang_thai as $item): ?>
                                <option value="<?= h($item) ?>"
                                    <?= chon($trang_thai, $item) ?>>
                                    <?= h($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form_group">
                        <label for="thang">Tháng</label>
                        <select id="thang" name="thang">
                            <option value="Tất cả"
                                <?= chon($thang, 'Tất cả') ?>>
                                Tất cả
                            </option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?= chon($thang, (string)$i) ?>>
                                    <?= sprintf('%02d', $i) ?>/<?= h($nam) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form_group">
                        <label for="nam">Năm</label>
                        <select id="nam" name="nam">
                            <?php foreach ($ds_nam as $y): ?>
                                <option value="<?= $y ?>"
                                    <?= chon($nam, (string)$y) ?>>
                                    <?= $y ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-primary">
                            Tra cứu
                        </button>

                        <button type="button" class="btn-outline" id="btnReset">
                            Đặt lại
                        </button>
                    </div>

                </div>
            </form>
        </section>

        <?php if ($loi_db !== ''): ?>
            <div class="error-message"><?= h($loi_db) ?></div>
        <?php endif; ?>

        <?php if (($_GET['thong_bao'] ?? '') === 'thanh_cong'): ?>
            <p class="success-message">Xác nhận thành công.</p>
        <?php elseif (($_GET['thong_bao'] ?? '') === 'da_thu'): ?>
            <p class="error-message">Hóa đơn đã được thanh toán. Không thể xác nhận lại.</p>
        <?php elseif (($_GET['thong_bao'] ?? '') === 'trang_thai_khong_hop_le'): ?>
            <p class="error-message">Trạng thái hóa đơn không hợp lệ.</p>
        <?php elseif (($_GET['thong_bao'] ?? '') === 'that_bai'): ?>
            <p class="error-message">Không thể xác nhận thanh toán. Vui lòng thử lại.</p>
        <?php endif; ?>

        <!--Danh sách hóa đơn-->
        <section class="table-box">
            <h2>Danh sách hóa đơn</h2>

            <div class="table-scroll">
                <table>
                    <thead>
                        <!--Phần đầu bảng-->
                        <tr>
                            <th>Chọn</th>
                            <th>STT</th>
                            <th>Mã hóa đơn</th>
                            <th>Loại hóa đơn</th>
                            <th>MSSV</th>
                            <th>Họ và tên</th>
                            <th>Phòng</th>
                            <th>Kỳ</th>
                            <th>Số tiền (VND)</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!--thân bảng-->
                        <?php if (!$da_tra_cuu): ?>
                            <tr>
                                <td colspan="10">
                                    Nhập tiêu chí và nhấn “Tra cứu” để xem hóa đơn.
                                </td>
                            </tr>
                        <?php elseif ($loi): ?>
                            <tr>
                                <td colspan="10">
                                    Vui lòng kiểm tra lại thông tin tìm kiếm.
                                </td>
                            </tr>
                        <?php elseif ($loi_db === '' && count($hien_thi) === 0): ?>
                            <tr>
                                <td colspan="10">Không tìm thấy hóa đơn.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hien_thi as $i => $hd): ?>
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="invoice-checkbox"
                                            value="<?= h($hd['ma_hd']) ?>"
                                            data-trang-thai="<?= h(trim((string)($hd['trang_thai'] ?? ''))) ?>"
                                            aria-label="Chọn hóa đơn <?= h($hd['ma_hd']) ?>"
                                        >
                                    </td>
                                    <td><?= $bat_dau + $i + 1 ?></td>
                                    <td><?= h($hd['ma_hd']) ?></td>
                                    <td><?= h($hd['loai']) ?></td>
                                    <td><?= h($hd['mssv']) ?></td>
                                    <td><?= h($hd['ho_ten']) ?></td>
                                    <td><?= h($hd['phong']) ?></td>
                                    <td>
                                        <?= h(sprintf('%02d', (int)$hd['thang'])) ?>/<?= h($hd['nam']) ?>
                                    </td>
                                    <td>
                                        <?= number_format((float)$hd['tong_tien'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="status <?= trim((string)$hd['trang_thai']) === 'Đã thu' ? 'paid' : 'unpaid' ?>">
                                            <?= h($hd['trang_thai']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!--Phân trang-->
            <div class="table-footer">
                <span>
                    <?php if ($da_tra_cuu && !$loi && $loi_db === '' && $tong > 0): ?>
                        Hiển thị <?= $bat_dau + 1 ?> - <?= min($bat_dau + count($hien_thi), $tong) ?>
                        trong tổng số <?= $tong ?> hóa đơn
                    <?php else: ?>
                        Hiển thị 0 hóa đơn
                    <?php endif; ?>
                </span>

                <?php if ($da_tra_cuu && !$loi && $loi_db === '' && $tong_trang > 1): ?>
                    <div class="pagination">
                        <?php if ($trang > 1): ?>
                            <a href="<?= h(lienKetTrang($trang - 1)) ?>">‹</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $tong_trang; $p++): ?>
                            <a
                                href="<?= h(lienKetTrang($p)) ?>"
                                class="<?= $p === $trang ? 'selected' : '' ?>"
                            >
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($trang < $tong_trang): ?>
                            <a href="<?= h(lienKetTrang($trang + 1)) ?>">›</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="payment-action">
                <span>Chọn hóa đơn cần xác nhận rồi nhấn "Xác nhận thanh toán".</span>
                <button type="button" class="btn-primary" id="btnPayment">
                    Xác nhận thanh toán
                </button>
            </div>
        </section>
    </main>
</div>

<script>
// Đặt lại: trở về trang ban đầu, xóa toàn bộ tham số tìm kiếm
document.getElementById('btnReset').addEventListener('click', function () {
    window.location.href = 'uc7.php';
});

// Chọn hóa đơn và xác nhận thanh toán
document.getElementById('btnPayment').addEventListener('click', function () {
    const selected = Array.from(
        document.querySelectorAll('.invoice-checkbox:checked')
    );

    //  nếu chưa chọn hóa đơn
    if (selected.length === 0) {
        alert('Vui lòng chọn hóa đơn cần xác nhận thanh toán.');
        return;
    }

    // Chỉ xác nhận một hóa đơn mỗi lần
    if (selected.length > 1) {
        alert('Vui lòng chỉ chọn một hóa đơn mỗi lần.');
        return;
    }

    // Lấy hóa đơn được chọn
    const checkbox = selected[0];
    const maHoaDon = checkbox.value;
    const trangThai = (checkbox.getAttribute('data-trang-thai') || '').trim();

    // - Hóa đơn đã được thanh toán
    if (trangThai === 'Đã thu') {
        alert('Hóa đơn đã được thanh toán. Không thể xác nhận lại.');
        return;
    }

    if (trangThai !== 'Chưa thu') {
        alert('Trạng thái hóa đơn không hợp lệ.');
        return;
    }

    // Hỏi xác nhận; chọn Hủy thì không thay đổi dữ liệu
    if (!confirm(
        'Bạn có chắc chắn muốn xác nhận thanh toán hóa đơn này không?'
    )) {
        return;
    }

    // Gửi yêu cầu xác nhận đến PHP
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'uc7.php';

    const action = document.createElement('input');
    action.type = 'hidden';
    action.name = 'xac_nhan_thanh_toan';
    action.value = '1';

    const maHD = document.createElement('input');
    maHD.type = 'hidden';
    maHD.name = 'ma_hd';
    maHD.value = maHoaDon;

    form.appendChild(action);
    form.appendChild(maHD);
    document.body.appendChild(form);
    form.submit();
});
</script>

</body>
</html>
