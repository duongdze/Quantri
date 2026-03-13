</main>

<!-- FOOTER -->
<footer class="vt-footer">
    <div class="container">
        <div class="row g-5">
            <!-- Brand -->
            <div class="col-lg-4">
                <div class="vt-footer-logo">
                    <div class="logo-icon" style="width:36px;height:36px;background:linear-gradient(135deg,#1e6fff,#6b8ff8);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;">
                        <i class="fas fa-globe-asia"></i>
                    </div>
                    VietTour
                </div>
                <p>Nền tảng đặt tour du lịch trực tuyến hàng đầu Việt Nam. Khám phá vẻ đẹp đất nước với hàng trăm tour chất lượng cao.</p>
                <div class="vt-social">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-6">
                <h5>Khám phá</h5>
                <ul class="vt-footer-links">
                    <li><a href="<?= BASE_URL ?>?action=tour-list"><i class="fas fa-angle-right"></i> Tất cả tours</a></li>
                    <li><a href="<?= BASE_URL ?>?action=tour-list&category_id=1"><i class="fas fa-angle-right"></i> Tour trong nước</a></li>
                    <li><a href="<?= BASE_URL ?>?action=tour-list&category_id=2"><i class="fas fa-angle-right"></i> Tour nước ngoài</a></li>
                    <li><a href="<?= BASE_URL ?>?action=tour-list&sort_by=price&sort_dir=ASC"><i class="fas fa-angle-right"></i> Tour giá tốt</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-3 col-6">
                <h5>Hỗ trợ</h5>
                <ul class="vt-footer-links">
                    <li><a href="<?= BASE_URL ?>?action=guide-booking"><i class="fas fa-angle-right"></i> Hướng dẫn đặt tour</a></li>
                    <li><a href="<?= BASE_URL ?>?action=refund-policy"><i class="fas fa-angle-right"></i> Chính sách hoàn tiền</a></li>
                    <li><a href="<?= BASE_URL ?>?action=about"><i class="fas fa-angle-right"></i> Về chúng tôi</a></li>
                    <li><a href="<?= BASE_URL ?>?action=faq"><i class="fas fa-angle-right"></i> Câu hỏi thường gặp</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-3">
                <h5>Liên hệ</h5>
                <ul class="vt-footer-links">
                    <li><a href="tel:19001234"><i class="fas fa-phone"></i> 1900 1234</a></li>
                    <li><a href="mailto:support@viettour.vn"><i class="fas fa-envelope"></i> support@viettour.vn</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Lê Lợi, Q.1, TP.HCM</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> T2–T7: 8:00 – 18:00</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="vt-footer-bottom">
        <div class="container">
            © <?= date('Y') ?> VietTour. Thiết kế với <i class="fas fa-heart" style="color:#ef4444"></i> tại Việt Nam.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>