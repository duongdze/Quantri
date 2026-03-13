<?php require_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<!-- Hero -->
<section style="background: linear-gradient(135deg,#0a1628,#1a2f5e); padding: 80px 0; color: #fff; text-align: center;">
    <div class="container">
        <div class="about-logo mb-3"><i class="fas fa-globe-asia" style="font-size:3rem;color:#60a5fa;"></i></div>
        <h1 style="font-size:2.4rem;font-weight:800;">Về VietTour</h1>
        <p style="color:rgba(255,255,255,.75);font-size:1.05rem;max-width:600px;margin:0 auto">Nền tảng du lịch trực tuyến hàng đầu Việt Nam – Nơi mọi chuyến đi bắt đầu</p>
    </div>
</section>

<!-- Intro -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-tag">Câu chuyện của chúng tôi</span>
                <h2 class="section-title mt-2">Khởi nguồn từ tình yêu du lịch</h2>
                <p class="text-muted">VietTour được thành lập với sứ mệnh kết nối du khách với những điểm đến tuyệt vời nhất của Việt Nam. Chúng tôi tin rằng mỗi chuyến đi là một câu chuyện đáng kể, một kỷ niệm không thể quên.</p>
                <p class="text-muted">Với đội ngũ hướng dẫn viên chuyên nghiệp và hệ thống đặt tour trực tuyến tiện lợi, chúng tôi cam kết mang lại trải nghiệm du lịch tốt nhất cho bạn.</p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="about-stat-card" style="background:linear-gradient(135deg,#1e6fff,#6b8ff8);">
                            <i class="fas fa-map-marked-alt"></i>
                            <span class="num">200+</span>
                            <span>Tour chất lượng</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card" style="background:linear-gradient(135deg,#27ae60,#2ecc71);">
                            <i class="fas fa-users"></i>
                            <span class="num">10,000+</span>
                            <span>Khách hàng</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card" style="background:linear-gradient(135deg,#e74c3c,#f39c12);">
                            <i class="fas fa-award"></i>
                            <span class="num">5+</span>
                            <span>Năm kinh nghiệm</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card" style="background:linear-gradient(135deg,#8e44ad,#9b59b6);">
                            <i class="fas fa-star"></i>
                            <span class="num">4.8/5</span>
                            <span>Đánh giá TB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values -->
<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">Giá trị cốt lõi</span>
            <h2 class="section-title mt-2">Chúng tôi cam kết gì?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-heart" style="color:#e74c3c;font-size:2rem;margin-bottom:16px;"></i>
                    <h5 class="fw-bold">Tận tâm với khách hàng</h5>
                    <p class="text-muted small">Mỗi du khách là một người bạn. Chúng tôi coi trọng trải nghiệm của bạn như của chính mình.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-leaf" style="color:#27ae60;font-size:2rem;margin-bottom:16px;"></i>
                    <h5 class="fw-bold">Du lịch bền vững</h5>
                    <p class="text-muted small">Chúng tôi luôn chú trọng bảo tồn môi trường và phát triển du lịch có trách nhiệm.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <i class="fas fa-shield-alt" style="color:#1e6fff;font-size:2rem;margin-bottom:16px;"></i>
                    <h5 class="fw-bold">Minh bạch & Tin cậy</h5>
                    <p class="text-muted small">Giá cả minh bạch, không phát sinh, chính sách hoàn tiền rõ ràng và hỗ trợ 24/7.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-stat-card { border-radius:18px; padding:30px 20px; text-align:center; color:#fff; display:flex; flex-direction:column; align-items:center; gap:6px; }
.about-stat-card i { font-size:1.5rem; opacity:.8; }
.about-stat-card .num { font-size:1.6rem; font-weight:800; }
.about-stat-card span { font-size:.82rem; opacity:.85; }
.value-card { text-align:center; padding:32px 24px; background:#fff; border-radius:18px; border:1.5px solid #f1f5f9; box-shadow:0 2px 8px rgba(0,0,0,.04); height:100%; }
</style>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
