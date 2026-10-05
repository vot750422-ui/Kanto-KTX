<?php

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng nhập</title>
</head>

<body>

    <h2>Đăng nhập hệ thống</h2>

    <?php if ($error): ?>
        <p style="color: red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="/Kanto-KTX/index.php?action=login">

        <div>
            <label>Tên đăng nhập</label>
            <input
                type="text"
                name="username"
                required>
        </div>

        <br>

        <div>
            <label>Mật khẩu</label>
            <input
                type="password"
                name="password"
                required>
        </div>

        <br>

        <button type="submit">
            Đăng nhập
        </button>

    </form>

</body>

</html>