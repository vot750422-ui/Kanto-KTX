<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Registration.php';
$passed = 0;
function checkRoom(bool $condition, string $description): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException($description);
    }
    echo '[PASS] ' . $description . PHP_EOL;
    $passed++;
}
try {
    // Bảng tạm riêng trong kết nối test, không sửa phòng/đơn thật.
    foreach (['toanha', 'phong', 'dienuutien', 'hopdong', 'dondangky', 'sinhvien'] as $table) {
        $sql = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $sql = preg_replace('/^.*CONSTRAINT `[^`]+` FOREIGN KEY .*\r?\n/m', '', $sql);
        $sql = preg_replace('/,\s*\n\)/', "\n)", $sql);
        $pdo->exec(str_replace('CREATE TABLE', 'CREATE TEMPORARY TABLE', $sql));
    }
    $seed = file_get_contents(__DIR__ . '/../sql/seed-buildings-rooms.sql');
    $pdo->exec($seed);
    checkRoom((int) $pdo->query('SELECT COUNT(*) FROM toanha')->fetchColumn() === 4, 'Seed tạo 4 tòa');
    checkRoom((int) $pdo->query('SELECT COUNT(*) FROM phong')->fetchColumn() === 200, 'Seed tạo 200 phòng');
    checkRoom((int) $pdo->query('SELECT SUM(SucChua) FROM phong')->fetchColumn() === 1600, 'Tổng sức chứa 1600 sinh viên');
    $all = $pdo->query('SELECT * FROM phong ORDER BY MaPhong')->fetchAll();
    $counts = [];
    foreach ($all as $i => $room) {
        $building = intdiv($i, 50) + 1;
        $local = $i % 50;
        $floor = $local < 13 ? 1 : ($local < 26 ? 2 : ($local < 38 ? 3 : 4));
        $offset = [1 => 0, 2 => 13, 3 => 26, 4 => 38][$floor];
        $expectedNumber = chr(64 + $building) . $floor . str_pad((string) ($local - $offset + 1), 2, '0', STR_PAD_LEFT);
        if ($room['MaPhong'] !== sprintf('P%04d', $i + 1) || $room['MaToa'] !== sprintf('T%02d', $building)
            || $room['SoPhong'] !== $expectedNumber || (int) $room['Tang'] !== $floor
            || $room['GioiTinhPhong'] !== ($building <= 2 ? 'Nam' : 'Nữ') || (int) $room['SucChua'] !== 8) {
            throw new RuntimeException('Sai thông tin phòng: ' . $room['MaPhong']);
        }
        $counts[$room['MaToa']][$floor] = ($counts[$room['MaToa']][$floor] ?? 0) + 1;
    }
    checkRoom(count(array_filter($counts, fn($floors) => array_values($floors) === [13, 13, 12, 12])) === 4, 'Mỗi tòa đúng 13/13/12/12 phòng theo tầng');
    checkRoom(true, 'Đúng mã P0001–P0200, số phòng, giới tính và sức chứa từng phòng');
    $pdo->exec($seed);
    checkRoom($all === $pdo->query('SELECT * FROM phong ORDER BY MaPhong')->fetchAll(), 'Chạy seed lại không tạo trùng hoặc đổi phòng');
    $pdo->exec(file_get_contents(__DIR__ . '/../sql/update-priorities.sql'));
    $model = new Registration($pdo);
    $male = $model->rooms('Nam');
    $female = $model->rooms('Nữ');
    checkRoom(count($male) === 100 && array_unique(array_column($male, 'MaToa')) === [0 => 'T01', 50 => 'T02'], 'Nam chỉ thấy 100 phòng Tòa A/B');
    checkRoom(count($female) === 100 && array_unique(array_column($female, 'MaToa')) === [0 => 'T03', 50 => 'T04'], 'Nữ chỉ thấy 100 phòng Tòa C/D');
    $data = ['MSSV' => 'SEEDTEST', 'HoTen' => 'Sinh viên kiểm thử', 'NgaySinh' => '2006-01-01', 'CCCD' => '000000000001', 'GioiTinh' => 'Nam', 'QueQuan' => 'Kiểm thử', 'SDT' => '0900000000', 'Lop' => 'TEST', 'NienKhoa' => '2024-2028', 'MaUuTien' => 'UT00'];
    $id = $model->submit($data, 'P0001');
    $stmt = $pdo->prepare('SELECT MaPhong, TrangThai FROM dondangky WHERE MaDonDangKy = ?');
    $stmt->execute([$id]);
    checkRoom($stmt->fetch() === ['MaPhong' => 'P0001', 'TrangThai' => 'Chờ duyệt'], 'Đơn lưu MaPhong kỹ thuật và trạng thái Chờ duyệt');
    $first = $model->rooms('Nam')[0];
    checkRoom((int) $first['ConCho'] === 7 && (int) $first['SucChua'] === 8, 'Một đơn giữ một chỗ, sức chứa vẫn là 8');
    $pdo->exec("INSERT INTO hopdong (MaHopDong, MSSV, MaPhong, NgayBatDau, NgayKetThuc, TrangThai) VALUES ('H1','SV1','P0001','2026-01-01','2026-12-31','Còn hạn')");
    checkRoom((int) $model->rooms('Nam')[0]['ConCho'] === 6, 'Chỗ khả dụng trừ cả hợp đồng Còn hạn và đơn Chờ duyệt');
    for ($i = 2; $i <= 7; $i++) {
        $model->submit(array_replace($data, ['MSSV' => 'SEEDTEST' . $i]), 'P0001');
    }
    checkRoom(!in_array('P0001', array_column($model->rooms('Nam'), 'MaPhong'), true), 'Phòng đầy không xuất hiện trong UC02');
    $rejected = false;
    try {
        $model->submit(array_replace($data, ['MSSV' => 'OVERFLOW']), 'P0001');
    } catch (DomainException $error) {
        $rejected = $error->getCode() === 2;
    }
    checkRoom($rejected && !$model->hasPending('OVERFLOW'), 'Submit kiểm tra lại phòng đầy, không tạo đơn vượt sức chứa');
    $pdo->exec("UPDATE phong SET SucChua = 10 WHERE MaPhong = 'P0002'");
    $pdo->exec($seed);
    checkRoom((int) $pdo->query("SELECT SucChua FROM phong WHERE MaPhong = 'P0002'")->fetchColumn() === 10 && $model->hasPending('SEEDTEST'), 'Seed giữ dữ liệu đã chỉnh sửa và đơn đã có');
    echo "\n$passed kiểm thử dữ liệu phòng đạt.\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, '[FAIL] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
