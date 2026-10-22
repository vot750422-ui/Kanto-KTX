<?php
require_once __DIR__ . '/Room.php';
require_once __DIR__ . '/../config/connection.php';

/** Only the same public fields and aggregate counts as UC15 leave this adapter. */
class ChatbotRoomSource
{
    public function __construct(private ?PDO $db = null) {}

    public function snapshot(): array
    {
        $db = $this->db ?? createDatabaseConnection();
        $ownsTransaction = !$db->inTransaction();
        if ($ownsTransaction) {
            $db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $db->exec('START TRANSACTION READ ONLY');
        }
        try {
            $model = new Room($db);
            $rows = $model->allPublicRooms();
            $rooms = $totals = [];
            foreach ($rows as $row) {
                $available = max(0, (int)$row['ConCho']);
                $key = $row['MaToa'];
                $totals[$key] ??= ['toa' => $row['TenToa'], 'khu' => $row['GioiTinhPhong'], 'so_phong' => 0, 'phong_con_cho' => 0, 'suc_chua' => 0, 'dang_o' => 0, 'dang_giu' => 0, 'con_cho' => 0];
                $totals[$key]['so_phong']++;
                $totals[$key]['phong_con_cho'] += (int)($available > 0);
                foreach (['suc_chua'=>'SucChua', 'dang_o'=>'DangO', 'dang_giu'=>'DangGiu'] as $target=>$field) {
                    $totals[$key][$target] += (int)$row[$field];
                }
                $totals[$key]['con_cho'] += $available;
                // Compact positional rows keep the full 200-room snapshot small.
                $rooms[] = [$row['TenToa'], $row['SoPhong'], (int)$row['Tang'], $row['GioiTinhPhong'], (int)$row['SucChua'], (int)$row['DangO'], (int)$row['DangGiu'], $available];
            }
            if ($ownsTransaction) { $db->commit(); }
            return [
                'updated_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format(DATE_ATOM),
                'summary' => array_values($totals),
                'columns' => ['toa','phong','tang','khu','suc_chua','dang_o','dang_giu','con_cho'],
                'rooms' => array_slice($rooms, 0, 300),
                'total_rooms' => count($rooms),
                'truncated' => count($rooms) > 300,
            ];
        } catch (Throwable $error) {
            if ($ownsTransaction && $db->inTransaction()) { $db->rollBack(); }
            throw $error;
        }
    }
}
