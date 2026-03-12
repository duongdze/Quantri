<?php
class TourController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Tour();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(5, min(50, (int)$_GET['per_page'])) : 12;

        // Build filters from GET parameters
        $filters = [];

        // Search keyword
        if (!empty($_GET['keyword'])) {
            $filters['keyword'] = trim($_GET['keyword']);
        }

        // Category filter
        if (!empty($_GET['category_id'])) {
            $filters['category_id'] = (int)$_GET['category_id'];
        }



        // Date range filters
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        // Price range filters
        if (!empty($_GET['price_min'])) {
            $filters['price_min'] = (float)$_GET['price_min'];
        }
        if (!empty($_GET['price_max'])) {
            $filters['price_max'] = (float)$_GET['price_max'];
        }

        // Rating filter
        if (!empty($_GET['rating_min'])) {
            $filters['rating_min'] = (float)$_GET['rating_min'];
        }

        // Sorting
        if (!empty($_GET['sort_by'])) {
            $filters['sort_by'] = $_GET['sort_by'];
            $filters['sort_dir'] = $_GET['sort_dir'] ?? 'DESC';
        }

        $result = $this->model->getAllTours($page, $perPage, $filters);
        $tours = $result['data'];
        $pagination = [
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
        ];

        $categoryModel = new TourCategory();
        $categories = $categoryModel->select();

        // Get statistics for header
        $stats = $this->getTourStatistics();

        require_once PATH_VIEW_ADMIN . 'pages/tours/index.php';
    }

    private function getTourStatistics()
    {
        // Use the new optimized statistics method
        return $this->model->getStatistics();
    }
    public function create()
    {
        // Load policies
        $policyModel = new TourPolicy();
        $policies = $policyModel->select();

        // Load categories for category dropdown
        $categoryModel = new TourCategory();
        $categories = $categoryModel->select();

        // Load suppliers for supplier dropdown
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->select();

        require_once PATH_VIEW_ADMIN . 'pages/tours/create.php';
    }

    public function store()
    {
        // Validate required fields
        $requiredFields = ['name', 'category_id', 'base_price'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Trường {$field} là bắt buộc.";
                header('Location: ' . BASE_URL_ADMIN . '&action=tours/create');
                return;
            }
        }

        // Additional validation
        if (strlen(trim($_POST['name'])) < 3) {
            $_SESSION['error'] = "Tên tour phải có ít nhất 3 ký tự.";
            header('Location: ' . BASE_URL_ADMIN . '&action=tours/create');
            return;
        }

        if ((float)$_POST['base_price'] <= 0) {
            $_SESSION['error'] = "Giá cơ bản phải lớn hơn 0.";
            header('Location: ' . BASE_URL_ADMIN . '&action=tours/create');
            return;
        }

        try {
            // Prepare tour basic data
            $tourData = [
                'name' => trim($_POST['name']),
                'category_id' => (int)$_POST['category_id'],
                'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
                'description' => trim($_POST['description'] ?? ''),
                'base_price' => (float)$_POST['base_price'],
            ];

            // Handle image uploads with security checks
            $uploadedImages = [];
            $uploadDir = PATH_ASSETS_UPLOADS . 'tours/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            // Handle main image upload (if provided)
            if (!empty($_FILES['main_image']['tmp_name'])) {
                $tmpName = $_FILES['main_image']['tmp_name'];
                $originalName = $_FILES['main_image']['name'];
                $fileType = $_FILES['main_image']['type'];
                $fileSize = $_FILES['main_image']['size'];

                // Validate file type
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Loại file không được phép: {$originalName}");
                }

                // Validate file size
                if ($fileSize > $maxFileSize) {
                    throw new Exception("File quá lớn (tối đa 5MB): {$originalName}");
                }

                // Validate file is actually an image
                if (!getimagesize($tmpName)) {
                    throw new Exception("File không phải là hình ảnh hợp lệ: {$originalName}");
                }

                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $newName = uniqid('tour_main_') . '.' . $extension;
                $filePath = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $uploadedImages[] = [
                        'path' => 'tours/' . $newName,
                        'is_main' => true
                    ];
                } else {
                    throw new Exception("Không thể tải lên ảnh đại diện: {$originalName}");
                }
            }

            // Handle gallery images upload
            if (!empty($_FILES['gallery_images']['name'][0])) {
                foreach ($_FILES['gallery_images']['tmp_name'] as $index => $tmpName) {
                    if (!empty($tmpName)) {
                        $originalName = $_FILES['gallery_images']['name'][$index];
                        $fileType = $_FILES['gallery_images']['type'][$index];
                        $fileSize = $_FILES['gallery_images']['size'][$index];

                        // Validate file type
                        if (!in_array($fileType, $allowedTypes)) {
                            throw new Exception("Loại file không được phép: {$originalName}");
                        }

                        // Validate file size
                        if ($fileSize > $maxFileSize) {
                            throw new Exception("File quá lớn (tối đa 5MB): {$originalName}");
                        }

                        // Validate file is actually an image
                        if (!getimagesize($tmpName)) {
                            throw new Exception("File không phải là hình ảnh hợp lệ: {$originalName}");
                        }

                        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                        $newName = uniqid('tour_') . '.' . $extension;
                        $filePath = $uploadDir . $newName;

                        if (move_uploaded_file($tmpName, $filePath)) {
                            // If no main image uploaded, make first gallery image as main
                            $isMain = (count($uploadedImages) === 0 && $index === 0);
                            
                            $uploadedImages[] = [
                                'path' => 'tours/' . $newName,
                                'is_main' => $isMain
                            ];
                        } else {
                            throw new Exception("Không thể tải lên file: {$originalName}");
                        }
                    }
                }
            }

            // Parse JSON data from form
            $pricingOptions = json_decode($_POST['tour_pricing_options'] ?? '[]', true);
            $dynamicPricing = json_decode($_POST['version_dynamic_pricing'] ?? '[]', true);
            $itineraries = json_decode($_POST['tour_itinerary'] ?? '[]', true);
            $partners = json_decode($_POST['tour_partners'] ?? '[]', true);
            $policyIds = $_POST['policies'] ?? [];

            // Create tour with all related data including versions
            $tourId = $this->model->createTour($tourData, $pricingOptions, $dynamicPricing, $itineraries, $partners, $uploadedImages, $policyIds);

            $_SESSION['success'] = 'Tour đã được tạo thành công!';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo tour: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=tours/create');
            exit;
        }
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID Tour không hợp lệ.';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        // Load main tour
        $tour = $this->model->find('*', 'id = :id', ['id' => $id]);
        if (!$tour) {
            $_SESSION['error'] = 'Không tìm thấy Tour.';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        // Load dropdown data
        $categoryModel = new TourCategory();
        $categories = $categoryModel->select();

        // Load suppliers
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->select();

        // Load policies
        $policyModel = new TourPolicy();
        $policies = $policyModel->select();

        $policyAssignmentModel = new TourPolicyAssignment();
        $assignedPolicies = $policyAssignmentModel->getByTourId($id);
        $assignedPolicyIds = array_column($assignedPolicies, 'policy_id');

        // Related entities
        $pricingModel = new TourPricing();
        $pricingOptions = $pricingModel->getByTourId($id);

        $dynamicPricing = []; // Temporarily empty until we update the logic

        $itineraryModel = new TourItinerary();
        $itinerarySchedule = $itineraryModel->select('*', 'tour_id = :tid', ['tid' => $id], 'day_number ASC');

        $partnerModel = new TourPartner();
        $partnerServices = $partnerModel->getByTourId($id);

        $departureModel = new TourDeparture();
        $departures = $departureModel->getByTourId($id);

        $imageModel = new TourImage();
        $images = $imageModel->getByTourId($id);

        // Map images to objects {id, url} for the edit view (use public URL)
        $allImages = array_map(function ($img) {
            return [
                'id' => $img['id'] ?? null,
                // public URL for preview
                'url' => BASE_ASSETS_UPLOADS . ($img['image_url'] ?? ''),
                // relative path stored in DB, used for delete/matching on server-side
                'path' => $img['image_url'] ?? '',
                'main' => !empty($img['main_img']) ? 1 : 0,
            ];
        }, $images ?: []);

        require_once PATH_VIEW_ADMIN . 'pages/tours/edit.php';
    }



    public function update()
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID Tour không hợp lệ.';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        // Basic validation
        $requiredFields = ['name', 'category_id', 'base_price'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Trường {$field} là bắt buộc.";
                header('Location: ' . BASE_URL_ADMIN . '&action=tours/edit&id=' . urlencode($id));
                return;
            }
        }

        try {
            $this->model->beginTransaction();

            // Prepare tour basic data for update
            $tourData = [
                'name' => trim($_POST['name']),
                'category_id' => (int)$_POST['category_id'],
                'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
                'description' => $_POST['description'] ?? '',
                'base_price' => (float)$_POST['base_price'],
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update tour basic info
            $this->model->update($tourData, 'id = :id', ['id' => $id]);

            $imageModel = new TourImage();

            // 1) Handle deleted images (could be IDs or URLs)
            $deleted = $_POST['deleted_images'] ?? [];
            if (!empty($deleted) && is_array($deleted)) {
                foreach ($deleted as $del) {
                    // If numeric -> treat as id
                    if (ctype_digit((string)$del)) {
                        $img = $imageModel->find('*', 'id = :id', ['id' => $del]);
                        if ($img) {
                            $path = PATH_ASSETS_UPLOADS . ($img['image_url'] ?? '');
                            if (!empty($img['image_url']) && file_exists($path)) {
                                @unlink($path);
                            }
                            $imageModel->delete('id = :id', ['id' => $del]);
                        }
                    } else {
                        // treat as URL (relative path)
                        $img = $imageModel->find('*', 'image_url = :url AND tour_id = :tid', ['url' => $del, 'tid' => $id]);
                        if ($img) {
                            $path = PATH_ASSETS_UPLOADS . ($img['image_url'] ?? '');
                            if (!empty($img['image_url']) && file_exists($path)) {
                                @unlink($path);
                            }
                            $imageModel->delete('id = :id', ['id' => $img['id']]);
                        }
                    }
                }
            }

            // Handle main image deletion
            if (!empty($_POST['delete_main_image'])) {
                // Find current main image
                $mainImg = $imageModel->find('*', 'tour_id = :tid AND main_img = 1', ['tid' => $id]);
                if ($mainImg) {
                    $path = PATH_ASSETS_UPLOADS . ($mainImg['image_url'] ?? '');
                    if (!empty($mainImg['image_url']) && file_exists($path)) {
                        @unlink($path);
                    }
                    $imageModel->delete('id = :id', ['id' => $mainImg['id']]);
                }
            }

            // 2) Handle primary image selection (existing image id or new image index)
            $primarySelection = $_POST['primary_image_selection'] ?? '';
            $newMainImageIndex = -1;

            if (!empty($primarySelection)) {
                // If existing image selected
                if (strpos($primarySelection, 'existing_') === 0) {
                    $existingId = substr($primarySelection, 9);
                    // reset previous main flags
                    $stmt = BaseModel::getPdo()->prepare("UPDATE tour_gallery_images SET main_img = 0 WHERE tour_id = :tid");
                    $stmt->execute(['tid' => $id]);

                    $imageModel->update(['main_img' => 1], 'id = :id', ['id' => $existingId]);
                }
                // If new image selected
                elseif (strpos($primarySelection, 'new_') === 0) {
                    $newMainImageIndex = (int)substr($primarySelection, 4);
                    // We will handle this in the upload loop
                    // Also reset previous main flags now, assuming the new one will be set
                    $stmt = BaseModel::getPdo()->prepare("UPDATE tour_gallery_images SET main_img = 0 WHERE tour_id = :tid");
                    $stmt->execute(['tid' => $id]);
                }
            }

            // 3) Handle uploaded files: main single `image` and gallery `gallery_images[]`
            $uploadDir = PATH_ASSETS_UPLOADS . 'tours/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Debug: Log upload directory
            error_log("Upload directory: " . $uploadDir);
            error_log("Upload directory exists: " . (is_dir($uploadDir) ? 'Yes' : 'No'));
            error_log("Upload directory writable: " . (is_writable($uploadDir) ? 'Yes' : 'No'));

            // Debug: Log toàn bộ FILES data
            error_log("Complete FILES data: " . print_r($_FILES, true));

            // If a new primary file was uploaded (single file input named `main_image`)
            if (!empty($_FILES['main_image']['tmp_name'])) {
                error_log("Main image upload detected");
                if (is_uploaded_file($_FILES['main_image']['tmp_name'])) {
                    $originalName = $_FILES['main_image']['name'];
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newName = uniqid('tour_') . '.' . $extension;
                    $filePath = $uploadDir . $newName;
                    error_log("Moving file from " . $_FILES['main_image']['tmp_name'] . " to " . $filePath);
                    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $filePath)) {
                        error_log("Main image upload successful");
                        // clear previous main flags
                        $stmt = BaseModel::getPdo()->prepare("UPDATE tour_gallery_images SET main_img = 0 WHERE tour_id = :tid");
                        $stmt->execute(['tid' => $id]);

                        // insert new image as main (append sort_order at end)
                        $maxOrder = 0;
                        $row = BaseModel::getPdo()->prepare("SELECT MAX(sort_order) as mo FROM tour_gallery_images WHERE tour_id = :tid");
                        $row->execute(['tid' => $id]);
                        $r = $row->fetch();
                        if ($r && isset($r['mo'])) $maxOrder = (int)$r['mo'];

                        $imageModel->insert([
                            'tour_id' => $id,
                            'image_url' => 'tours/' . $newName,
                            'caption' => '',
                            'main_img' => 1,
                            'sort_order' => $maxOrder + 1,
                        ]);
                        error_log("Main image inserted into database");
                    } else {
                        error_log("Failed to move main image file");
                    }
                }
            } else {
                error_log("No main image upload detected");
            }

            // Multiple gallery uploads with security checks
            error_log("Gallery upload check - FILES data: " . print_r($_FILES['gallery_images'], true));
            if (!empty($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['tmp_name'])) {
                error_log("Processing gallery uploads");
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                foreach ($_FILES['gallery_images']['tmp_name'] as $index => $tmpName) {
                    if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                        error_log("Processing gallery image $index");
                        $originalName = $_FILES['gallery_images']['name'][$index];
                        $fileType = $_FILES['gallery_images']['type'][$index];
                        $fileSize = $_FILES['gallery_images']['size'][$index];

                        error_log("Gallery file: $originalName, type: $fileType, size: $fileSize");

                        // Validate file type
                        if (!in_array($fileType, $allowedTypes)) {
                            throw new Exception("Loại file không được phép: {$originalName}");
                        }

                        // Validate file size
                        if ($fileSize > $maxFileSize) {
                            throw new Exception("File quá lớn (tối đa 5MB): {$originalName}");
                        }

                        // Validate file is actually an image
                        if (!getimagesize($tmpName)) {
                            throw new Exception("File không phải là hình ảnh hợp lệ: {$originalName}");
                        }

                        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                        $newName = uniqid('tour_') . '.' . $extension;
                        $filePath = $uploadDir . $newName;

                        error_log("Moving gallery file from $tmpName to $filePath");
                        if (move_uploaded_file($tmpName, $filePath)) {
                            error_log("Gallery image $index upload successful");
                            // insert as non-main by default
                            $maxOrder = 0;
                            $row = BaseModel::getPdo()->prepare("SELECT MAX(sort_order) as mo FROM tour_gallery_images WHERE tour_id = :tid");
                            $row->execute(['tid' => $id]);
                            $r = $row->fetch();
                            if ($r && isset($r['mo'])) $maxOrder = (int)$r['mo'];

                            $imageModel->insert([
                                'tour_id' => $id,
                                'image_url' => 'tours/' . $newName,
                                'caption' => '',
                                'main_img' => ($index == $newMainImageIndex) ? 1 : 0,
                                'sort_order' => $maxOrder + 1,
                            ]);
                            error_log("Gallery image $index inserted into database");
                        } else {
                            error_log("Failed to move gallery file $index");
                            throw new Exception("Không thể tải lên file: {$originalName}");
                        }
                    }
                }
            } else {
                error_log("No gallery images detected for upload");
            }

            // Parse JSON arrays for pricing/itineraries/partners and update related tables
            $pricingOptions = json_decode($_POST['tour_pricing_options'] ?? '[]', true);
            $dynamicPricing = json_decode($_POST['version_dynamic_pricing'] ?? '[]', true);
            $itineraries = json_decode($_POST['tour_itinerary'] ?? '[]', true);
            $partners = json_decode($_POST['tour_partners'] ?? '[]', true);

            // For simplicity: delete existing related rows and re-insert
            $pricingModel = new TourPricing();
            $dynamicPricingModel = new TourDynamicPricing();
            $itineraryModel = new TourItinerary();
            $partnerModel = new TourPartner();

            $pricingModel->delete('tour_id = :tid', ['tid' => $id]);
            foreach ($pricingOptions as $opt) {
                $optionId = $pricingModel->insert([
                    'tour_id' => $id,
                    'label' => $opt['label'] ?? '',
                    'description' => $opt['description'] ?? '',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $itineraryModel->delete('tour_id = :tid', ['tid' => $id]);
            foreach ($itineraries as $index => $it) {
                $dayNumber = $index + 1;
                if (isset($it['day_number'])) {
                    $dayNumber = (int)$it['day_number'];
                }
                $itineraryModel->insert([
                    'tour_id' => $id,
                    'day_label' => "Ngày {$dayNumber}",
                    'day_number' => $dayNumber,
                    'time_start' => $it['time_start'] ?? null,
                    'time_end' => $it['time_end'] ?? null,
                    'title' => $it['title'] ?? '',
                    'description' => $it['description'] ?? '',
                    'activities' => $it['description'] ?? '',
                    'image_url' => $it['image_url'] ?? '',
                ]);
            }

            // Handle departures
            $departureModel = new TourDeparture();
            $departureModel->delete('tour_id = :tid', ['tid' => $id]);
            $departures = json_decode($_POST['tour_departures'] ?? '[]', true);
            foreach ($departures as $dep) {
                $departureModel->insert([
                    'tour_id' => $id,
                    'departure_date' => $dep['departure_date'] ?? null,
                    'max_seats' => (int)($dep['max_seats'] ?? 40),
                    'price_adult' => (float)($dep['price_adult'] ?? 0),
                    'price_child' => (float)($dep['price_child'] ?? 0),
                    'status' => $dep['status'] ?? 'open',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $partnerModel->delete('tour_id = :tid', ['tid' => $id]);
            foreach ($partners as $p) {
                $partnerModel->insert([
                    'tour_id' => $id,
                    'service_type' => $p['service_type'] ?? 'other',
                    'partner_name' => $p['partner_name'] ?? '',
                    'contact' => $p['partner_contact'] ?? '',
                    'notes' => $p['notes'] ?? '',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Update policies
            $policyAssignmentModel = new TourPolicyAssignment();
            $policyAssignmentModel->delete('tour_id = :tid', ['tid' => $id]);
            $policyIds = $_POST['policies'] ?? [];
            foreach ($policyIds as $policyId) {
                $policyAssignmentModel->insert([
                    'tour_id' => $id,
                    'policy_id' => $policyId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->model->commit();

            $_SESSION['success'] = 'Cập nhật tour thành công.';
            // Prefer returning to provided `return_to` (only if internal), otherwise fallback to tours list
            $returnTo = $_POST['return_to'] ?? '';
            if ($returnTo && strpos($returnTo, BASE_URL_ADMIN) === 0) {
                header('Location: ' . $returnTo);
            } else {
                header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            }
            exit;
        } catch (Exception $e) {
            $this->model->rollBack();
            $_SESSION['error'] = 'Có lỗi khi cập nhật tour: ' . $e->getMessage();
            header('Location: ' . BASE_URL_ADMIN . '&action=tours/edit&id=' . urlencode($id));
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location:' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        try {
            // Check for existing bookings
            $bookingModel = new Booking();
            $bookingCount = $bookingModel->count('tour_id = :id', ['id' => $id]);

            if ($bookingCount > 0) {
                $_SESSION['error'] = 'Không thể xóa tour này vì đã có ' . $bookingCount . ' booking liên quan.';
                header('Location:' . BASE_URL_ADMIN . '&action=tours');
                return;
            }

            $result = $this->model->removeTour($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa tour thành công!';
            } else {
                throw new Exception('Không thể xóa tour');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        header('Location:' . BASE_URL_ADMIN . '&action=tours');
    }
    public function detail()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID Tour không hợp lệ.';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        // Load main tour with category name and version info using custom query
        $pdo = BaseModel::getPdo();
        $stmt = $pdo->prepare("
            SELECT t.*, tc.name as category_name
            FROM tours t 
            LEFT JOIN tour_categories tc ON t.category_id = tc.id 
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $tour = $stmt->fetch();

        if (!$tour) {
            $_SESSION['error'] = 'Không tìm thấy Tour.';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        // Load related data for detail view
        $pricingModel = new TourPricing();
        $pricingOptions = $pricingModel->getByTourId($id);

        // TODO: Dynamic pricing now uses version_id/departure_id instead of tour_id
        // $dynamicPricingModel = new TourDynamicPricing();
        $dynamicPricing = []; // Temporarily empty until we update the logic

        $itineraryModel = new TourItinerary();
        $itinerarySchedule = $itineraryModel->select('*', 'tour_id = :tid', ['tid' => $id], 'day_number ASC');

        $partnerModel = new TourPartner();
        $partnerServices = $partnerModel->getByTourId($id);

        $policyAssignmentModel = new TourPolicyAssignment();
        $assignedPolicies = $policyAssignmentModel->getByTourId($id);
        // Fetch full policy details
        $policyModel = new TourPolicy();
        $policies = [];
        foreach ($assignedPolicies as $ap) {
            $p = $policyModel->findById($ap['policy_id']);
            if ($p) {
                $policies[] = $p;
            }
        }

        $imageModel = new TourImage();
        $images = $imageModel->getByTourId($id);
        $allImages = array_map(function ($img) {
            return [
                'id' => $img['id'] ?? null,
                'url' => BASE_ASSETS_UPLOADS . ($img['image_url'] ?? ''),
                'main' => !empty($img['main_img']) ? 1 : 0,
            ];
        }, $images ?: []);

        // Also compute avg rating and booking count if not present
        if (!isset($tour['avg_rating'])) {
            $stmt = BaseModel::getPdo()->prepare("SELECT AVG(rating) as avg_rating FROM tour_feedbacks WHERE tour_id = :tid");
            $stmt->execute(['tid' => $id]);
            $tour['avg_rating'] = $stmt->fetch()['avg_rating'] ?? 0;
        }
        if (!isset($tour['booking_count'])) {
            $stmt = BaseModel::getPdo()->prepare("SELECT COUNT(*) as bc FROM bookings WHERE tour_id = :tid");
            $stmt->execute(['tid' => $id]);
            $tour['booking_count'] = $stmt->fetch()['bc'] ?? 0;
        }


        // Load departures (lịch khởi hành)
        $departureModel = new class extends BaseModel {
            protected $table = 'tour_departures';
        };
        $departures = $departureModel->select('*', 'tour_id = :tid', ['tid' => $id], 'departure_date ASC');
        
        // Normalize commonly expected fields for the detail view
        $tour['subtitle'] = $tour['subtitle'] ?? ($tour['short_description'] ?? '');
        $tour['duration'] = $tour['duration'] ?? ($tour['days'] ?? '');
        $tour['capacity'] = $tour['capacity'] ?? ($tour['seats'] ?? '');
        $tour['start_date'] = $tour['start_date'] ?? ($tour['next_start_date'] ?? '');

        require_once PATH_VIEW_ADMIN . 'pages/tours/detail.php';
    }

    /**
     * Toggle tour status (AJAX endpoint)
     */
    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['_method']) || $_POST['_method'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            $result = $this->model->toggleStatus($id);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to toggle status');
            }
        } catch (Exception $e) {
            error_log('Error toggling tour status: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Toggle featured status (AJAX endpoint)
     */
    public function toggleFeatured()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['_method']) || $_POST['_method'] !== 'PATCH') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            $result = $this->model->toggleFeatured($id);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to toggle featured status');
            }
        } catch (Exception $e) {
            error_log('Error toggling tour featured status: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Bulk update tour status
     */
    public function bulkUpdateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Method not allowed';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        $ids = $_POST['tour_ids'] ?? [];
        $status = $_POST['status'] ?? '';

        if (empty($ids) || !in_array($status, ['active', 'inactive'])) {
            $_SESSION['error'] = 'Invalid request data';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        try {
            $result = $this->model->bulkUpdateStatus($ids, $status);

            if ($result) {
                $_SESSION['success'] = "Cập nhật trạng thái thành công cho " . count($ids) . " tour";
            } else {
                throw new Exception('Failed to update status');
            }
        } catch (Exception $e) {
            error_log('Error bulk updating tour status: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật trạng thái';
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=tours');
    }

    /**
     * Bulk delete tours
     */
    public function bulkDelete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Method not allowed';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        $ids = $_POST['tour_ids'] ?? [];

        if (empty($ids)) {
            $_SESSION['error'] = 'Vui lòng chọn ít nhất một tour để xóa';
            header('Location: ' . BASE_URL_ADMIN . '&action=tours');
            return;
        }

        try {
            $result = $this->model->bulkDelete($ids);

            if ($result) {
                $_SESSION['success'] = "Xóa thành công " . count($ids) . " tour";
            } else {
                throw new Exception('Failed to delete tours');
            }
        } catch (Exception $e) {
            error_log('Error bulk deleting tours: ' . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa tour: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL_ADMIN . '&action=tours');
    }

    /**
     * Search tours (AJAX endpoint)
     */
    public function search()
    {
        $keyword = $_GET['q'] ?? '';
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'status' => $_GET['status'] ?? 'active',
            'difficulty_level' => $_GET['difficulty_level'] ?? null,
            'price_min' => $_GET['price_min'] ?? null,
            'price_max' => $_GET['price_max'] ?? null
        ];

        try {
            $tours = $this->model->searchTours($keyword, $filters, 20);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $tours]);
        } catch (Exception $e) {
            error_log('Error searching tours: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Get tours by status (AJAX endpoint)
     */
    public function getByStatus()
    {
        $status = $_GET['status'] ?? 'active';
        $limit = $_GET['limit'] ?? 10;

        try {
            $tours = $this->model->getByStatus($status, $limit);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $tours]);
        } catch (Exception $e) {
            error_log('Error getting tours by status: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
}
