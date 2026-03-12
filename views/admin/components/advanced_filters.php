<?php

/**
 * Advanced Filters Component
 * @param array $filters - Current filter values
 * @param array $filterOptions - Available filter options
 * @param string $reportType - Type of report (financial, booking, feedback)
 */

function renderAdvancedFilters($filters = [], $filterOptions = [], $reportType = 'general')
{
    $filters = $filters ?? [];
    $filterOptions = $filterOptions ?? [];
    $reportType = $reportType ?? 'general';
?>

    <!-- Advanced Filters Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Bộ lọc nâng cao
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-1"></i>Reset
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAdvancedFilters()">
                    <i class="fas fa-cog me-1"></i>Nâng cao
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Basic Filters -->
            <div class="row g-3 mb-3">
                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label">Khoảng thời gian</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from"
                            value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                            id="date_from">
                        <span class="input-group-text">đến</span>
                        <input type="date" class="form-control" name="date_to"
                            value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                            id="date_to">
                    </div>
                </div>

                <!-- Quick Date Presets -->
                <div class="col-md-2">
                    <label class="form-label">Chọn nhanh</label>
                    <select class="form-select" id="date_preset" onchange="applyDatePreset()">
                        <option value="">-- Chọn --</option>
                        <?php if (isset($filterOptions['datePresets'])): ?>
                            <?php foreach ($filterOptions['datePresets'] as $preset): ?>
                                <option value="<?= $preset['key'] ?>"><?= $preset['label'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if ($reportType === 'booking' || $reportType === 'financial'): ?>
                    <!-- Tour Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Tour</label>
                        <select class="form-select" name="tour_id" id="tour_filter">
                            <option value="">-- Tất cả tours --</option>
                            <?php if (isset($filterOptions['tours'])): ?>
                                <?php foreach ($filterOptions['tours'] as $tour): ?>
                                    <option value="<?= $tour['id'] ?>"
                                        <?= (isset($filters['tour_id']) && $filters['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Danh mục</label>
                        <select class="form-select" name="category_id" id="category_filter">
                            <option value="">-- Tất cả --</option>
                            <?php if (isset($filterOptions['categories'])): ?>
                                <?php foreach ($filterOptions['categories'] as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                        <?= (isset($filters['category_id']) && $filters['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status" id="status_filter">
                            <option value="">-- Tất cả --</option>
                            <?php if (isset($filterOptions['statuses'])): ?>
                                <?php foreach ($filterOptions['statuses'] as $status): ?>
                                    <option value="<?= $status['value'] ?>"
                                        <?= (isset($filters['status']) && $filters['status'] == $status['value']) ? 'selected' : '' ?>>
                                        <?= $status['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($reportType === 'feedback'): ?>
                    <!-- Feedback Type Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Loại phản hồi</label>
                        <select class="form-select" name="feedback_type" id="feedback_type_filter">
                            <option value="">-- Tất cả --</option>
                            <?php if (isset($filterOptions['feedbackTypes'])): ?>
                                <?php foreach ($filterOptions['feedbackTypes'] as $type): ?>
                                    <option value="<?= $type['value'] ?>"
                                        <?= (isset($filters['feedback_type']) && $filters['feedback_type'] == $type['value']) ? 'selected' : '' ?>>
                                        <i class="<?= $type['icon'] ?> me-1"></i><?= $type['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Đánh giá</label>
                        <select class="form-select" name="rating" id="rating_filter">
                            <option value="">-- Tất cả --</option>
                            <?php if (isset($filterOptions['ratings'])): ?>
                                <?php foreach ($filterOptions['ratings'] as $rating): ?>
                                    <option value="<?= $rating['value'] ?>"
                                        <?= (isset($filters['rating']) && $filters['rating'] == $rating['value']) ? 'selected' : '' ?>>
                                        <?= $rating['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Sentiment Filter -->
                    <div class="col-md-2">
                        <label class="form-label">Tình cảm</label>
                        <select class="form-select" name="sentiment" id="sentiment_filter">
                            <option value="">-- Tất cả --</option>
                            <?php if (isset($filterOptions['sentiments'])): ?>
                                <?php foreach ($filterOptions['sentiments'] as $sentiment): ?>
                                    <option value="<?= $sentiment['value'] ?>"
                                        <?= (isset($filters['sentiment']) && $filters['sentiment'] == $sentiment['value']) ? 'selected' : '' ?>>
                                        <i class="<?= $sentiment['icon'] ?> me-1"></i><?= $sentiment['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Advanced Filters (Hidden by default) -->
            <div id="advanced_filters" style="display: none;">
                <div class="row g-3 mb-3">
                    <?php if ($reportType === 'booking' || $reportType === 'financial'): ?>
                        <!-- Price Range -->
                        <div class="col-md-3">
                            <label class="form-label">Khoảng giá</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="price_min"
                                    value="<?= htmlspecialchars($filters['price_min'] ?? '') ?>"
                                    placeholder="Tối thiểu">
                                <span class="input-group-text">-</span>
                                <input type="number" class="form-control" name="price_max"
                                    value="<?= htmlspecialchars($filters['price_max'] ?? '') ?>"
                                    placeholder="Tối đa">
                            </div>
                        </div>

                        <!-- Duration Range -->
                        <div class="col-md-2">
                            <label class="form-label">Thời gian (ngày)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="duration_min"
                                    value="<?= htmlspecialchars($filters['duration_min'] ?? '') ?>"
                                    placeholder="Tối thiểu">
                                <span class="input-group-text">-</span>
                                <input type="number" class="form-control" name="duration_max"
                                    value="<?= htmlspecialchars($filters['duration_max'] ?? '') ?>"
                                    placeholder="Tối đa">
                            </div>
                        </div>

                        <!-- Departure Location -->
                        <div class="col-md-2">
                            <label class="form-label">Nơi khởi hành</label>
                            <select class="form-select" name="departure_location">
                                <option value="">-- Tất cả --</option>
                                <?php if (isset($filterOptions['departureLocations'])): ?>
                                    <?php foreach ($filterOptions['departureLocations'] as $location): ?>
                                        <option value="<?= $location ?>"
                                            <?= (isset($filters['departure_location']) && $filters['departure_location'] == $location) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($location) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Source Filter (Booking only) -->
                        <?php if ($reportType === 'booking'): ?>
                            <div class="col-md-2">
                                <label class="form-label">Nguồn</label>
                                <select class="form-select" name="source">
                                    <option value="">-- Tất cả --</option>
                                    <?php if (isset($filterOptions['sources'])): ?>
                                        <?php foreach ($filterOptions['sources'] as $source): ?>
                                            <option value="<?= $source['value'] ?>"
                                                <?= (isset($filters['source']) && $filters['source'] == $source['value']) ? 'selected' : '' ?>>
                                                <?= $source['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Keyword Search -->
                    <div class="col-md-3">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" class="form-control" name="keyword"
                            value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>"
                            placeholder="Tìm kiếm...">
                    </div>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <small id="filter_summary"></small>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search me-1"></i>Áp dụng
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-eraser me-1"></i>Xóa bộ lọc
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    <div id="active_filters" class="mb-3" style="display: none;">
        <div class="d-flex flex-wrap gap-2">
            <!-- Active filter tags will be inserted here -->
        </div>
    </div>

<?php
}
?>

<script>
    // Advanced Filters JavaScript
    function toggleAdvancedFilters() {
        const advancedDiv = document.getElementById('advanced_filters');
        const button = event.target.closest('button');

        if (advancedDiv.style.display === 'none') {
            advancedDiv.style.display = 'block';
            button.innerHTML = '<i class="fas fa-cog me-1"></i>Thu gọn';
        } else {
            advancedDiv.style.display = 'none';
            button.innerHTML = '<i class="fas fa-cog me-1"></i>Nâng cao';
        }
    }

    function applyDatePreset() {
        const preset = document.getElementById('date_preset').value;
        if (!preset) return;

        const presets = {
            'today': [new Date(), new Date()],
            'yesterday': [new Date(Date.now() - 86400000), new Date(Date.now() - 86400000)],
            'this_week': [new Date(Date.now() - (new Date().getDay() * 86400000)), new Date()],
            'last_week': [new Date(Date.now() - ((new Date().getDay() + 7) * 86400000)), new Date(Date.now() - ((new Date().getDay()) * 86400000))],
            'this_month': [new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date()],
            'last_month': [new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1), new Date(new Date().getFullYear(), new Date().getMonth(), 0)],
            'last_7_days': [new Date(Date.now() - (6 * 86400000)), new Date()],
            'last_30_days': [new Date(Date.now() - (29 * 86400000)), new Date()],
            'last_90_days': [new Date(Date.now() - (89 * 86400000)), new Date()]
        };

        if (presets[preset]) {
            const [start, end] = presets[preset];
            document.getElementById('date_from').value = formatDate(start);
            document.getElementById('date_to').value = formatDate(end);
        }
    }

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function applyFilters() {
        const form = document.createElement('form');
        form.method = 'GET';

        // Get all filter inputs
        const inputs = document.querySelectorAll('input[name], select[name]');
        inputs.forEach(input => {
            if (input.value) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = input.name;
                hiddenInput.value = input.value;
                form.appendChild(hiddenInput);
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    function clearFilters() {
        // Clear all filter inputs
        const inputs = document.querySelectorAll('input[name], select[name]');
        inputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });

        // Submit form to clear filters
        applyFilters();
    }

    function resetFilters() {
        // Reset to default filters (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));

        document.getElementById('date_from').value = formatDate(thirtyDaysAgo);
        document.getElementById('date_to').value = formatDate(today);
        document.getElementById('date_preset').value = 'last_30_days';

        // Clear other filters
        const otherInputs = document.querySelectorAll('input[name]:not([name="date_from"]):not([name="date_to"]), select[name]:not([name="date_preset"])');
        otherInputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });

        applyFilters();
    }

    function updateFilterSummary() {
        const summaryDiv = document.getElementById('filter_summary');
        const activeFiltersDiv = document.getElementById('active_filters');
        const filters = [];

        // Collect active filters
        const inputs = document.querySelectorAll('input[name], select[name]');
        inputs.forEach(input => {
            if (input.value) {
                let label = input.previousElementSibling?.textContent || input.name;
                let value = input.value;

                // Format display values
                if (input.type === 'date') {
                    value = new Date(value).toLocaleDateString('vi-VN');
                } else if (input.type === 'number') {
                    value = parseInt(value).toLocaleString('vi-VN');
                }

                filters.push({
                    name: input.name,
                    label,
                    value
                });
            }
        });

        // Update summary text
        if (filters.length > 0) {
            summaryDiv.textContent = `Đang áp dụng ${filters.length} bộ lọc`;
            activeFiltersDiv.style.display = 'block';

            // Create filter tags
            const tagsHtml = filters.map(filter =>
                `<span class="badge bg-primary me-2 mb-2">
                ${filter.label}: ${filter.value}
                <button type="button" class="btn-close btn-close-white ms-1" 
                        onclick="removeFilter('${filter.name}')"></button>
            </span>`
            ).join('');

            activeFiltersDiv.querySelector('.d-flex').innerHTML = tagsHtml;
        } else {
            summaryDiv.textContent = '';
            activeFiltersDiv.style.display = 'none';
        }
    }

    function removeFilter(filterName) {
        const input = document.querySelector(`[name="${filterName}"]`);
        if (input) {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
            applyFilters();
        }
    }

    // Initialize filter summary on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateFilterSummary();

        // Update summary when filters change
        const inputs = document.querySelectorAll('input[name], select[name]');
        inputs.forEach(input => {
            input.addEventListener('change', updateFilterSummary);
        });
    });
</script>