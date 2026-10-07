<header class="site-header">
    <a class="site-brand" href="/Kanto-KTX/index.php" aria-label="Kanto — Trang chủ">
        <img src="/Kanto-KTX/assets/images/logo.png" alt="" class="site-logo">
        <span class="site-brand-text">
            <strong>KANTO</strong>
            <span>HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ</span>
        </span>
    </a>

    <nav class="site-nav" aria-label="Điều hướng chính">
        <a href="/Kanto-KTX/index.php"<?= empty($_GET['action']) ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v11H3Z"/><path d="M9 21v-8h6v8"/></svg>Trang chủ</a>
        <a href="/Kanto-KTX/index.php?action=register"<?= ($_GET['action'] ?? '') === 'register' ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/></svg>Đăng ký</a>
        <button type="button" disabled title="Thông tin phòng đang được xây dựng"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="1"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M10 21v-6h4v6"/></svg>Thông tin phòng</button>
        <button type="button" disabled title="Hướng dẫn đang được xây dựng"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5C8 2 4 3 2 4v15c4-2 7-1 10 1 3-2 6-3 10-1V4c-2-1-6-2-10 1Zm0 0v15"/></svg>Hướng dẫn</button>
        <a class="site-login" href="/Kanto-KTX/index.php?action=login"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h6v18h-6M3 12h12M10 7l5 5-5 5"/></svg>Đăng nhập</a>
    </nav>
</header>
