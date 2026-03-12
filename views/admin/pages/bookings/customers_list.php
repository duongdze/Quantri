<?php include_once PATH_VIEW_ADMIN . 'default/header.php'; ?>
<?php include_once PATH_VIEW_ADMIN . 'default/sidebar.php'; ?>
<main class="wrapper">
  <div class="main-content">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h3">Danh sách khách trong đoàn</h1>
        <p class="text-muted">
          Tour: <strong><?= htmlspecialchars($booking['tour_name']) ?></strong>
        </p>
      </div>
      <a href="<?= BASE_URL_ADMIN . '&action=bookings' ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách booking
      </a>
    </div>

    <div class="card">
      <div class="card-header">Khách tham gia tour</div>
      <div class="card-body">
        <?php if (empty($customers)): ?>
          <p class="text-muted mb-0">Chưa có khách nào trong đoàn.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th>Họ tên</th>
                  <th>Giới tính</th>
                  <th>Ngày sinh</th>
                  <th>SĐT</th>
                  <th>CMND/CCCD</th>
                  <th>Ghi chú</th>
                  <th>Phòng</th>
                  <th>Nhóm</th>
                  <th>FOC</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($customers as $c): ?>
                  <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['gender']) ?></td>
                    <td><?= htmlspecialchars($c['birth_date']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                    <td><?= htmlspecialchars($c['id_card']) ?></td>
                    <td><?= nl2br(htmlspecialchars($c['special_request'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($c['room_type']) ?></td>
                    <td><?= htmlspecialchars($c['passenger_type']) ?></td>
                    <td><?= ((int)$c['is_foc'] === 1) ? '✅' : '' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<?php include_once PATH_VIEW_ADMIN . 'default/footer.php'; ?>
