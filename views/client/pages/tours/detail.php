<?php include_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<!-- Hero Section -->
<div class="tour-hero position-relative">
    <?php 
    $heroImage = !empty($images) ? BASE_ASSETS_UPLOADS . $images[0]['image_url'] : 'https://via.placeholder.com/1920x600';
    // Find main image if set
    foreach($images as $img) {
        if (!empty($img['main_img'])) {
            $heroImage = BASE_ASSETS_UPLOADS . $img['image_url'];
            break;
        }
    }
    ?>
    <img src="<?= $heroImage ?>" alt="<?= htmlspecialchars($tour['name']) ?>" class="w-100 h-100 object-fit-cover">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.8));"></div>
    <div class="position-absolute top-50 start-50 translate-middle text-center w-75 hero-content">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb justify-content-center mb-4">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-white opacity-75">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>?action=tour-list" class="text-white opacity-75">Tour</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($tour['name']) ?></li>
            </ol>
        </nav>
        
        <span class="badge bg-primary px-3 py-2 mb-3 rounded-pill text-uppercase letter-spacing-1"><?= htmlspecialchars($tour['category_name'] ?? 'General') ?></span>
        <h1 class="display-4 fw-bold mb-3 text-white" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.8);"><?= htmlspecialchars($tour['name']) ?></h1>
        <?php if (!empty($tour['subtitle'])): ?>
            <p class="lead text-white opacity-100" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.8);"><?= htmlspecialchars($tour['subtitle']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container my-5" style="margin-top: -60px; position: relative; z-index: 10;">
    <div class="row">
        <!-- Left Content -->
        <div class="col-lg-8">
            <!-- Overview Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="info-card-box text-center h-100 hover-lift">
                        <div class="info-card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold">Thời gian</h6>
                        <p class="h5 mb-0 fw-bold"><?= htmlspecialchars($tour['duration_days'] ?? 'N/A') ?> Ngày</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-box text-center h-100 hover-lift">
                        <div class="info-card-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold">Quy mô</h6>
                        <p class="h5 mb-0 fw-bold"><?= htmlspecialchars($tour['max_participants'] ?? 'N/A') ?> chỗ</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-box text-center h-100 hover-lift">
                        <div class="info-card-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold">Khởi hành</h6>
                        <?php 
                        $departureText = 'Liên hệ';
                        if (!empty($departures)) {
                            // Map over to ensure dates are valid and filter for future ones
                            $futureDate = null;
                            foreach($departures as $d) {
                                if (strtotime($d['departure_date']) >= strtotime(date('Y-m-d'))) {
                                    $futureDate = date('d/m/Y', strtotime($d['departure_date']));
                                    break;
                                }
                            }
                            if ($futureDate) $departureText = $futureDate;
                        }
                        ?>
                        <p class="h5 mb-0 fw-bold"><?= $departureText ?></p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <section class="mb-5">
                <h3 class="mb-4 text-primary">Giới thiệu tour</h3>
                <div class="bg-white p-4 rounded shadow-soft">
                    <div class="tour-description text-justify">
                        <?= nl2br($tour['description']) ?>
                    </div>
                </div>
            </section>

            <!-- Gallery -->
            <?php if (!empty($images)): ?>
            <section class="mb-5">
                <h3 class="mb-4 text-primary">Thư viện ảnh</h3>
                <div class="row g-3">
                    <?php foreach(array_slice($images, 0, 6) as $img): ?>
                        <div class="col-md-4 col-6">
                            <img src="<?= BASE_ASSETS_UPLOADS . $img['image_url'] ?>" class="gallery-img w-100 shadow-sm" alt="Tour Image">
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Itinerary Timeline -->
            <?php if (!empty($itinerarySchedule)): ?>
            <!-- Itinerary Accordion -->
            <section class="mb-5">
                <div class="d-flex align-items-center mb-4">
                    <h3 class="text-primary fw-bold mb-0"><i class="fas fa-map-marked-alt me-2"></i>Lịch trình chi tiết</h3>
                    <span class="badge bg-light text-primary ms-3 border"><?= count($itinerarySchedule) ?> Ngày</span>
                </div>
                
                <div class="accordion" id="itineraryAccordion">
                    <?php foreach($itinerarySchedule as $index => $item): ?>
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded overflow-hidden">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> fw-bold py-3 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                    <span class="badge bg-primary me-3 rounded-pill px-3 py-2">Ngày <?= $item['day_number'] ?></span>
                                    <span><?= htmlspecialchars($item['title'] ?? $item['day_label']) ?></span>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#itineraryAccordion">
                                <div class="accordion-body bg-light p-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 d-none d-md-block">
                                            <i class="fas fa-route fa-2x text-primary opacity-50"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-md-4">
                                            <div class="text-muted text-justify">
                                                <?php 
                                                $content = trim($item['description'] ?? $item['activities'] ?? '');
                                                if ($content === '') {
                                                    echo '<div class="alert alert-light border border-dashed text-center text-muted mb-0"><i class="fas fa-pencil-alt me-2"></i>Nội dung chi tiết đang được cập nhật...</div>';
                                                } else {
                                                    echo nl2br($content);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <style>
                .accordion-button:not(.collapsed) {
                    color: #0d6efd;
                    background-color: #fff;
                    box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
                }
                .accordion-button:focus {
                    box-shadow: none;
                    border-color: rgba(0,0,0,.125);
                }
                .accordion-button::after {
                    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230d6efd'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
                }
            </style>
            <?php endif; ?>
            
            <!-- Calendar Section (Moved from Sidebar) -->
            <?php if (!empty($departures)): ?>
            <section id="tour-calendar-section" class="mb-5">
                <h3 class="mb-4 text-primary">Chọn ngày khởi hành</h3>
                <div class="bg-white p-4 rounded shadow-soft">
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3"><i class="far fa-calendar-alt me-2"></i>Lịch khởi hành</label>
                        
                        <!-- Calendar Container -->
                        <div class="calendar-container shadow-sm border rounded overflow-hidden d-flex flex-column">
                            <!-- Calendar Grid Area -->
                            <div class="calendar-content flex-grow-1 p-3 bg-white">
                                <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" id="prevMonthBtn"><i class="fas fa-chevron-left"></i></button>
                                    <h5 class="mb-0 text-primary fw-bold text-uppercase" id="currentMonthLabel">THÁNG --/----</h5>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle" id="nextMonthBtn"><i class="fas fa-chevron-right"></i></button>
                                </div>
                                
                                <div class="calendar-weekdays d-grid mb-2">
                                    <div>CN</div><div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div>
                                </div>

                                <div class="calendar-days d-grid" id="calendarDays">
                                    <!-- Days will be generated here -->
                                </div>
                            </div>
                        </div>
                        <div id="selectedDateDisplay" class="mt-2 text-success fw-bold small"></div>
                    </div>
                </div>
            </section>

            <style>
                .calendar-container {
                    background: #fff;
                }
                .calendar-content {
                    min-width: 0;
                    overflow: hidden;
                }
                .calendar-weekdays {
                    grid-template-columns: repeat(7, 1fr);
                    text-align: center;
                    font-weight: 700;
                    font-size: 0.75rem;
                    color: #333;
                    margin-bottom: 0.5rem;
                }
                .calendar-days {
                    grid-template-columns: repeat(7, 1fr);
                    gap: 4px;
                }
                .day-cell {
                    aspect-ratio: 1; /* Keep square */
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    border-radius: 6px;
                    font-size: 0.85rem;
                    color: #333;
                    cursor: default;
                }
                .day-cell.inactive {
                    color: #e9ecef;
                }
                .day-cell.has-departure {
                    cursor: pointer;
                    background-color: #fff;
                    border: 1px solid #dee2e6;
                    font-weight: 600;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                }
                .day-cell.has-departure:hover {
                    border-color: #0d6efd;
                    background-color: #e7f1ff;
                    z-index: 1;
                }
                .day-cell.selected {
                    background-color: #0d6efd !important;
                    color: white !important;
                    border-color: #0d6efd !important;
                    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
                }
                .day-price {
                    font-size: 0.6rem;
                    margin-top: 2px;
                    color: #dc3545;
                    font-weight: 500;
                    line-height: 1;
                }
                .day-cell.selected .day-price {
                    color: #ffdede;
                }
                .day-cell.disabled-too-soon {
                    background-color: #f8f9fa;
                    border: 1px dashed #dee2e6;
                    opacity: 0.7;
                    cursor: not-allowed;
                    color: #adb5bd;
                }
                .day-cell.disabled-too-soon .day-price {
                    color: #adb5bd;
                    text-decoration: line-through;
                }
                .day-cell.disabled-too-soon:hover {
                    border-color: #dee2e6;
                    background-color: #f8f9fa;
                }
                .day-cell.sold-out {
                    background-color: #f1f3f5;
                    border: 1px solid #dee2e6;
                    opacity: 0.8;
                    cursor: not-allowed;
                    color: #adb5bd;
                    position: relative;
                }
                .day-cell.sold-out .day-price {
                    color: #ef4444;
                    font-weight: 700;
                    font-size: 0.55rem;
                }
                .day-cell.sold-out:hover {
                    background-color: #f1f3f5;
                    border-color: #dee2e6;
                }
            </style>

            <script>
                // Pass PHP data to JS
                const departures = <?= json_encode(array_map(function($d) use ($tour) {
                    return [
                        'id' => $d['id'],
                        'date' => $d['departure_date'],
                        'price' => $d['price_adult'] > 0 ? $d['price_adult'] : $tour['base_price'],
                        'seats_available' => (in_array($d['status'], ['closed', 'full'])) ? 0 : ($d['max_seats'] - ($d['booked_seats'] ?? 0))
                    ];
                }, $departures)) ?>;
                
                document.addEventListener('DOMContentLoaded', function() {
                    initCalendar();
                });

                let currentMonth = new Date();
                currentMonth.setDate(1); 
                
                const now = new Date();
                now.setDate(1);
                now.setHours(0,0,0,0);

                if (departures.length > 0) {
                    const firstFutureDep = departures.find(d => new Date(d.date) >= now);
                    if (firstFutureDep) {
                         const firstDepDate = new Date(firstFutureDep.date);
                         firstDepDate.setDate(1);
                         if (firstDepDate > now) {
                            currentMonth = firstDepDate;
                         }
                    }
                }

                function initCalendar() {
                    renderCalendar(currentMonth);
                    updateNavButtons();
                }

                function updateNavButtons() {
                    const prevBtn = document.getElementById('prevMonthBtn');
                    if (currentMonth <= now) {
                        prevBtn.disabled = true;
                        prevBtn.style.opacity = '0.3';
                        prevBtn.style.cursor = 'not-allowed';
                    } else {
                        prevBtn.disabled = false;
                        prevBtn.style.opacity = '1';
                        prevBtn.style.cursor = 'pointer';
                    }
                }

                function renderCalendar(date) {
                    const year = date.getFullYear();
                    const month = date.getMonth();
                    
                    document.getElementById('currentMonthLabel').innerText = `THÁNG ${month + 1}/${year}`;
                    
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const daysInMonth = lastDay.getDate();
                    const startDayOfWeek = firstDay.getDay(); // 0 is Sunday
                    
                    const calendarDaysEl = document.getElementById('calendarDays');
                    calendarDaysEl.innerHTML = '';
                    
                    document.getElementById('prevMonthBtn').onclick = () => {
                        const prevDate = new Date(currentMonth);
                        prevDate.setMonth(prevDate.getMonth() - 1);
                        
                        if (prevDate >= now) {
                            currentMonth.setMonth(currentMonth.getMonth() - 1);
                            renderCalendar(currentMonth);
                            updateNavButtons();
                        }
                    };

                    document.getElementById('nextMonthBtn').onclick = () => {
                        currentMonth.setMonth(currentMonth.getMonth() + 1);
                        renderCalendar(currentMonth);
                        updateNavButtons();
                    };

                    for (let i = 0; i < startDayOfWeek; i++) {
                        const div = document.createElement('div');
                        div.className = 'day-cell inactive';
                        calendarDaysEl.appendChild(div);
                    }

                    for (let day = 1; day <= daysInMonth; day++) {
                        const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        
                        const div = document.createElement('div');
                        div.className = 'day-cell';
                        div.innerText = day;

                        const departure = departures.find(d => d.date === currentDateStr);
                        if (departure) {
                            // Check 7-day restriction
                            const depDate = new Date(departure.date);
                            depDate.setHours(0,0,0,0);
                            
                            const minDate = new Date();
                            minDate.setHours(0,0,0,0);
                            minDate.setDate(minDate.getDate() + 7);

                            const priceDiv = document.createElement('div');
                            priceDiv.className = 'day-price';
                            priceDiv.innerText = formatCompactPrice(departure.price);
                            div.appendChild(priceDiv);

                            if (depDate < minDate) {
                                div.classList.add('has-departure', 'disabled-too-soon');
                                div.title = "Đã quá hạn đăng ký (Yêu cầu đặt trước 7 ngày)";
                            } else if (departure.seats_available <= 0) {
                                div.classList.add('has-departure', 'sold-out');
                                const soldOutDiv = document.createElement('div');
                                soldOutDiv.className = 'day-price';
                                soldOutDiv.innerText = 'Hết chỗ';
                                div.appendChild(soldOutDiv);
                                div.title = "Tour này đã hết chỗ";
                            } else {
                                div.classList.add('has-departure');
                                div.onclick = function() {
                                    selectDeparture(departure, div);
                                };
                                
                                const selectedId = document.getElementById('departureSelect').value;
                                if (selectedId == departure.id) {
                                    div.classList.add('selected');
                                }
                            }
                        } else {
                            div.classList.add('inactive'); // Disable non-departure days
                        }

                        calendarDaysEl.appendChild(div);
                    }
                }

                function selectDeparture(departure, element) {
                    document.getElementById('departureSelect').value = departure.id;
                    
                    document.querySelectorAll('.day-cell').forEach(el => el.classList.remove('selected'));
                    element.classList.add('selected');

                    const dateFn = new Date(departure.date);
                    const formattedDate = `${dateFn.getDate()}/${dateFn.getMonth()+1}/${dateFn.getFullYear()}`;
                    const fullPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(departure.price);
                    document.getElementById('selectedDateDisplay').innerText = `Bạn chọn ngày: ${formattedDate} - Giá: ${fullPrice}`;

                    const sidebarSelectedDate = document.getElementById('sidebarSelectedDate');
                    const sidebarDateText = document.getElementById('sidebarDateText');
                    sidebarDateText.innerText = `Ngày: ${formattedDate} - Giá: ${fullPrice}`;
                    sidebarSelectedDate.classList.remove('d-none');
                }

                function checkDepartureAvailability(departureId) {
                    const dep = departures.find(d => d.id == departureId);
                    return dep && dep.seats_available > 0;
                }

                function formatCompactPrice(price) {
                    if (price >= 1000000000) {
                        return (price / 1000000000).toFixed(1).replace(/\.0$/, '') + ' tỷ';
                    } else if (price >= 1000000) {
                        return (price / 1000000).toFixed(1).replace(/\.0$/, '') + 'tr';
                    } else if (price >= 1000) {
                        return (price / 1000).toFixed(0) + 'k';
                    }
                    return price;
                }
            </script>
            <?php endif; ?>

            <!-- Policies -->
            <?php if (!empty($policies)): ?>
            <section class="mb-5">
                <h3 class="mb-4 text-primary">Chính sách & Điều khoản</h3>
                <div class="row g-4">
                    <?php foreach($policies as $policy): ?>
                        <div class="col-12">
                            <div class="policy-card p-4 rounded h-100 border shadow-sm">
                                <h5 class="card-title text-secondary mb-3">
                                    <i class="fas fa-shield-alt me-2"></i><?= htmlspecialchars($policy['name']) ?>
                                </h5>
                                <div class="card-text text-muted">
                                    <?= nl2br($policy['description']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Reviews Section -->
            <section class="mb-5" id="reviews">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3 class="text-primary fw-bold mb-0"><i class="fas fa-star me-2"></i>Đánh giá từ khách hàng</h3>
                    <?php
                    $avgRating    = round((float)($ratingSummary['avg_rating'] ?? 0), 1);
                    $totalReviews = (int)($ratingSummary['total'] ?? 0);
                    ?>
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-warning">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?= $i <= round($avgRating) ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="fw-bold"><?= number_format($avgRating, 1) ?>/5</span>
                        <span class="text-muted">(<?= $totalReviews ?> đánh giá)</span>
                    </div>
                </div>

                <!-- Rating Summary Bars -->
                <?php if ($totalReviews > 0): ?>
                <div class="bg-white p-4 rounded shadow-sm mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center border-end">
                            <div style="font-size:3.5rem;font-weight:800;color:#f59e0b;line-height:1"><?= number_format($avgRating, 1) ?></div>
                            <div class="text-warning my-1">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="<?= $i <= round($avgRating) ? 'fas' : ($i - 0.5 <= $avgRating ? 'fas fa-star-half-alt' : 'far') ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?= $totalReviews ?> đánh giá</small>
                        </div>
                        <div class="col-md-9 ps-4">
                            <?php foreach ([5,4,3,2,1] as $starNum):
                                $count = (int)($ratingSummary['star'.$starNum] ?? 0);
                                $pct   = $totalReviews > 0 ? round($count / $totalReviews * 100) : 0;
                            ?>
                            <div class="d-flex align-items-center mb-1">
                                <span class="text-muted me-2" style="width:40px;font-size:.8rem"><?= $starNum ?> sao</span>
                                <div class="flex-grow-1 bg-light rounded" style="height:8px;overflow:hidden">
                                    <div class="bg-warning rounded" style="width:<?= $pct ?>%;height:100%"></div>
                                </div>
                                <span class="ms-2 text-muted" style="width:32px;font-size:.8rem"><?= $count ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Flash messages -->
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Write Review Form -->
                <?php if ($canReview): ?>
                <div class="bg-white p-4 rounded shadow-sm mb-4 border border-primary border-opacity-25">
                    <h5 class="fw-bold mb-3"><i class="fas fa-pen-alt me-2 text-primary"></i>Viết đánh giá của bạn</h5>
                    <form action="<?= BASE_URL ?>?action=review-store" method="POST" id="reviewForm">
                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">

                        <!-- Star rating picker -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Đánh giá <span class="text-danger">*</span></label>
                            <div class="star-picker d-flex gap-1" id="starPicker">
                                <?php for($s = 1; $s <= 5; $s++): ?>
                                    <input type="radio" name="rating" id="star<?= $s ?>" value="<?= $s ?>" class="d-none" required>
                                    <label for="star<?= $s ?>" class="star-lbl fs-2" data-val="<?= $s ?>" style="cursor:pointer;color:#d1d5db;transition:color .1s">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                                <span class="ms-2 align-self-center text-muted small" id="starLabel">Chọn số sao</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nội dung đánh giá</label>
                            <textarea class="form-control" name="comment" rows="4"
                                      placeholder="Chia sẻ trải nghiệm của bạn về tour này…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-paper-plane me-2"></i>Gửi đánh giá
                        </button>
                    </form>
                    <script>
                    (function(){
                        const labels = document.querySelectorAll('#starPicker .star-lbl');
                        const texts  = ['','Rất tệ','Tệ','Bình thường','Tốt','Tuyệt vời!'];
                        let selected = 0;

                        function paint(upTo) {
                            labels.forEach((lbl, i) => {
                                lbl.style.color = i < upTo ? '#f59e0b' : '#d1d5db';
                            });
                        }

                        labels.forEach((lbl, i) => {
                            lbl.addEventListener('mouseover', () => paint(i + 1));
                            lbl.addEventListener('mouseleave', () => paint(selected));
                            lbl.addEventListener('click', () => {
                                selected = i + 1;
                                paint(selected);
                                document.getElementById('starLabel').textContent = texts[selected];
                            });
                        });
                    })();
                    </script>
                </div>
                <?php elseif ($alreadyReviewed): ?>
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i>Bạn đã đánh giá tour này. Cảm ơn chia sẻ của bạn!
                </div>
                <?php elseif (!empty($_SESSION['user'])): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Chỉ có thể đánh giá sau khi đã đặt và hoàn thành tour.
                    <a href="<?= BASE_URL ?>?action=tour-list" class="alert-link ms-2">Khám phá tour ngay</a>
                </div>
                <?php else: ?>
                <div class="alert alert-light border mb-4">
                    <i class="fas fa-lock me-2"></i>
                    <a href="<?= BASE_URL ?>?action=login" class="fw-bold">Đăng nhập</a> để viết đánh giá về tour này.
                </div>
                <?php endif; ?>

                <!-- Review List -->
                <?php if (!empty($reviews)): ?>
                    <div class="reviews-list">
                        <?php foreach($reviews as $rev): ?>
                            <div class="review-item bg-white p-4 rounded shadow-sm mb-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="review-avatar me-3">
                                            <?php if (!empty($rev['avatar'])): ?>
                                                <img src="<?= BASE_ASSETS_UPLOADS . $rev['avatar'] ?>" alt="User" class="rounded-circle" width="48" height="48" style="object-fit:cover">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:48px;height:48px">
                                                    <?= strtoupper(mb_substr($rev['full_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($rev['full_name'] ?? 'Khách hàng') ?></h6>
                                            <div class="text-warning small">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="<?= $i <= $rev['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                                <?php endfor; ?>
                                                <span class="text-muted ms-1"><?= $rev['rating'] ?>/5</span>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></small>
                                </div>
                                <?php if (!empty($rev['comment'])): ?>
                                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-5 rounded shadow-sm text-center text-muted">
                        <i class="far fa-comment-dots fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Chưa có đánh giá nào cho tour này. Hãy là người đầu tiên trải nghiệm và chia sẻ!</p>
                    </div>
                <?php endif; ?>
            </section>

        </div>

        <!-- Right Stick Sidebar -->
        <div class="col-lg-4">
            <div class="booking-card card shadow-lg sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header text-center">
                    <p class="text-muted mb-1 text-uppercase small ls-1">Giá trọn gói chỉ từ</p>
                    <div class="booking-price">
                        <?= number_format($tour['base_price'], 0, ',', '.') ?> <span class="fs-5 text-dark">đ</span>
                    </div>
                </div>
                <div class="card-body p-4 booking-form">
                    <div class="mb-4 text-center">
                         <p class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Kiểm tra lịch khởi hành bên dưới</p>
                         <button class="btn btn-outline-primary btn-sm w-100" onclick="document.getElementById('tour-calendar-section').scrollIntoView({behavior: 'smooth'})">
                            Xem lịch & chọn ngày
                         </button>
                    </div>
                    
                     <!-- Hidden input for selected departure (synced with calendar) -->
                     <input type="hidden" id="departureSelect" value="">
                     <div id="sidebarSelectedDate" class="alert alert-success p-2 small mb-3 d-none">
                        <i class="fas fa-check-circle me-1"></i> <span id="sidebarDateText"></span>
                     </div>

                    <div class="d-grid gap-3">
                        <button class="btn btn-book-now text-white btn-lg shadow-sm" type="button" onclick="bookNow()">
                            <i class="fas fa-paper-plane me-2"></i> Đặt Tour Ngay
                        </button>
                        <button class="btn btn-outline-primary btn-lg" type="button">
                            <i class="fas fa-headset me-2"></i> Tư vấn miễn phí
                        </button>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="d-flex align-items-center bg-light p-3 rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-white p-2 rounded-circle shadow-sm text-primary">
                                <i class="fas fa-building fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Đơn vị tổ chức</p>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($tour['supplier_name'] ?? 'VietTravel') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script for bookNow (Moved out/kept global) -->
<script>
    function bookNow() {
        try {
            const departureSelect = document.getElementById('departureSelect');
            if (!departureSelect || !departureSelect.value) {
                alert('Vui lòng chọn ngày khởi hành từ lịch!');
                document.getElementById('tour-calendar-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            
            const tourId = <?= $tour['id'] ?>;
            const departureId = departureSelect.value;

            if (!checkDepartureAvailability(departureId)) {
                alert('Rất tiếc, ngày khởi hành này đã hết chỗ! Vui lòng chọn ngày khác.');
                return;
            }

            window.location.href = `<?= BASE_URL ?>?action=booking-create&tour_id=${tourId}&departure_id=${departureId}`;
        } catch (e) {
            console.error(e);
            alert('Có lỗi xảy ra: ' + e.message);
        }
    }
</script>

<?php include_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
