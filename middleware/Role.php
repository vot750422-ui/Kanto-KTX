<?php

require_once __DIR__ . '/Auth.php';

function dashboardForRole(string $role): ?string
{
    $dashboards = [
        'Sinh viên' => appUrl('views/student/Dashboard.php'),
        'Nhân viên quản lý KTX' => appUrl('views/nvql/Dashboard.php'),
        'Nhân viên kế toán' => appUrl('views/ketoan/Dashboard.php'),
        'Quản lý KTX' => appUrl('views/quanly/Dashboard.php'),
    ];
    return $dashboards[$role] ?? null;
}

// Liệt kê rõ các vai trò được phép; không tự cấp quyền cho vai trò quản lý.
function requireRole(string ...$allowedRoles): void
{
    $user = requireAuth();
    if (dashboardForRole($user['VaiTro']) === null || !in_array($user['VaiTro'], $allowedRoles, true)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Bạn không có quyền truy cập trang này.');
    }
}