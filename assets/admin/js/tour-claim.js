// ============================================
// TOUR CLAIM SYSTEM - Hệ thống HDV nhận tour
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    const claimBtns = document.querySelectorAll('.claim-tour-btn');

    if (claimBtns.length === 0) return; // Không có tour khả dụng

    claimBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tourId = this.dataset.tourId;
            const tourName = this.dataset.tourName;
            const originalHTML = this.innerHTML;

            // Confirmation dialog
            if (!confirm(`Bạn có chắc muốn nhận tour "${tourName}"?\n\n✓ Sau khi nhận, bạn sẽ chịu trách nhiệm quản lý tất cả booking của tour này.\n✓ Tour sẽ xuất hiện trong danh sách "Tour của tôi".\n✓ Bạn có thể xem và chỉnh sửa trạng thái các booking.`)) {
                return;
            }

            // Disable button và hiển thị loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            const formData = new FormData();
            formData.append('tour_id', tourId);

            fetch('?mode=admin&action=guides/claim-tour', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success - show message and reload
                        alert('✓ ' + data.message);
                        location.reload();
                    } else {
                        // Error - show message and re-enable button
                        alert('✗ ' + data.message);
                        this.disabled = false;
                        this.innerHTML = originalHTML;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('✗ Có lỗi xảy ra khi nhận tour. Vui lòng thử lại.');
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                });
        });
    });
});
