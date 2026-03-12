<?php
// require_once 'models/admin/Guide.php';

class GuideController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Guide();
    }

    public function index()
    {
        $guides = $this->model->getAll();
        require_once PATH_VIEW_ADMIN . 'pages/guides/index.php';
    }

    public function create()
    {
        // Không cần load thêm data
        require_once PATH_VIEW_ADMIN . 'pages/guides/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . BASE_URL_ADMIN . '&action=guides/create');
            exit;
        }

        try {
            // Validate inputs
            $full_name = $_POST['full_name'] ?? null;
            $email = $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $birth_date = $_POST['birth_date'] ?? null;
            $address = $_POST['address'] ?? '';
            $id_card = $_POST['id_card'] ?? '';

            // Guide specific fields
            $languages = $_POST['languages'] ?? '';
            $experience_years = $_POST['experience_years'] ?? 0;
            $health_status = $_POST['health_status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $guide_type = $_POST['guide_type'] ?? 'domestic';
            $specialization = $_POST['specialization'] ?? '';

            // Basic validation
            if (!$full_name || !$email || !$phone) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
                header('Location:' . BASE_URL_ADMIN . '&action=guides/create');
                exit;
            }

            // Handle avatar upload
            $avatar = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/users/guide/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $new_filename = 'guide_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    $avatar = $upload_path;
                }
            }

            // Prepare user data
            $userData = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'role' => 'guide',
                'password_hash' => password_hash('123456', PASSWORD_DEFAULT) // Default password
            ];

            if ($avatar) {
                $userData['avatar'] = $avatar;
            }

            // Prepare guide data
            $guideData = [
                'languages' => $languages,
                'experience_years' => (int)$experience_years,
                'rating' => 0,
                'health_status' => $health_status,
                'notes' => $notes
            ];

            // Create guide
            $guide_id = $this->model->createGuide($userData, $guideData);

            $_SESSION['success'] = 'Tạo hướng dẫn viên thành công';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location:' . BASE_URL_ADMIN . '&action=guides/create');
            exit;
        }
    }

    public function detail()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy hướng dẫn viên';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        $guide = $this->model->getGuideWithDetails($id);

        if (!$guide) {
            $_SESSION['error'] = 'Hướng dẫn viên không tồn tại';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/guides/detail.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy hướng dẫn viên';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        $guide = $this->model->getGuideWithDetails($id);

        if (!$guide) {
            $_SESSION['error'] = 'Hướng dẫn viên không tồn tại';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        require_once PATH_VIEW_ADMIN . 'pages/guides/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy hướng dẫn viên';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        try {
            // Get existing guide
            $existingGuide = $this->model->getGuideWithDetails($id);
            if (!$existingGuide) {
                throw new Exception('Guide not found');
            }

            // Validate inputs
            $full_name = $_POST['full_name'] ?? null;
            $email = $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $birth_date = $_POST['birth_date'] ?? null;
            $address = $_POST['address'] ?? '';
            $id_card = $_POST['id_card'] ?? '';

            // Guide specific fields
            $languages = $_POST['languages'] ?? '';
            $experience_years = $_POST['experience_years'] ?? 0;
            $health_status = $_POST['health_status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $guide_type = $_POST['guide_type'] ?? 'domestic';
            $specialization = $_POST['specialization'] ?? '';

            // Basic validation
            if (!$full_name || !$email || !$phone) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
                header('Location:' . BASE_URL_ADMIN . '&action=guides/edit&id=' . $id);
                exit;
            }

            // Handle avatar upload
            $avatar = $existingGuide['avatar']; // Keep existing avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/users/guide/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $new_filename = 'guide_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Delete old avatar if exists
                    if ($existingGuide['avatar'] && file_exists($existingGuide['avatar'])) {
                        unlink($existingGuide['avatar']);
                    }
                    $avatar = $upload_path;
                }
            }

            // Prepare user data
            $userData = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'avatar' => $avatar
            ];

            // Prepare guide data
            $guideData = [
                'languages' => $languages,
                'experience_years' => (int)$experience_years,
                'health_status' => $health_status,
                'notes' => $notes
            ];

            // Update guide
            $this->model->updateGuide($id, $userData, $guideData);

            $_SESSION['success'] = 'Cập nhật hướng dẫn viên thành công';
            header('Location:' . BASE_URL_ADMIN . '&action=guides/detail&id=' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            header('Location:' . BASE_URL_ADMIN . '&action=guides/edit&id=' . $id);
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy hướng dẫn viên';
            header('Location:' . BASE_URL_ADMIN . '&action=guides');
            exit;
        }

        try {
            $result = $this->model->deleteGuide($id);

            if ($result) {
                $_SESSION['success'] = 'Xóa hướng dẫn viên thành công';
            } else {
                $_SESSION['error'] = 'Không thể xóa hướng dẫn viên';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location:' . BASE_URL_ADMIN . '&action=guides');
        exit;
    }
}
