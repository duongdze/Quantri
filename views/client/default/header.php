<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'VietTour – Khám phá Việt Nam' ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'VietTour – Nền tảng đặt tour du lịch trực tuyến hàng đầu Việt Nam') ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'VietTour – Khám phá Việt Nam') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'VietTour – Nền tảng đặt tour du lịch trực tuyến hàng đầu Việt Nam') ?>">
    <meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? (BASE_ASSETS_CLIENT . 'img/og-default.jpg')) ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_ASSETS_CLIENT ?>css/style.css">
</head>
<body>

<?php $curAction = $_GET['action'] ?? '/'; ?>

<!-- NAVBAR -->
<nav class="vt-navbar">
    <div class="container">
        <!-- Logo -->
        <a href="<?= BASE_URL ?>" class="vt-logo">
            <div class="logo-icon"><i class="fas fa-globe-asia"></i></div>
            VietTour
        </a>

        <!-- Hamburger (mobile) -->
        <button class="vt-hamburger" id="vtHamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav Links -->
        <ul class="vt-nav-links" id="vtNavLinks">
            <li><a href="<?= BASE_URL ?>" class="<?= in_array($curAction, ['/', 'home']) ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Trang chủ
            </a></li>
            <li><a href="<?= BASE_URL ?>?action=tour-list" class="<?= $curAction === 'tour-list' ? 'active' : '' ?>">
                <i class="fas fa-map-marked-alt"></i> Tours
            </a></li>
            <li><a href="<?= BASE_URL ?>?action=about" class="<?= $curAction === 'about' ? 'active' : '' ?>">
                <i class="fas fa-info-circle"></i> Về chúng tôi
            </a></li>
            <li><a href="<?= BASE_URL ?>?action=blog" class="<?= str_starts_with($curAction, 'blog') ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i> Blog
            </a></li>
            <li><a href="<?= BASE_URL ?>?action=contact" class="<?= $curAction === 'contact' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Liên hệ
            </a></li>
        </ul>


        <!-- Auth -->
        <div class="vt-nav-auth">
            <?php if (!empty($_SESSION['user'])): ?>
                <div class="vt-user-menu">
                    <button class="vt-user-btn" id="vtUserBtn">
                        <div class="vt-avatar">
                            <?= strtoupper(mb_substr($_SESSION['user']['full_name'], 0, 1)) ?>
                        </div>
                        <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
                        <i class="fas fa-chevron-down" style="font-size:.70rem"></i>
                    </button>
                    <div class="vt-dropdown" id="vtDropdown">
                        <?php if (in_array($_SESSION['user']['role'], ['admin', 'guide'])): ?>
                        <a href="<?= BASE_URL_ADMIN ?>">
                            <i class="fas fa-tachometer-alt"></i> Quản trị
                        </a>
                        <hr>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>?action=my-account">
                            <i class="fas fa-user-circle"></i> Tài khoản của tôi
                        </a>
                        <a href="<?= BASE_URL ?>?action=my-bookings">
                            <i class="fas fa-ticket-alt"></i> Đơn hàng của tôi
                        </a>
                        <hr>
                        <a href="<?= BASE_URL ?>?action=logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>?action=login" class="btn-login">Đăng nhập</a>
                <a href="<?= BASE_URL ?>?action=register" class="btn-register">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
// Hamburger toggle
const ham = document.getElementById('vtHamburger');
const nav = document.getElementById('vtNavLinks');
ham && ham.addEventListener('click', () => {
    ham.classList.toggle('open');
    nav.classList.toggle('open');
});

// User dropdown toggle
const btn = document.getElementById('vtUserBtn');
const drop = document.getElementById('vtDropdown');
btn && btn.addEventListener('click', (e) => {
    e.stopPropagation();
    drop.classList.toggle('show');
});
document.addEventListener('click', () => drop && drop.classList.remove('show'));
</script>

<main>