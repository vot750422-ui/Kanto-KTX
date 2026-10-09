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
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/login.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>

    <main class="login-wrapper">
        <img class="login-logo" src="<?= htmlspecialchars(appUrl('assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Kanto KTX">
        <div class="login-box">
            <header class="login-header">
                <h1>Đăng nhập hệ thống</h1>
                <p>Nhập tài khoản của bạn để tiếp tục</p>
            </header>

            <?php if ($error): ?>
                <p class="alert-error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars(appUrl('index.php?action=login'), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        autocomplete="username"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required>
                </div>

                <button type="submit" class="btn-login">
                    Đăng nhập
                </button>
            </form>
        </div>
    </main>

</body>

</html>
