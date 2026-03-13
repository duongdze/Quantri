<?php require_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<!-- Contact Hero -->
<section style="background: linear-gradient(135deg, #0a1628, #1e6fff); padding: 80px 0 60px; color: #fff; text-align: center;">
    <div class="container">
        <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:.5rem">Liên hệ với chúng tôi</h1>
        <p style="color:rgba(255,255,255,.8)">Chúng tôi luôn sẵn sàng hỗ trợ bạn. Hãy kết nối với chúng tôi!</p>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4">
        <!-- Contact Cards -->
        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="contact-info-card">
                        <div class="contact-icon" style="background:#e8f4fd;color:#1e6fff"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="fw-bold">Địa chỉ</div>
                            <div class="text-muted small">123 Đường Du Lịch, Q.1, TP.HCM</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="contact-info-card">
                        <div class="contact-icon" style="background:#fef9e7;color:#f39c12"><i class="fas fa-phone-alt"></i></div>
                        <div>
                            <div class="fw-bold">Hotline</div>
                            <div class="text-muted small">0901 234 567 (8h – 21h)</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="contact-info-card">
                        <div class="contact-icon" style="background:#eafaf1;color:#27ae60"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="fw-bold">Email</div>
                            <div class="text-muted small">hotro@viettour.vn</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="contact-info-card">
                        <div class="contact-icon" style="background:#f5eef8;color:#8e44ad"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="fw-bold">Giờ làm việc</div>
                            <div class="text-muted small">T2-T7: 8:00 – 21:00 <br>CN: 9:00 – 18:00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <?php 
                $error   = $_SESSION['error']   ?? null;
                $success = $_SESSION['success'] ?? null;
                unset($_SESSION['error'], $_SESSION['success']);
                ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success border-0 rounded-3 mb-4"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4"><i class="fas fa-times-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <h5 class="fw-bold mb-4">Gửi tin nhắn cho chúng tôi</h5>
                <form action="<?= BASE_URL ?>?action=contact-post" method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Chủ đề</label>
                            <select name="subject" class="form-select bg-light border-0 rounded-3">
                                <option>Thắc mắc về tour</option>
                                <option>Hỗ trợ đặt tour</option>
                                <option>Chính sách hoàn tiền</option>
                                <option>Báo lỗi / phản hồi</option>
                                <option>Khác</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control bg-light border-0 rounded-3" rows="5" required placeholder="Nhập nội dung liên hệ..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.contact-info-card { display:flex;align-items:center;gap:16px;padding:16px 20px;background:#fff;border-radius:14px;border:1.5px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,.04); }
.contact-icon { width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
</style>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
