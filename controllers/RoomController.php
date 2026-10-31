<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../models/Room.php';

// UC15 kiểm tra bộ lọc rồi hiển thị thông tin phòng công khai.
class RoomController
{
    public function __construct(private PDO $db) {}

    public function show(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            http_response_code(405);
            header('Allow: GET');
            return;
        }
        $gender = 'Nam';
        $buildingId = $floor = null;
        $buildings = $floors = $rooms = [];
        $selectedBuilding = null;
        $message = '';
        foreach (['GioiTinhPhong', 'MaToa', 'Tang'] as $key) {
            if (isset($_GET[$key]) && !is_string($_GET[$key])) {
                $message = 'Bộ lọc phòng không hợp lệ. Vui lòng chọn lại khu, tòa và tầng.';
            }
        }
        if (!$message) {
            $gender = $_GET['GioiTinhPhong'] ?? 'Nam';
            $buildingId = $_GET['MaToa'] ?? null;
            $floorInput = $_GET['Tang'] ?? null;
            if (!in_array($gender, ['Nam', 'Nữ'], true)
                || ($buildingId !== null && ($buildingId === '' || mb_strlen($buildingId) > 10))
                || ($floorInput !== null && (!preg_match('/^[1-9][0-9]{0,2}$/D', $floorInput) || (int) $floorInput > 255))) {
                $message = 'Bộ lọc phòng không hợp lệ. Vui lòng chọn lại khu, tòa và tầng.';
            } else {
                $floor = $floorInput === null ? null : (int) $floorInput;
            }
        }
        if ($message) {
            http_response_code(400);
        } else {
            try {
                $model = new Room($this->db);
                $buildings = $model->buildings($gender);
                $buildingId ??= $buildings[0]['MaToa'] ?? null;
                foreach ($buildings as $building) {
                    if ($building['MaToa'] === $buildingId) {
                        $selectedBuilding = $building;
                    }
                }
                if ($buildingId !== null && !$selectedBuilding) {
                    $message = 'Tòa đã chọn không thuộc khu này hoặc không có phòng.';
                    http_response_code(400);
                } elseif ($selectedBuilding) {
                    $floors = $model->floors($gender, $buildingId);
                    $floor ??= $floors[0] ?? null;
                    if (!in_array($floor, $floors, true)) {
                        $message = 'Tầng đã chọn không có trong tòa này.';
                        http_response_code(400);
                    } else {
                        $rooms = $model->rooms($gender, $buildingId, $floor);
                    }
                } elseif ($floor !== null) {
                    $message = 'Không có tòa và tầng phù hợp với bộ lọc.';
                    http_response_code(400);
                }
            } catch (PDOException $error) {
                http_response_code(500);
                $message = 'Chưa thể tải thông tin phòng. Vui lòng thử lại sau.';
                $rooms = [];
            }
        }
        require __DIR__ . '/../views/rooms/information.php';
    }
}
