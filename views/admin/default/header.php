<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/admin-dashboard.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/sidebar.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/tours.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/tours-modern.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/tour_logs.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/components.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/tour-form-modern.css">
    <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/tour-version-form.css">
    <!-- <link rel="stylesheet" href="<?= BASE_ASSETS_ADMIN ?>css/style.css"> -->

    <style>
        /* Slide-in toast from right with keyframe fallback and stronger show rules */
        .toast-slide {
            transform: translateX(120%);
            opacity: 0;
            min-width: 280px;
            max-width: 520px;
            border-radius: .6rem;
            will-change: transform, opacity;
        }

        /* When Bootstrap toggles the .show class we animate into place. Use !important to override other rules. */
        .toast-slide.show {
            transform: translateX(0) !important;
            opacity: 1 !important;
            transition: transform .45s cubic-bezier(.2, .9, .2, 1), opacity .35s ease;
        }

        /* Keyframe fallback for environments where class toggling may be delayed */
        @keyframes slideInFromRight {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-slide.animate-in {
            animation: slideInFromRight .45s cubic-bezier(.2, .9, .2, 1) forwards;
        }

        /* Slight elevation */
        #toast-container .toast {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        /* Custom coloring */
        .toast-success-custom {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            color: #fff;
        }

        .toast-error-custom {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            color: #fff;
        }

        /* Ensure container doesn't clip the toast */
        [aria-live][aria-atomic] {
            overflow: visible;
        }

        /* Arrange toasts right-aligned and keep them clickable */
        #toast-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: .5rem;
            pointer-events: auto;
        }

        #toast-container .toast {
            margin-right: 1rem;
            pointer-events: auto;
        }

        /* Make toast body take remaining space and keep close button small */
        #toast-container .toast .toast-body {
            flex: 1 1 auto;
            padding-right: .5rem;
        }

        #toast-container .toast .btn-close {
            flex: 0 0 auto;
            width: 30px;
            height: 18px;
            padding: .25rem;
            margin: 0 !important;
        }

        /* Ensure vertical centering inside toast */
        #toast-container .toast .d-flex {
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="admin-layout d-flex">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <div class="header-right">
                    <div class="user-profile-section d-flex align-items-center gap-3">
                        <?php
                        // safe user vars
                        $loggedInUser = $_SESSION['user'] ?? null;
                        $avatarUrl = !empty($loggedInUser['avatar']) ? BASE_ASSETS_UPLOADS . $loggedInUser['avatar'] : 'https://ui-avatars.com/api/?name=' . urlencode($loggedInUser['full_name'] ?? 'User') . '&background=0D6EFD&color=fff&size=40';
                        $userName = $loggedInUser['full_name'] ?? 'Guest';
                        $userRole = $loggedInUser['role'] ?? 'user';
                        $roleLabel = match ((string)$userRole) {
                            'admin' => 'Quản trị viên',
                            'guide' => 'Hướng dẫn viên',
                            'supplier' => 'Nhà cung cấp',
                            default => ucfirst((string)$userRole)
                        };
                        ?>

                        <!-- Avatar -->
                        <div class="user-avatar">
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars((string)$userName) ?>" class="avatar-img">
                        </div>

                        <!-- User Info -->
                        <div class="user-info d-none d-md-block">
                            <p class="user-name mb-0"><?= htmlspecialchars((string)$userName) ?></p>
                            <p class="user-role small text-muted mb-0"><?= htmlspecialchars($roleLabel) ?></p>
                        </div>

                        <!-- Dropdown Toggle -->
                        <div class="dropdown-container ms-auto">
                            <button class="dropdown-btn btn btn-sm btn-outline-secondary" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL_ADMIN ?>&action=account">
                                        <i class="fas fa-user"></i> Thông tin tài khoản
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= BASE_URL_ADMIN ?>&action=logout">
                                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php // Flash messages (show once and clear) - render as Bootstrap toasts in top-right corner 
        ?>
        <?php if (!empty($_SESSION['success']) || !empty($_SESSION['error'])): ?>
            <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 1rem; right: 0; z-index: 1080; pointer-events: none;">
                <div id="toast-container" class="toast-container p-0">
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="toast toast-slide toast-success-custom align-items-center border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?= htmlspecialchars((string)$_SESSION['success']) ?>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="toast toast-slide toast-error-custom align-items-center border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?= htmlspecialchars((string)$_SESSION['error']) ?>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                (function() {
                    // Initialize and show any toasts in the container
                    document.addEventListener('DOMContentLoaded', function() {
                        var container = document.getElementById('toast-container');
                        if (!container) return;
                        var toasts = container.querySelectorAll('.toast');
                        toasts.forEach(function(el) {
                            try {
                                var t = new bootstrap.Toast(el);
                                t.show();
                                // Ensure our slide animation runs even if Bootstrap doesn't toggle classes quickly
                                el.classList.add('animate-in');
                                setTimeout(function() {
                                    el.classList.add('show');
                                }, 10);
                            } catch (e) {
                                // ignore if bootstrap not available
                                console.warn('Toast init failed', e);
                                // fallback: add classes so CSS animation runs
                                el.classList.add('animate-in');
                                el.classList.add('show');
                            }
                        });
                    });
                })();
            </script>
            <?php unset($_SESSION['success'], $_SESSION['error']); ?>
        <?php endif; ?>