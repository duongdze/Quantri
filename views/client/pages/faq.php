<?php require_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<section style="background: linear-gradient(135deg,#0a1628,#1e6fff); padding: 80px 0 60px; color: #fff; text-align: center;">
    <div class="container">
        <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:.5rem">Câu hỏi thường gặp</h1>
        <p style="color:rgba(255,255,255,.8)">Tổng hợp các câu hỏi phổ biến từ khách hàng</p>
    </div>
</section>

<div class="container py-5" style="max-width:800px">
    <?php
    $faqs = [
        ['q' => 'Tôi có thể hủy tour không?', 'a' => 'Bạn có thể hủy tour trong vòng 48 giờ trước ngày khởi hành. Phí hủy tour theo chính sách hoàn tiền của VietTour.'],
        ['q' => 'Thanh toán bằng hình thức nào?', 'a' => 'VietTour hỗ trợ chuyển khoản ngân hàng và quét QR code. Thông tin thanh toán được hiển thị sau khi đặt tour.'],
        ['q' => 'Tôi cần chuẩn bị giấy tờ gì?', 'a' => 'Đối với tour trong nước: chứng minh nhân dân hoặc hộ chiếu. Đối với tour nước ngoài: hộ chiếu còn hiệu lực ít nhất 6 tháng và visa (nếu cần).'],
        ['q' => 'Trẻ em có được giảm giá không?', 'a' => 'Trẻ em dưới 2 tuổi miễn phí, trẻ 2-12 tuổi được giảm 50% giá người lớn. Chi tiết từng tour có thể khác nhau.'],
        ['q' => 'Tôi có thể thay đổi ngày khởi hành không?', 'a' => 'Bạn có thể liên hệ hotline 0901 234 567 để yêu cầu đổi ngày. Việc thay đổi phụ thuộc vào chỗ trống còn lại.'],
        ['q' => 'Tour có bảo hiểm du lịch không?', 'a' => 'Tất cả các tour của VietTour đều bao gồm bảo hiểm du lịch cơ bản. Bạn có thể mua thêm gói bảo hiểm nâng cao khi đặt tour.'],
    ];
    ?>
    <div class="accordion" id="faqAccordion">
        <?php foreach ($faqs as $i => $faq): ?>
        <div class="accordion-item border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> fw-bold" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                    <i class="fas fa-question-circle text-primary me-3"></i>
                    <?= htmlspecialchars($faq['q']) ?>
                </button>
            </h2>
            <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted">
                    <?= htmlspecialchars($faq['a']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <p class="text-muted">Còn thắc mắc?</p>
        <a href="<?= BASE_URL ?>?action=contact" class="btn btn-outline-primary rounded-pill px-5">
            <i class="fas fa-envelope me-2"></i>Liên hệ hỗ trợ
        </a>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
