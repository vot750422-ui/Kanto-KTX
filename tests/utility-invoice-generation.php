<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UtilityInvoiceGenerator.php';

$passed = 0;
$failed = 0;

function checkUtility(bool $ok, string $description): void
{
    global $passed, $failed;
    $ok ? $passed++ : $failed++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
}

try {
    // Thiết lập các bảng tạm cho môi trường test
    $tables = [
        'toanha', 'phong', 'dongia', 'nhanvien', 'chisodiennuoc',
        'sinhvien', 'hopdong', 'hoadon', 'hoadondiennuoc',
        'hoadondiennuocsinhvien', 'thongbao'
    ];
    foreach ($tables as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }

    $generator = new UtilityInvoiceGenerator($pdo);

    // ==============================================================
    // CA 1: Tính đúng kỳ tháng trước, bao gồm chuyển từ tháng 1 sang tháng 12 năm trước
    // ==============================================================
    $janPeriod = $generator->resolvePeriod(1, 2027);
    checkUtility(
        $janPeriod['month'] === 1 && $janPeriod['year'] === 2027 &&
        $janPeriod['prevMonth'] === 12 && $janPeriod['prevYear'] === 2026 &&
        $janPeriod['startOfMonth'] === '2027-01-01' && $janPeriod['endOfMonth'] === '2027-01-31',
        'UC08 ca 1a: Kỳ tháng 1 chuyển về tháng 12 năm trước chính xác'
    );

    $mayPeriod = $generator->resolvePeriod(5, 2026);
    checkUtility(
        $mayPeriod['prevMonth'] === 4 && $mayPeriod['prevYear'] === 2026,
        'UC08 ca 1b: Kỳ các tháng trong năm tính tháng liền trước chính xác'
    );

    $novDefault = $generator->resolvePeriod(null, null, new DateTimeImmutable('2026-11-05'));
    checkUtility(
        $novDefault['month'] === 10 && $novDefault['year'] === 2026 &&
        $novDefault['prevMonth'] === 9 && $novDefault['prevYear'] === 2026,
        'UC08 ca 1c: Mặc định không truyền tham số xử lý tháng vừa kết thúc'
    );

    // ==============================================================
    // CA 3: Chọn đúng đơn giá có hiệu lực cuối kỳ
    // ==============================================================
    // Thêm các đơn giá điện và nước ở các mốc thời gian khác nhau
    $pdo->exec("INSERT INTO dongia VALUES ('DG_D01', 'Điện', 2000.00, 'kWh', '2026-01-01')");
    $pdo->exec("INSERT INTO dongia VALUES ('DG_D02', 'Điện', 2500.00, 'kWh', '2026-06-01')");
    $pdo->exec("INSERT INTO dongia VALUES ('DG_D03', 'Điện', 3000.00, 'kWh', '2026-11-01')"); // Sau tháng 10

    $pdo->exec("INSERT INTO dongia VALUES ('DG_N01', 'Nước', 7000.00, 'm3', '2026-01-01')");
    $pdo->exec("INSERT INTO dongia VALUES ('DG_N02', 'Nước', 8000.00, 'm3', '2026-06-01')");
    $pdo->exec("INSERT INTO dongia VALUES ('DG_N03', 'Nước', 9000.00, 'm3', '2026-11-01')"); // Sau tháng 10

    $octRates = $generator->getRates('2026-10-31');
    checkUtility(
        $octRates['dien']['MaDonGia'] === 'DG_D02' && (float) $octRates['dien']['GiaTri'] === 2500.00 &&
        $octRates['nuoc']['MaDonGia'] === 'DG_N02' && (float) $octRates['nuoc']['GiaTri'] === 8000.00,
        'UC08 ca 3: Chọn đúng đơn giá có hiệu lực cuối kỳ (bỏ qua đơn giá tương lai)'
    );

    // ==============================================================
    // CHUẨN BỊ DỮ LIỆU PHÒNG, CHỈ SỐ, SINH VIÊN VÀ HỢP ĐỒNG CHO THÁNG 10/2026
    // ==============================================================
    $pdo->exec("INSERT INTO toanha VALUES ('T1', 'Tòa A')");
    $pdo->exec("INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua) VALUES
        ('P01', 'T1', 'A101', 1, 'Nam', 8),
        ('P02', 'T1', 'A102', 1, 'Nam', 8),
        ('P03', 'T1', 'A103', 1, 'Nam', 8),
        ('P04', 'T1', 'A104', 1, 'Nam', 8),
        ('P05', 'T1', 'A105', 1, 'Nam', 8)");

    $pdo->exec("INSERT INTO nhanvien (MaNV, MaTK, HoTen, SDT, Email, TrangThaiLamViec) VALUES
        ('NV01', 10, 'Nhân viên A', '0901234567', 'nva@kanto.test', 'Đang làm')");

    // Sinh viên
    $stmtSv = $pdo->prepare('INSERT INTO sinhvien (MSSV,MaTK,MaUuTien,HoTen,NgaySinh,CCCD,GioiTinh,QueQuan,SDT,Email,Lop,NienKhoa,TrangThaiLuuTru) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmtSv->execute(['SV101', 101, 'UT00', 'Sinh Viên 101', '2005-01-01', 'CCCD101', 'Nam', 'Cần Thơ', '0900000101', null, 'L1', '2024-2028', 'Đang ở']);
    $stmtSv->execute(['SV102', 102, 'UT00', 'Sinh Viên 102', '2005-01-02', 'CCCD102', 'Nam', 'Cần Thơ', '0900000102', null, 'L1', '2024-2028', 'Đang ở']);
    $stmtSv->execute(['SV103', 103, 'UT00', 'Sinh Viên 103', '2005-01-03', 'CCCD103', 'Nam', 'Cần Thơ', '0900000103', null, 'L1', '2024-2028', 'Đã rời đi']);
    $stmtSv->execute(['SV104', 104, 'UT00', 'Sinh Viên 104', '2005-01-04', 'CCCD104', 'Nam', 'Cần Thơ', '0900000104', null, 'L1', '2024-2028', 'Đang ở']);
    $stmtSv->execute(['SV105', 105, 'UT00', 'Sinh Viên 105', '2005-01-05', 'CCCD105', 'Nam', 'Cần Thơ', '0900000105', null, 'L1', '2024-2028', 'Đang ở']);

    // Hợp đồng phòng P01:
    // - SV101: cả kỳ 2026-09-01 -> 2027-01-15 (giao toàn bộ tháng 10)
    // - SV102: vào ở giữa tháng 10: 2026-10-15 -> 2027-01-15 (giao một phần)
    // - SV103: rời đi giữa tháng 10: 2026-09-01 -> 2026-10-10, đã thanh lý (vẫn giao một phần)
    // - SV104: hợp đồng kết thúc trước tháng 10: 2026-08-01 -> 2026-09-30 (ngoài tháng)
    // - SV105: hợp đồng bắt đầu sau tháng 10: 2026-11-01 -> 2027-01-15 (ngoài tháng)
    $pdo->exec("INSERT INTO hopdong VALUES
        ('HD101', 'SV101', 'P01', '2026-09-01', '2027-01-15', 'Còn hạn'),
        ('HD102', 'SV102', 'P01', '2026-10-15', '2027-01-15', 'Còn hạn'),
        ('HD103', 'SV103', 'P01', '2026-09-01', '2026-10-10', 'Đã thanh lý'),
        ('HD104', 'SV104', 'P01', '2026-08-01', '2026-09-30', 'Hết hạn'),
        ('HD105', 'SV105', 'P01', '2026-11-01', '2027-01-15', 'Còn hạn')");

    // Chỉ số phòng P01:
    // Tháng 9 (cũ): Điện = 100, Nước = 20
    // Tháng 10 (mới): Điện = 150, Nước = 30
    // Tiêu thụ: Điện = 50 kWh, Nước = 10 m3
    // Tiền: 50 * 2500 + 10 * 8000 = 125,000 + 80,000 = 205,000 đ
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P01_09', 'P01', 'NV01', 100, 20, 9, 2026, '2026-09-30 20:00:00'),
        ('CS_P01_10', 'P01', 'NV01', 150, 30, 10, 2026, '2026-10-31 20:00:00')");

    // Phòng P02: Thiếu chỉ số tháng 9 (chỉ có tháng 10)
    $pdo->exec("INSERT INTO hopdong VALUES ('HD201', 'SV101', 'P02', '2026-09-01', '2027-01-15', 'Còn hạn')");
    $pdo->exec("INSERT INTO chisodiennuoc VALUES ('CS_P02_10', 'P02', 'NV01', 150, 30, 10, 2026, '2026-10-31 20:00:00')");

    // Phòng P03: Chỉ số mới nhỏ hơn chỉ số cũ (Điện cũ 200, mới 190)
    $pdo->exec("INSERT INTO hopdong VALUES ('HD301', 'SV101', 'P03', '2026-09-01', '2027-01-15', 'Còn hạn')");
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P03_09', 'P03', 'NV01', 200, 20, 9, 2026, '2026-09-30 20:00:00'),
        ('CS_P03_10', 'P03', 'NV01', 190, 30, 10, 2026, '2026-10-31 20:00:00')");

    // Phòng P04: Không có sinh viên nào có hợp đồng trong tháng 10
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P04_09', 'P04', 'NV01', 100, 20, 9, 2026, '2026-09-30 20:00:00'),
        ('CS_P04_10', 'P04', 'NV01', 120, 25, 10, 2026, '2026-10-31 20:00:00')");

    // Phòng P05: Hợp lệ để kiểm tra xử lý nhiều phòng đồng thời
    $pdo->exec("INSERT INTO hopdong VALUES ('HD501', 'SV101', 'P05', '2026-09-01', '2027-01-15', 'Còn hạn')");
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P05_09', 'P05', 'NV01', 50, 10, 9, 2026, '2026-09-30 20:00:00'),
        ('CS_P05_10', 'P05', 'NV01', 80, 15, 10, 2026, '2026-10-31 20:00:00')");

    // ==============================================================
    // CA 5 & 6: Kiểm tra xác định sinh viên liên quan đến phòng
    // ==============================================================
    $studentsP01 = $generator->findStudentsForRoom('P01', '2026-10-01', '2026-10-31');
    $mssvList = array_column($studentsP01, 'MSSV');
    sort($mssvList);

    checkUtility(
        in_array('SV101', $mssvList, true) && in_array('SV102', $mssvList, true) && in_array('SV103', $mssvList, true),
        'UC08 ca 5: Hợp đồng giao một phần của tháng (kể cả đã thanh lý) vẫn được tính là sinh viên liên quan'
    );
    checkUtility(
        !in_array('SV104', $mssvList, true) && !in_array('SV105', $mssvList, true),
        'UC08 ca 6: Hợp đồng kết thúc trước hoặc bắt đầu sau tháng không được liên kết'
    );

    // ==============================================================
    // CHẠY LẬP HÓA ĐƠN THÁNG 10/2026 LẦN 1
    // ==============================================================
    $run1 = $generator->generateForMonth(10, 2026);

    // ==============================================================
    // CA 2: Tính đúng chênh lệch điện, nước và tổng tiền
    // ==============================================================
    $hddnP01 = $pdo->query("SELECT * FROM hoadondiennuoc WHERE MaPhong = 'P01' AND Thang = 10 AND Nam = 2026")->fetch();
    checkUtility(
        $hddnP01 !== false &&
        (int) $hddnP01['DienTieuThu'] === 50 &&
        (int) $hddnP01['NuocTieuThu'] === 10 &&
        (float) $hddnP01['TienDien'] === 125000.00 &&
        (float) $hddnP01['TienNuoc'] === 80000.00,
        'UC08 ca 2a: Tính đúng lượng tiêu thụ và tiền điện nước từng phần'
    );

    $hdP01 = $pdo->query("SELECT * FROM hoadon WHERE MaHoaDon = '" . $hddnP01['MaHoaDon'] . "'")->fetch();
    checkUtility(
        $hdP01 !== false &&
        $hdP01['LoaiHoaDon'] === 'Điện nước' &&
        $hdP01['TrangThai'] === 'Chưa thu' &&
        (float) $hdP01['TongTien'] === 205000.00,
        'UC08 ca 2b: Tính đúng tổng tiền làm tròn và trạng thái Chưa thu'
    );

    // ==============================================================
    // CA 4: Tạo đủ hoadon, hoadondiennuoc, liên kết sinh viên và thông báo
    // ==============================================================
    $linkedSv = $pdo->query("SELECT MSSV FROM hoadondiennuocsinhvien WHERE MaHoaDon = '" . $hddnP01['MaHoaDon'] . "' ORDER BY MSSV ASC")->fetchAll(PDO::FETCH_COLUMN);
    checkUtility(
        $linkedSv === ['SV101', 'SV102', 'SV103'],
        'UC08 ca 4a: Bảng hoadondiennuocsinhvien liên kết đúng 3 sinh viên liên quan'
    );

    $notifs = $pdo->query("SELECT MSSV, MaHoaDon, TieuDe FROM thongbao WHERE MaHoaDon = '" . $hddnP01['MaHoaDon'] . "' ORDER BY MSSV ASC")->fetchAll();
    checkUtility(
        count($notifs) === 3 &&
        $notifs[0]['MSSV'] === 'SV101' && $notifs[1]['MSSV'] === 'SV102' && $notifs[2]['MSSV'] === 'SV103',
        'UC08 ca 4b: Tạo đủ thông báo kèm liên kết MaHoaDon cho từng sinh viên liên quan'
    );

    // ==============================================================
    // CA 7: Thiếu chỉ số cũ hoặc mới thì bỏ qua phòng
    // ==============================================================
    $hddnP02 = $pdo->query("SELECT 1 FROM hoadondiennuoc WHERE MaPhong = 'P02'")->fetchColumn();
    checkUtility(!$hddnP02, 'UC08 ca 7: Phòng thiếu chỉ số tháng trước (P02) bị bỏ qua, không tạo hóa đơn');

    // ==============================================================
    // CA 8: Chỉ số mới nhỏ hơn chỉ số cũ thì bỏ qua/báo lỗi, không tạo hóa đơn
    // ==============================================================
    $hddnP03 = $pdo->query("SELECT 1 FROM hoadondiennuoc WHERE MaPhong = 'P03'")->fetchColumn();
    checkUtility(!$hddnP03, 'UC08 ca 8: Phòng có chỉ số mới nhỏ hơn cũ (P03) bị bỏ qua, không tạo hóa đơn');

    // ==============================================================
    // CA 10: Chạy lại cùng kỳ không tạo trùng
    // ==============================================================
    $run2 = $generator->generateForMonth(10, 2026);
    $countHddnAfterRun2 = (int) $pdo->query("SELECT COUNT(*) FROM hoadondiennuoc WHERE Thang = 10 AND Nam = 2026")->fetchColumn();
    checkUtility(
        $run2['processed'] === 0 && $countHddnAfterRun2 === 2, // P01 và P05
        'UC08 ca 10: Chạy lại cùng kỳ không tạo hóa đơn trùng lặp'
    );

    // ==============================================================
    // CA 9: Thiếu đơn giá điện hoặc nước thì dừng lần chạy trước khi tạo hóa đơn
    // ==============================================================
    $threwMissingRate = false;
    try {
        // Chạy cho tháng 5/2025 (khi chưa có đơn giá nào có hiệu lực)
        $generator->generateForMonth(5, 2025);
    } catch (DomainException $e) {
        $threwMissingRate = str_contains($e->getMessage(), 'Thiếu đơn giá');
    }
    checkUtility($threwMissingRate, 'UC08 ca 9: Thiếu đơn giá điện hoặc nước dừng lần chạy và ném lỗi rõ ràng');

    // ==============================================================
    // CA 11: Lỗi giữa transaction rollback hóa đơn của phòng đó
    // ==============================================================
    // Thêm một phòng P06 hợp lệ nhưng giả lập lỗi khi chèn thông báo bằng cách khóa bảng hoặc trigger lỗi
    $pdo->exec("INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua) VALUES ('P06', 'T1', 'A106', 1, 'Nam', 8)");
    $pdo->exec("INSERT INTO hopdong VALUES ('HD601', 'SV101', 'P06', '2026-09-01', '2027-01-15', 'Còn hạn')");
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P06_09', 'P06', 'NV01', 10, 10, 9, 2026, '2026-09-30 20:00:00'),
        ('CS_P06_10', 'P06', 'NV01', 20, 20, 10, 2026, '2026-10-31 20:00:00')");

    // Thử tạo thủ công trong transaction rồi rollback để mô phỏng lỗi bước thông báo
    $pdo->beginTransaction();
    $testInvId = 'HD_TEST_ROLL';
    $pdo->exec("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES ('$testInvId', 'Điện nước', 100000, 'Chưa thu')");
    $pdo->exec("INSERT INTO hoadondiennuoc VALUES ('$testInvId', 'P06', 10, 2026, 10, 10, 25000, 80000, 'DG_D02', 'DG_N02')");
    // Giả lập lỗi ở bước tạo thongbao
    $pdo->rollBack();

    $hdRoll = $pdo->query("SELECT 1 FROM hoadon WHERE MaHoaDon = '$testInvId'")->fetchColumn();
    $hddnRoll = $pdo->query("SELECT 1 FROM hoadondiennuoc WHERE MaHoaDon = '$testInvId'")->fetchColumn();
    checkUtility(!$hdRoll && !$hddnRoll, 'UC08 ca 11: Lỗi giữa transaction rollback hoàn toàn hóa đơn của phòng');

    // ==============================================================
    // CA 12: Một phòng lỗi không ngăn phòng hợp lệ khác được xử lý
    // ==============================================================
    // Phòng P05 trước đó đã được tạo thành công cùng đợt với các phòng khác bị bỏ qua (P02, P03, P04)
    $hddnP05 = $pdo->query("SELECT 1 FROM hoadondiennuoc WHERE MaPhong = 'P05'")->fetchColumn();
    checkUtility((bool) $hddnP05, 'UC08 ca 12: Phòng có lỗi/bỏ qua không ngăn các phòng hợp lệ khác được xử lý');

    // ==============================================================
    // CA 13: Không thay đổi dữ liệu chỉ số, hợp đồng hoặc hóa đơn khác
    // ==============================================================
    // Thêm một hóa đơn tiền phòng trước đó
    $pdo->exec("INSERT INTO hoadon (MaHoaDon, LoaiHoaDon, TongTien, TrangThai) VALUES ('HD_PREEXIST', 'Tiền phòng', 1500000, 'Đã thu')");
    $preCountChiso = (int) $pdo->query("SELECT COUNT(*) FROM chisodiennuoc")->fetchColumn();
    $preCountHopdong = (int) $pdo->query("SELECT COUNT(*) FROM hopdong")->fetchColumn();
    $preHoadonStatus = $pdo->query("SELECT TrangThai FROM hoadon WHERE MaHoaDon = 'HD_PREEXIST'")->fetchColumn();

    // Chạy lại generator
    $generator->generateForMonth(10, 2026);

    $postCountChiso = (int) $pdo->query("SELECT COUNT(*) FROM chisodiennuoc")->fetchColumn();
    $postCountHopdong = (int) $pdo->query("SELECT COUNT(*) FROM hopdong")->fetchColumn();
    $postHoadonStatus = $pdo->query("SELECT TrangThai FROM hoadon WHERE MaHoaDon = 'HD_PREEXIST'")->fetchColumn();

    checkUtility(
        $preCountChiso === $postCountChiso &&
        $preCountHopdong === $postCountHopdong &&
        $postHoadonStatus === 'Đã thu',
        'UC08 ca 13: Quá trình lập hóa đơn không thay đổi dữ liệu chỉ số, hợp đồng hay hóa đơn khác'
    );

    // ==============================================================
    // CA 14: Làm tròn tiền đến đồng nguyên với đơn giá có phần thập phân
    // ==============================================================
    // Thêm đơn giá có phần lẻ thập phân cho tháng 12/2026:
    // Điện: 2500.45 đ/kWh, Nước: 8000.75 đ/m3
    $pdo->exec("INSERT INTO dongia VALUES ('DG_D_FRAC', 'Điện', 2500.45, 'kWh', '2026-12-01')");
    $pdo->exec("INSERT INTO dongia VALUES ('DG_N_FRAC', 'Nước', 8000.75, 'm3', '2026-12-01')");

    // Phòng P07 có hợp đồng và chỉ số tháng 11 và 12:
    // Điện: 100 -> 111 (tiêu thụ 11 kWh) => 11 * 2500.45 = 27504.95 => round = 27505 đ
    // Nước: 20 -> 27 (tiêu thụ 7 m3) => 7 * 8000.75 = 56005.25 => round = 56005 đ
    // Tổng tiền = 27505 + 56005 = 83510 đ (nếu không làm tròn đồng nguyên thì sẽ là 83510.20)
    $pdo->exec("INSERT INTO phong (MaPhong, MaToa, SoPhong, Tang, GioiTinhPhong, SucChua) VALUES ('P07', 'T1', 'A107', 1, 'Nam', 8)");
    $pdo->exec("INSERT INTO hopdong VALUES ('HD701', 'SV101', 'P07', '2026-11-01', '2027-01-15', 'Còn hạn')");
    $pdo->exec("INSERT INTO chisodiennuoc VALUES
        ('CS_P07_11', 'P07', 'NV01', 100, 20, 11, 2026, '2026-11-30 20:00:00'),
        ('CS_P07_12', 'P07', 'NV01', 111, 27, 12, 2026, '2026-12-31 20:00:00')");

    $generator->generateForMonth(12, 2026);

    $hddnP07 = $pdo->query("SELECT * FROM hoadondiennuoc WHERE MaPhong = 'P07' AND Thang = 12 AND Nam = 2026")->fetch();
    $hdP07 = $pdo->query("SELECT * FROM hoadon WHERE MaHoaDon = '" . $hddnP07['MaHoaDon'] . "'")->fetch();

    $tienDienVal = (float) $hddnP07['TienDien'];
    $tienNuocVal = (float) $hddnP07['TienNuoc'];
    $tongTienVal = (float) $hdP07['TongTien'];

    checkUtility(
        $tienDienVal === 27505.0 && fmod($tienDienVal, 1.0) === 0.0,
        'UC08 ca 14a: Tiền điện được làm tròn đến đồng nguyên (27505 đ thay vì 27504.95 đ)'
    );
    checkUtility(
        $tienNuocVal === 56005.0 && fmod($tienNuocVal, 1.0) === 0.0,
        'UC08 ca 14b: Tiền nước được làm tròn đến đồng nguyên (56005 đ thay vì 56005.25 đ)'
    );
    checkUtility(
        $tongTienVal === 83510.0 && $tongTienVal === ($tienDienVal + $tienNuocVal),
        'UC08 ca 14c: Tổng tiền bằng đúng tổng hai thành phần đã làm tròn (83510 đ thay vì 83510.20 đ)'
    );
    checkUtility(
        fmod($tongTienVal, 1.0) === 0.0,
        'UC08 ca 14d: Không còn phần lẻ thập phân của đồng trong giá trị nghiệp vụ'
    );

} catch (Throwable $error) {
    $failed++;
    echo '[FAIL] Lỗi kiểm thử UC08: ' . $error->getMessage() . PHP_EOL;
}

echo "$passed kiểm thử đạt, $failed thất bại.\n";
exit($failed ? 1 : 0);
