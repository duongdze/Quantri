<?php require_once PATH_VIEW_CLIENT . 'default/header.php'; ?>

<section style="background: linear-gradient(135deg,#0a1628,#1e6fff); padding: 80px 0 60px; color: #fff; text-align: center;">
    <div class="container">
        <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:.5rem">Chính sách hoàn tiền</h1>
        <p style="color:rgba(255,255,255,.8)">Minh bạch – Rõ ràng – Công bằng</p>
    </div>
</section>

<div class="container py-5" style="max-width:800px">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <?php
        $policies = [
            ['days' => '≥ 15 ngày', 'refund' => '100%', 'class' => 'success'],
            ['days' => '8–14 ngày', 'refund' => '80%', 'class' => 'info'],
            ['days' => '3–7 ngày', 'refund' => '50%', 'class' => 'warning'],
            ['days' => '1–2 ngày', 'refund' => '20%', 'class' => 'danger'],
            ['days' => 'Ngày khởi hành', 'refund' => '0%', 'class' => 'dark'],
        ];
        ?>

        <h5 class="fw-bold mb-3">Bảng phí hủy tour</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered rounded-3 overflow-hidden">
                <thead class="table-dark">
                    <tr>
                        <th>Thời điểm hủy (trước ngày khởi hành)</th>
                        <th>Hoàn tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($policies as $p): ?>
                    <tr>
                        <td><?= $p['days'] ?></td>
                        <td><span class="badge bg-<?= $p['class'] ?>"><?= $p['refund'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h5 class="fw-bold mb-3">Điều kiện áp dụng</h5>
        <ul class="text-muted">
            <li class="mb-2">Yêu cầu hoàn tiền phải được gửi qua email hoặc hotline chính thức của VietTour.</li>
            <li class="mb-2">Hoàn tiền được xử lý trong vòng 3–7 ngày làm việc.</li>
            <li class="mb-2">Trường hợp tour bị hủy do sự cố bất khả kháng (thiên tai, dịch bệnh), khách hàng được hoàn 100%.</li>
            <li class="mb-2">Không hoàn tiền đối với dịch vụ cộng thêm (visa, bảo hiểm riêng lẻ).</li>
        </ul>

        <div class="alert alert-primary border-0 rounded-3 mt-4 d-flex align-items-center">
            <i class="fas fa-info-circle me-3 fs-4"></i>
            <span>Để hủy tour hoặc thắc mắc về hoàn tiền, vui lòng liên hệ hotline <strong>0901 234 567</strong> hoặc email <strong>hotro@viettour.vn</strong>.</span>
        </div>
    </div>
</div>

<?php require_once PATH_VIEW_CLIENT . 'default/footer.php'; ?>
