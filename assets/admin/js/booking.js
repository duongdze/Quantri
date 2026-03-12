document.addEventListener('DOMContentLoaded', function () {
    const tourSelect = document.getElementById('booking-tour-id');
    const customerSelect = document.getElementById('booking-customer-id');
    const totalPriceInput = document.getElementById('booking-total-price');
    const companionList = document.getElementById('booking-companion-list');
    const addCompanionBtn = document.getElementById('booking-add-companion-btn');
    const companionTemplate = document.getElementById('companion-template');

    // Update total price when tour changes
    tourSelect.addEventListener('change', function () {
        if (this.value) {
            const price = this.options[this.selectedIndex].dataset.price || 0;
            totalPriceInput.value = parseInt(price) || 0;
            document.getElementById('booking-summary-tour').textContent = this.options[this.selectedIndex].text;
        } else {
            totalPriceInput.value = 0;
            document.getElementById('booking-summary-tour').textContent = '--';
        }
        updateSummary();
    });

    // Update customer name in summary
    customerSelect.addEventListener('change', function () {
        if (this.value) {
            document.getElementById('booking-summary-customer').textContent = this.options[this.selectedIndex].text.split('(')[0].trim();
        } else {
            document.getElementById('booking-summary-customer').textContent = '--';
        }
        updateSummary();
    });

    // Add companion button
    addCompanionBtn.addEventListener('click', function (e) {
        e.preventDefault();
        addCompanionItem();
    });

    function addCompanionItem(data = null) {
        const clone = companionTemplate.content.cloneNode(true);

        if (data) {
            clone.querySelector('[name="companion_name[]"]').value = data.name || '';
            clone.querySelector('[name="companion_gender[]"]').value = data.gender || '';
            clone.querySelector('[name="companion_birth_date[]"]').value = data.birth_date || '';
            clone.querySelector('[name="companion_phone[]"]').value = data.phone || '';
            clone.querySelector('[name="companion_id_card[]"]').value = data.id_card || '';
            clone.querySelector('[name="companion_room_type[]"]').value = data.room_type || '';
            clone.querySelector('[name="companion_special_request[]"]').value = data.special_request || '';
        }

        const removeBtn = clone.querySelector('.remove-companion');
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            this.closest('.companion-item').remove();
            updateSummary();
        });

        companionList.appendChild(clone);
        updateSummary();
    }

    function updateSummary() {
        const companionCount = document.querySelectorAll('.companion-item').length;
        document.getElementById('booking-summary-companion-count').textContent = companionCount;
        document.getElementById('booking-summary-price').textContent =
            (parseInt(totalPriceInput.value) || 0).toLocaleString('vi-VN');
    }

    // Set today's date as default booking date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('booking-booking-date').value = today;

    // Initialize summary
    updateSummary();
});


document.addEventListener('DOMContentLoaded', function () {
    // Xử lý click vào các nút đổi trạng thái
    const statusChangeBtns = document.querySelectorAll('.status-change-btn');

    statusChangeBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const newStatus = this.dataset.status;
            const bookingId = this.dataset.bookingId;
            const statusNames = {
                'cho_xac_nhan': 'Chờ xác nhận',
                'da_coc': 'Đã cọc',
                'hoan_tat': 'Hoàn tất',
                'da_huy': 'Hủy'
            };

            // Xác nhận trước khi đổi
            if (!confirm(`Bạn có chắc muốn đổi trạng thái sang "${statusNames[newStatus]}"?`)) {
                return;
            }

            // Gửi AJAX request
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            formData.append('status', newStatus);

            fetch('?mode=admin&action=bookings/update-status', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật badge trạng thái
                        const badge = document.getElementById('status-badge');
                        if (badge) {
                            // Xóa các class cũ
                            badge.classList.remove('bg-warning', 'bg-info', 'bg-success', 'bg-danger');

                            // Thêm class mới
                            const statusClasses = {
                                'cho_xac_nhan': 'bg-warning',
                                'da_coc': 'bg-info',
                                'hoan_tat': 'bg-success',
                                'da_huy': 'bg-danger'
                            };
                            badge.classList.add(statusClasses[newStatus]);
                            badge.textContent = data.status_text;
                            badge.dataset.status = newStatus;
                        }

                        // Hiển thị thông báo thành công
                        alert(data.message);
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật trạng thái');
                });
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const companionModal = document.getElementById('companionModal');
    const companionForm = document.getElementById('companionForm');
    const addCompanionBtn = document.getElementById('add-companion-btn');
    const saveCompanionBtn = document.getElementById('saveCompanionBtn');
    if (!companionModal || !addCompanionBtn) return; // Không phải trang detail
    const modal = new bootstrap.Modal(companionModal);
    let isEditMode = false;
    // Mở modal thêm companion mới
    addCompanionBtn.addEventListener('click', function () {
        isEditMode = false;
        document.getElementById('companionModalTitle').textContent = 'Thêm Khách Đi Kèm';
        companionForm.reset();
        document.getElementById('companion-id').value = '';
        modal.show();
    });
    // Xử lý click nút Edit companion
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-companion-btn') || e.target.closest('.edit-companion-btn')) {
            e.preventDefault();
            const btn = e.target.classList.contains('edit-companion-btn') ? e.target : e.target.closest('.edit-companion-btn');
            isEditMode = true;
            const companionId = btn.dataset.companionId;
            // Lấy thông tin companion từ DOM
            const companionCard = btn.closest('.border');
            const name = companionCard.querySelector('[data-field="name"]')?.textContent.trim() || '';
            const gender = companionCard.querySelector('[data-field="gender"]')?.textContent.trim() || '';
            const birthDate = companionCard.querySelector('[data-field="birth_date"]')?.textContent.trim() || '';
            const phone = companionCard.querySelector('[data-field="phone"]')?.textContent.trim() || '';
            const idCard = companionCard.querySelector('[data-field="id_card"]')?.textContent.trim() || '';
            const roomType = companionCard.querySelector('[data-field="room_type"]')?.textContent.trim() || '';
            const specialRequest = companionCard.querySelector('[data-field="special_request"]')?.textContent.trim() || '';
            // Điền vào form
            document.getElementById('companionModalTitle').textContent = 'Sửa Thông Tin Khách';
            document.getElementById('companion-id').value = companionId;
            document.getElementById('companion-name').value = name;
            document.getElementById('companion-gender').value = gender;
            document.getElementById('companion-birth-date').value = birthDate;
            document.getElementById('companion-phone').value = phone;
            document.getElementById('companion-id-card').value = idCard;
            document.getElementById('companion-room-type').value = roomType;
            document.getElementById('companion-special-request').value = specialRequest;
            modal.show();
        }
    });
    // Xử lý click nút Delete companion
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-companion-btn') || e.target.closest('.delete-companion-btn')) {
            e.preventDefault();
            const btn = e.target.classList.contains('delete-companion-btn') ? e.target : e.target.closest('.delete-companion-btn');
            const companionId = btn.dataset.companionId;
            const bookingId = btn.dataset.bookingId;
            if (!confirm('Bạn có chắc muốn xóa khách này?')) return;
            const formData = new FormData();
            formData.append('companion_id', companionId);
            formData.append('booking_id', bookingId);
            fetch('?mode=admin&action=bookings/delete-companion', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Reload để cập nhật danh sách
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
        }
    });
    // Lưu companion (thêm mới hoặc cập nhật)
    saveCompanionBtn.addEventListener('click', function () {
        const formData = new FormData(companionForm);
        const action = isEditMode ? 'bookings/update-companion' : 'bookings/add-companion';
        fetch(`?mode=admin&action=${action}`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    modal.hide();
                    location.reload(); // Reload để cập nhật danh sách
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
    });
});