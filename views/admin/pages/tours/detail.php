<?php
include_once PATH_VIEW_ADMIN . 'default/header.php';
include_once PATH_VIEW_ADMIN . 'default/sidebar.php';

$tour = $tour ?? null;
$allImages = $allImages ?? [];
$pricingOptions = $pricingOptions ?? [];
$itinerarySchedule = $itinerarySchedule ?? [];
$partnerServices = $partnerServices ?? [];
$departures = $departures ?? [];
$policies = $policies ?? [];

// Helper for price formatting
function formatPrice($price)
{
    if ($price >= 1000000000) {
        return round($price / 1000000000, ($price / 1000000000) >= 10 ? 0 : 1) . ' tỷ';
    } elseif ($price >= 1000000) {
        return round($price / 1000000, 1) . ' tr';
    } else {
        return number_format($price, 0, ',', '.') . 'đ';
    }
}

// Find main image from $allImages
$mainImage = BASE_URL . 'assets/admin/image/no-image.png';
foreach ($allImages as $img) {
    if (!empty($img['main'])) {
        $mainImage = $img['url'] ?? BASE_URL . 'assets/admin/image/no-image.png';
        break;
    }
}

// Prepare gallery URLs for lightbox
$galleryUrls = [];
foreach ($allImages as $img) {
    $url = $img['url'] ?? '';
    if ($url) {
        $galleryUrls[] = $url;
    }
}
if (empty($galleryUrls)) {
    $galleryUrls[] = $mainImage;
}
?>

<main class="dashboard tour-detail-page">
    <div class="dashboard-container">
        <!-- Modern Page Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-modern">
                        <a href="<?= BASE_URL_ADMIN ?>&action=/" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <a href="<?= BASE_URL_ADMIN ?>&action=tours" class="breadcrumb-link">
                            <i class="fas fa-route"></i>
                            <span>Quản lý Tour</span>
                        </a>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span class="breadcrumb-current">Chi tiết Tour</span>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-route title-icon"></i>
                            <?= htmlspecialchars($tour['name'] ?? 'Tên Tour') ?>
                        </h1>
                        <p class="page-subtitle"><?= htmlspecialchars($tour['category_name'] ?? 'Chưa có danh mục') ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <a href="<?= BASE_URL_ADMIN ?>&action=tours/edit&id=<?= $tour['id'] ?>" class="btn btn-modern btn-secondary">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL_ADMIN ?>&action=bookings/create&tour_id=<?= $tour['id'] ?>" class="btn btn-modern btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Tạo Booking
                    </a>
                </div>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-modern alert-success alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-modern alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= formatPrice($tour['base_price'] ?? 0) ?></div>
                        <div class="stat-label">Giá gốc</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($departures) ?></div>
                        <div class="stat-label">Lịch khởi hành</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= count($itinerarySchedule) ?></div>
                        <div class="stat-label">Số ngày</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">4.5</div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Main Column (Left) -->
            <div class="col-lg-8">
                <!-- Description Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Mô tả chi tiết
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tour['description'])): ?>
                            <div class="description-content">
                                <?= nl2br(htmlspecialchars($tour['description'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <h6>Chưa có mô tả</h6>
                                <p class="mb-0">Mô tả chi tiết cho tour này chưa được cập nhật.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Itinerary Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-map-signs text-success me-2"></i>
                            Lịch trình Tour
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($itinerarySchedule)): ?>
                            <div class="itinerary-timeline">
                                <?php foreach ($itinerarySchedule as $index => $item): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-day">
                                            <?= htmlspecialchars($item['day_label'] ?? 'N' . ($index + 1)) ?>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                <?= htmlspecialchars($item['title'] ?? 'Lịch trình ngày ' . ($index + 1)) ?>
                                            </h6>
                                            <p class="timeline-description">
                                                <?= htmlspecialchars($item['description'] ?? '') ?>
                                            </p>
                                            <?php if (!empty($item['time_start'])): ?>
                                                <div class="timeline-time">
                                                    <i class="fas fa-clock"></i>
                                                    <?= date('H:i', strtotime($item['time_start'])) ?> -
                                                    <?= date('H:i', strtotime($item['time_end'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                <h6>Chưa có lịch trình</h6>
                                <p class="mb-0">Lịch trình chi tiết cho tour này chưa được cập nhật.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Versions Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-info me-2"></i>
                            Lịch khởi hành
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($departures)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ngày khởi hành</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departures as $departure): ?>
                                            <tr>
                                                <td class="fw-medium">
                                                    <?= date('d/m/Y', strtotime($departure['departure_date'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $departure['status'] === 'open' ? 'success' : ($departure['status'] === 'full' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($departure['status'] ?? 'unknown') ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                <h6>Chưa có lịch khởi hành</h6>
                                <p class="mb-0">Hiện chưa có lịch khởi hành nào cho tour này.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tour Policies Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            Chính sách áp dụng
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($policies)): ?>
                            <div class="row">
                                <?php foreach ($policies as $policy): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-2 text-primary">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <?= htmlspecialchars($policy['name']) ?>
                                            </h6>
                                            <?php if (!empty($policy['description'])): ?>
                                                <p class="mb-0 small text-muted"><?= htmlspecialchars($policy['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                <h6>Chưa có chính sách</h6>
                                <p class="mb-0 small">Tour này chưa được gán chính sách nào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gallery Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-images text-primary me-2"></i>
                                Thư viện ảnh
                                <span class="badge bg-secondary ms-2"><?= count($galleryUrls) ?> ảnh</span>
                            </h5>
                            <?php if (count($galleryUrls) > 6): ?>
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="openLightbox(0)">
                                    <i class="fas fa-images me-2"></i>Xem tất cả
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($galleryUrls)): ?>
                            <div class="row g-3">
                                <?php
                                $displayImages = array_slice($galleryUrls, 0, 6);
                                foreach ($displayImages as $index => $url):
                                    $isLast = ($index === 5 && count($galleryUrls) > 6);
                                ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="gallery-item-wrapper position-relative overflow-hidden rounded shadow-sm" style="cursor: pointer; aspect-ratio: 16/9;" onclick="openLightbox(<?= $index ?>)">
                                            <img src="<?= $url ?>" alt="Tour Gallery Image <?= $index + 1 ?>" class="img-fluid w-100 h-100" style="object-fit: cover; transition: transform 0.3s;">

                                            <div class="gallery-overlay">
                                                <div class="gallery-overlay-content">
                                                    <i class="fas fa-search-plus"></i>
                                                    <span>Xem ảnh</span>
                                                </div>
                                            </div>

                                            <?php if ($isLast): ?>
                                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.6); z-index: 2;">
                                                    <div class="text-center text-white">
                                                        <span class="h4 fw-bold mb-0 d-block">+<?= count($galleryUrls) - 6 ?></span>
                                                        <span class="small">Xem thêm</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <div class="gallery-empty-state">
                                    <i class="fas fa-images fa-4x mb-3 text-muted"></i>
                                    <h6 class="text-muted">Thư viện trống</h6>
                                    <p class="text-muted mb-3">Chưa có hình ảnh nào trong thư viện.</p>
                                    <a href="<?= BASE_URL_ADMIN ?>&action=tours/edit&id=<?= $tour['id'] ?>#gallery" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-2"></i>
                                        Thêm hình ảnh
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lightbox Modal -->
                <div id="galleryLightbox" class="lightbox-overlay">
                    <div class="lightbox-content">
                        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>

                        <div class="lightbox-main">
                            <button class="lightbox-nav prev" onclick="moveSlide(-1)"><i class="fas fa-chevron-left"></i></button>
                            <img id="lightboxImage" src="" alt="Gallery Image">
                            <button class="lightbox-nav next" onclick="moveSlide(1)"><i class="fas fa-chevron-right"></i></button>
                        </div>

                        <div class="lightbox-caption">
                            <span id="currentIndex">1</span> / <span id="totalImages">0</span>
                        </div>

                        <!-- Thumbnails Strip -->
                        <div class="lightbox-thumbnails">
                            <div class="thumbnails-track" id="thumbnailsTrack">
                                <!-- Thumbnails injected via JS -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar (Right) -->
            <div class="col-lg-4">
                <!-- Main Image Widget -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-image text-primary me-2"></i>
                            Ảnh đại diện
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="main-image-container gallery-item-wrapper"
                            style="cursor: pointer; position: relative;"
                            onclick="openMainImageLightbox(this)">
                            <img src="<?= $mainImage ?>" alt="Tour Main Image" class="img-fluid rounded" style="width: 100%; height: auto; object-fit: cover;">
                            <div class="gallery-overlay rounded">
                                <div class="gallery-overlay-content">
                                    <i class="fas fa-search-plus"></i>
                                    <span>Xem ảnh</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Card -->
                <?php if (!empty($tour['supplier_id'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-building text-info me-2"></i>
                                Nhà cung cấp
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Load supplier info
                            $supplierModel = new Supplier();
                            $supplier = $supplierModel->find('*', 'id = :id', ['id' => $tour['supplier_id']]);
                            ?>
                            <?php if ($supplier): ?>
                                <div class="supplier-info">
                                    <h6 class="mb-3 text-primary">
                                        <i class="fas fa-handshake me-2"></i>
                                        <?= htmlspecialchars($supplier['name']) ?>
                                    </h6>

                                    <?php if (!empty($supplier['type'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-2"></i>Loại:
                                            </small>
                                            <span class="badge bg-info"><?= htmlspecialchars($supplier['type']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($supplier['contact_person'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-2"></i>Người liên hệ:
                                            </small>
                                            <span><?= htmlspecialchars($supplier['contact_person']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($supplier['phone'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-2"></i>Điện thoại:
                                            </small>
                                            <a href="tel:<?= $supplier['phone'] ?>"><?= htmlspecialchars($supplier['phone']) ?></a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($supplier['email'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-2"></i>Email:
                                            </small>
                                            <a href="mailto:<?= $supplier['email'] ?>"><?= htmlspecialchars($supplier['email']) ?></a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($supplier['address'])): ?>
                                        <div class="mb-0">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:
                                            </small>
                                            <span><?= htmlspecialchars($supplier['address']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <p class="mb-0 small">Không tìm thấy thông tin nhà cung cấp</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>



                <style>
                    /* Gallery Styles */
                    .gallery-item-wrapper {
                        transition: transform 0.3s ease;
                    }

                    .gallery-item-wrapper:hover {
                        transform: translateY(-2px);
                    }

                    .gallery-item-wrapper:hover img {
                        transform: scale(1.05);
                    }

                    .gallery-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                        z-index: 1;
                    }

                    .gallery-item-wrapper:hover .gallery-overlay {
                        opacity: 1;
                    }

                    .gallery-overlay-content {
                        text-align: center;
                        color: white;
                    }

                    .gallery-overlay-content i {
                        font-size: 24px;
                        margin-bottom: 5px;
                        display: block;
                    }

                    /* Lightbox Styles */
                    .lightbox-overlay {
                        display: none;
                        position: fixed;
                        z-index: 9999;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.95);
                        backdrop-filter: blur(5px);
                    }

                    .lightbox-content {
                        position: relative;
                        width: 100%;
                        height: 100%;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                    }

                    .lightbox-main {
                        position: relative;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        height: 80%;
                        padding: 0 50px;
                    }

                    #lightboxImage {
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        border-radius: 4px;
                        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5);
                    }

                    .lightbox-close {
                        position: absolute;
                        top: 20px;
                        right: 30px;
                        color: #fff;
                        background: none;
                        border: none;
                        font-size: 40px;
                        cursor: pointer;
                        z-index: 10001;
                        padding: 0;
                        line-height: 1;
                        width: 40px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: color 0.2s;
                    }

                    .lightbox-close:hover {
                        color: #dc3545;
                    }

                    .lightbox-nav {
                        background: rgba(255, 255, 255, 0.1);
                        border: none;
                        color: white;
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        font-size: 20px;
                        cursor: pointer;
                        transition: all 0.2s;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: absolute;
                    }

                    .lightbox-nav:hover {
                        background: rgba(255, 255, 255, 0.2);
                        transform: scale(1.1);
                    }

                    .lightbox-nav.prev {
                        left: 20px;
                    }

                    .lightbox-nav.next {
                        right: 20px;
                    }

                    .lightbox-caption {
                        color: #ccc;
                        margin-top: 10px;
                        font-family: monospace;
                        font-size: 14px;
                    }

                    .lightbox-thumbnails {
                        height: 80px;
                        width: 100%;
                        margin-top: 20px;
                        overflow-x: auto;
                        display: flex;
                        justify-content: center;
                        padding: 10px 0;
                        background: rgba(0, 0, 0, 0.3);
                    }

                    .thumbnails-track {
                        display: flex;
                        gap: 10px;
                        padding: 0 20px;
                    }

                    .lightbox-thumb {
                        height: 60px;
                        width: 90px;
                        object-fit: cover;
                        border-radius: 4px;
                        cursor: pointer;
                        opacity: 0.5;
                        transition: all 0.2s;
                        border: 2px solid transparent;
                    }

                    .lightbox-thumb.active {
                        opacity: 1;
                        border-color: #0d6efd;
                    }

                    .lightbox-thumb:hover {
                        opacity: 0.8;
                    }

                    @media (max-width: 768px) {
                        .lightbox-main {
                            padding: 0;
                        }

                        .lightbox-nav {
                            width: 40px;
                            height: 40px;
                            font-size: 16px;
                        }

                        .lightbox-nav.prev {
                            left: 10px;
                        }

                        .lightbox-nav.next {
                            right: 10px;
                        }

                        .lightbox-thumbnails {
                            display: none;
                        }

                        /* Hide thumbs on mobile */
                    }
                </style>

                <script>
                    // Prepare Gallery Data
                    const galleryImages = <?= json_encode($galleryUrls) ?>;

                    let currentImageIndex = 0;

                    function openLightbox(index) {
                        if (galleryImages.length === 0) return;
                        currentImageIndex = index;

                        const lightbox = document.getElementById('galleryLightbox');
                        const totalImages = document.getElementById('totalImages');
                        const thumbnailsTrack = document.getElementById('thumbnailsTrack');

                        // Render Thumbnails
                        thumbnailsTrack.innerHTML = '';
                        galleryImages.forEach((src, idx) => {
                            const thumb = document.createElement('img');
                            thumb.src = src;
                            thumb.className = `lightbox-thumb ${idx === index ? 'active' : ''}`;
                            thumb.onclick = () => showImage(idx);
                            thumbnailsTrack.appendChild(thumb);
                        });

                        totalImages.innerText = galleryImages.length;
                        showImage(index);

                        lightbox.style.display = 'block';
                        document.body.style.overflow = 'hidden'; // Prevent background scrolling

                        // Keyboard Nav
                        document.addEventListener('keydown', handleKeyboardNav);
                    }

                    function closeLightbox() {
                        const lightbox = document.getElementById('galleryLightbox');
                        if (lightbox) lightbox.style.display = 'none';
                        document.body.style.overflow = '';
                        document.removeEventListener('keydown', handleKeyboardNav);
                    }

                    function showImage(index) {
                        if (index >= galleryImages.length) index = 0;
                        if (index < 0) index = galleryImages.length - 1;

                        currentImageIndex = index;

                        const imgEnd = document.getElementById('lightboxImage');
                        const currentIndexEl = document.getElementById('currentIndex');

                        // Update Image
                        imgEnd.style.opacity = '0';
                        setTimeout(() => {
                            imgEnd.src = galleryImages[index];
                            imgEnd.style.opacity = '1';
                        }, 200);

                        // Update Counter
                        currentIndexEl.innerText = index + 1;

                        // Update Thumbnails
                        document.querySelectorAll('.lightbox-thumb').forEach((thumb, idx) => {
                            if (idx === index) {
                                thumb.classList.add('active');
                                thumb.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'nearest',
                                    inline: 'center'
                                });
                            } else {
                                thumb.classList.remove('active');
                            }
                        });
                    }

                    function moveSlide(step) {
                        showImage(currentImageIndex + step);
                    }

                    function handleKeyboardNav(e) {
                        if (e.key === 'Escape') closeLightbox();
                        if (e.key === 'ArrowRight') moveSlide(1);
                        if (e.key === 'ArrowLeft') moveSlide(-1);
                    }

                    // Close on click outside
                    const lightboxEl = document.getElementById('galleryLightbox');
                    if (lightboxEl) {
                        lightboxEl.addEventListener('click', (e) => {
                            if (e.target.id === 'galleryLightbox' || e.target.classList.contains('lightbox-content')) {
                                closeLightbox();
                            }
                        });
                    }

                    function openMainImageLightbox(element) {
                        const img = element.querySelector('img');
                        if (!img) return;

                        const src = img.getAttribute('src');

                        // Check against galleryImages variable defined above
                        if (!galleryImages || !galleryImages.length) {
                            console.warn('No gallery data available');
                            return;
                        }

                        // Try to find index
                        let index = -1;

                        for (let i = 0; i < galleryImages.length; i++) {
                            // Compare src with gallery image URL. 
                            // Using includes to be safe about relative/absolute path differences
                            if (src.includes(galleryImages[i]) || galleryImages[i].includes(src)) {
                                index = i;
                                break;
                            }
                        }

                        if (index !== -1) {
                            openLightbox(index);
                        } else {
                            // Fallback: If main image is not in gallery list, just open the first gallery image
                            // This allows user to enter the lightbox view.
                            openLightbox(0);
                        }
                    }

                    function showAllImages() {
                        const galleryItems = document.querySelectorAll('.gallery-item-wrapper');
                        galleryItems.forEach(item => item.style.display = 'block');
                    }
                </script>

                <?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>