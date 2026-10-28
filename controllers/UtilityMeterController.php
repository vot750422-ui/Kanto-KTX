<?php

require_once __DIR__ . '/../models/UtilityMeter.php';
require_once __DIR__ . '/../models/StaffDashboard.php';

class UtilityMeterController
{
    public function __construct(private PDO $db) {}

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $roomInput = $_POST['room_id'] ?? $_GET['room_id'] ?? '';
        $roomId = is_string($roomInput) ? trim($roomInput) : '';
        $electricity = is_string($_POST['electricity'] ?? null) ? trim($_POST['electricity']) : '';
        $water = is_string($_POST['water'] ?? null) ? trim($_POST['water']) : '';
        $errors = [];
        $model = new UtilityMeter($this->db);

        if ($method === 'POST') {
            // Kiểm tra chỉ số từ form trước khi giao Model ghi nhận theo tháng hiện tại.
            if (preg_match('/^[A-Za-z0-9_-]{1,10}$/D', $roomId) !== 1) {
                $errors[] = 'Phòng không hợp lệ.';
            }
            if (preg_match('/^\d{1,10}$/D', $electricity) !== 1 || (int) $electricity > 2147483647) {
                $errors[] = 'Chỉ số điện phải là số nguyên không âm hợp lệ.';
            }
            if (preg_match('/^\d{1,10}$/D', $water) !== 1 || (int) $water > 2147483647) {
                $errors[] = 'Chỉ số nước phải là số nguyên không âm hợp lệ.';
            }

            if (!$errors) {
                try {
                    $model->record(
                        $roomId,
                        (int) $_SESSION['user']['MaTK'],
                        (int) $electricity,
                        (int) $water
                    );
                    $_SESSION['utility_meter_notice'] = 'Lưu thành công.';
                    header(
                        'Location: ' . appUrl('index.php?action=utility-meters&room_id=' . rawurlencode($roomId)),
                        true,
                        303
                    );
                    return;
                } catch (DomainException $error) {
                    $errors[] = $error->getMessage();
                } catch (Throwable $error) {
                    http_response_code(500);
                    error_log('UC07 ghi chỉ số thất bại (' . get_class($error) . ').');
                    $errors[] = 'Chưa thể lưu chỉ số. Vui lòng thử lại sau.';
                }
            }
        } elseif ($method !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }

        // Nạp lại danh sách phòng có người ở để form luôn hiển thị trạng thái mới nhất.
        $rooms = $model->occupiedRooms();
        $selected = null;
        foreach ($rooms as $room) {
            if ($room['MaPhong'] === $roomId) {
                $selected = $room;
                break;
            }
        }

        $notice = $_SESSION['utility_meter_notice'] ?? null;
        unset($_SESSION['utility_meter_notice']);
        $displayName = (new StaffDashboard($this->db))->nameForAccount((int) $_SESSION['user']['MaTK'])
            ?? $_SESSION['user']['TenDangNhap'];

        require __DIR__ . '/../views/utilities/meters.php';
    }
}
