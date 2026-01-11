<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/ThemeModel.php';

class ThemeController {
    private $themeModel;
    
    public function __construct() {
        $this->themeModel = new ThemeModel();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'index';
        
        switch ($action) {
            case 'save_settings':
                $this->saveSettings();
                break;
            case 'get_settings':
                $this->getSettings();
                break;
            case 'upload_banner':
                $this->uploadBanner();
                break;
            case 'delete_banner':
                $this->deleteBanner();
                break;
            case 'update_banner':
                $this->updateBanner();
                break;
            case 'create_event':
                $this->createEvent();
                break;
            case 'update_event':
                $this->updateEvent();
                break;
            case 'delete_event':
                $this->deleteEvent();
                break;
            case 'get_banners':
                $this->getBanners();
                break;
            case 'get_events':
                $this->getEvents();
                break;
            case 'apply_preset':
                $this->applyPreset();
                break;
            case 'reorder_banners':
                $this->reorderBanners();
                break;
            case 'upload_website_bg':
                $this->uploadWebsiteBackground();
                break;
            case 'remove_website_bg':
                $this->removeWebsiteBackground();
                break;
            case 'upload_hero_bg':
                $this->uploadHeroBackground();
                break;
            case 'remove_hero_bg':
                $this->removeHeroBackground();
                break;
            case 'get_presets':
                $this->getPresets();
                break;
            case 'add_preset':
                $this->addPreset();
                break;
            case 'update_preset':
                $this->updatePresetAction();
                break;
            case 'delete_preset':
                $this->deletePresetAction();
                break;
            case 'activate_preset':
                $this->activatePresetAction();
                break;
            default:
                $this->index();
        }
    }

    private function saveSettings() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $settings = $_POST['settings'] ?? [];
            
            if (empty($settings)) {
                throw new Exception('Không có dữ liệu để lưu');
            }
            
            foreach ($settings as $key => $value) {
                $type = $this->getSettingType($key);
                $this->themeModel->setThemeSetting($key, $value, $type);
            }
            
            echo json_encode(['success' => true, 'message' => 'Cài đặt đã được lưu'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function getSettings() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $settings = $this->themeModel->getAllThemeSettings();
            echo json_encode(['success' => true, 'data' => $settings], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function uploadBanner() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            if (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Không có file được upload hoặc có lỗi xảy ra');
            }
            
            $file = $_FILES['banner_image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Định dạng file không được hỗ trợ');
            }
            
            $uploadDir = BASE_PATH . '/public/uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Không thể lưu file');
            }
            
            $imagePath = '/public/uploads/banners/' . $filename;
            $title = $_POST['title'] ?? null;
            $eventType = $_POST['event_type'] ?? 'default';
            $animationType = $_POST['animation_type'] ?? 'fade';
            $transitionDuration = intval($_POST['transition_duration'] ?? 500);
            
            $result = $this->themeModel->addBanner($imagePath, $title, $eventType, $animationType, $transitionDuration);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Upload banner thành công',
                    'image_path' => $imagePath
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể lưu thông tin banner vào database');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function deleteBanner() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID không hợp lệ');
            }
            
            $result = $this->themeModel->deleteBanner($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa banner thành công'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể xóa banner');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function updateBanner() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID không hợp lệ');
            }
            
            $data = [];
            if (isset($_POST['title'])) $data['title'] = $_POST['title'];
            if (isset($_POST['description'])) $data['description'] = $_POST['description'];
            if (isset($_POST['event_type'])) $data['event_type'] = $_POST['event_type'];
            if (isset($_POST['is_active'])) $data['is_active'] = intval($_POST['is_active']);
            if (isset($_POST['sort_order'])) $data['sort_order'] = intval($_POST['sort_order']);
            if (isset($_POST['animation_type'])) $data['animation_type'] = $_POST['animation_type'];
            if (isset($_POST['transition_duration'])) $data['transition_duration'] = intval($_POST['transition_duration']);
            
            $result = $this->themeModel->updateBanner($id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật banner thành công'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể cập nhật banner');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function createEvent() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $data = [
                'event_name' => $_POST['event_name'] ?? '',
                'event_type' => $_POST['event_type'] ?? 'default',
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'is_active' => intval($_POST['is_active'] ?? 1),
                'theme_config' => []
            ];
            
            if (isset($_POST['primary_color'])) {
                $data['theme_config']['primary_color'] = $_POST['primary_color'];
            }
            if (isset($_POST['secondary_color'])) {
                $data['theme_config']['secondary_color'] = $_POST['secondary_color'];
            }
            if (isset($_POST['accent_color'])) {
                $data['theme_config']['accent_color'] = $_POST['accent_color'];
            }
            
            $result = $this->themeModel->createEvent($data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Tạo sự kiện thành công'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể tạo sự kiện');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function updateEvent() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID không hợp lệ');
            }
            
            $data = [];
            if (isset($_POST['event_name'])) $data['event_name'] = $_POST['event_name'];
            if (isset($_POST['event_type'])) $data['event_type'] = $_POST['event_type'];
            if (isset($_POST['start_date'])) $data['start_date'] = $_POST['start_date'];
            if (isset($_POST['end_date'])) $data['end_date'] = $_POST['end_date'];
            if (isset($_POST['is_active'])) $data['is_active'] = intval($_POST['is_active']);
            
            if (isset($_POST['primary_color']) || isset($_POST['secondary_color']) || isset($_POST['accent_color'])) {
                $data['theme_config'] = [];
                if (isset($_POST['primary_color'])) $data['theme_config']['primary_color'] = $_POST['primary_color'];
                if (isset($_POST['secondary_color'])) $data['theme_config']['secondary_color'] = $_POST['secondary_color'];
                if (isset($_POST['accent_color'])) $data['theme_config']['accent_color'] = $_POST['accent_color'];
            }
            
            $result = $this->themeModel->updateEvent($id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật sự kiện thành công'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể cập nhật sự kiện');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function deleteEvent() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID không hợp lệ');
            }
            
            $result = $this->themeModel->deleteEvent($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa sự kiện thành công'], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể xóa sự kiện');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function getBanners() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $banners = $this->themeModel->getAllBanners();
            echo json_encode(['success' => true, 'data' => $banners], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function getEvents() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $events = $this->themeModel->getAllEvents();
            echo json_encode(['success' => true, 'data' => $events], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function applyPreset() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $presetKey = $_POST['preset'] ?? '';
            
            $presets = [
                'default' => [
                    'primary_color' => '#4f46e5',
                    'secondary_color' => '#7c3aed',
                    'accent_color' => '#10b981',
                    'background_color' => '#ffffff',
                    'text_color' => '#1f2937',
                    'sidebar_bg' => '#ffffff',
                    'header_bg' => '#ffffff'
                ],
                'noel' => [
                    'primary_color' => '#dc2626',
                    'secondary_color' => '#16a34a',
                    'accent_color' => '#ffffff',
                    'background_color' => '#fef2f2',
                    'text_color' => '#1f2937',
                    'sidebar_bg' => '#ffffff',
                    'header_bg' => '#ffffff'
                ],
                'tet' => [
                    'primary_color' => '#dc2626',
                    'secondary_color' => '#fbbf24',
                    'accent_color' => '#fde047',
                    'background_color' => '#fef2f2',
                    'text_color' => '#1f2937',
                    'sidebar_bg' => '#ffffff',
                    'header_bg' => '#ffffff'
                ]
            ];
            
            if (!isset($presets[$presetKey])) {
                throw new Exception('Preset không hợp lệ');
            }
            
            $preset = $presets[$presetKey];
            
            foreach ($preset as $key => $value) {
                $type = $this->getSettingType($key);
                $this->themeModel->setThemeSetting($key, $value, $type);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã áp dụng preset thành công',
                'preset' => $preset
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function reorderBanners() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $ordersJson = $_POST['orders'] ?? '[]';
            $orders = json_decode($ordersJson, true);
            
            if (!is_array($orders)) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            foreach ($orders as $order) {
                if (!isset($order['id']) || !isset($order['sort_order'])) {
                    continue;
                }
                
                $this->themeModel->updateBanner($order['id'], [
                    'sort_order' => intval($order['sort_order'])
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã sắp xếp lại banner'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function getSettingType($key) {
        $colorKeys = ['primary_color', 'secondary_color', 'accent_color', 'background_color', 'text_color', 'sidebar_bg', 'header_bg'];
        $booleanKeys = ['enable_banner', 'animation_enabled'];
        
        if (in_array($key, $colorKeys)) {
            return 'color';
        } elseif (in_array($key, $booleanKeys)) {
            return 'boolean';
        }
        
        return 'text';
    }

    private function uploadWebsiteBackground() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            if (!isset($_FILES['website_background']) || $_FILES['website_background']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Không có file được upload hoặc có lỗi xảy ra');
            }
            
            $file = $_FILES['website_background'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Định dạng file không được hỗ trợ');
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Ảnh quá lớn! Dung lượng tối đa 5MB');
            }
            
            $uploadDir = BASE_PATH . '/public/uploads/backgrounds/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'website_bg_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Không thể lưu file');
            }
            
            $relativePath = 'public/uploads/backgrounds/' . $filename;
            
            // Save to database
            $this->themeModel->setThemeSetting('website_background', $relativePath, 'image');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã tải lên ảnh nền website',
                'path' => $relativePath
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function removeWebsiteBackground() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $path = $input['path'] ?? '';
            
            // Delete file if exists
            if ($path) {
                $fullPath = BASE_PATH . '/' . $path;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            // Clear setting in database
            $this->themeModel->setThemeSetting('website_background', '', 'image');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã xóa ảnh nền website'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function uploadHeroBackground() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            if (!isset($_FILES['hero_background']) || $_FILES['hero_background']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Không có file được upload hoặc có lỗi xảy ra');
            }
            
            $file = $_FILES['hero_background'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Định dạng file không được hỗ trợ');
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Ảnh quá lớn! Dung lượng tối đa 5MB');
            }
            
            $uploadDir = BASE_PATH . '/public/uploads/hero/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'hero_bg_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception('Không thể lưu file');
            }
            
            $relativePath = 'public/uploads/hero/' . $filename;
            
            // Save to database
            $this->themeModel->setThemeSetting('hero_background_image', $relativePath, 'image');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã tải lên ảnh nền Hero',
                'path' => $relativePath
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function removeHeroBackground() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $path = $input['path'] ?? '';
            
            // Delete file if exists
            if ($path) {
                $fullPath = BASE_PATH . '/' . $path;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            // Clear setting in database
            $this->themeModel->setThemeSetting('hero_background_image', '', 'image');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã xóa ảnh nền Hero'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // =====================================================
    // PRESET MANAGEMENT METHODS
    // =====================================================

    private function getPresets() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $presets = $this->themeModel->getAllPresets();
            echo json_encode(['success' => true, 'presets' => $presets], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function addPreset() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $data = [
                'name' => $_POST['name'] ?? 'Theme mới',
                'icon' => $_POST['icon'] ?? 'palette',
                'primary_color' => $_POST['primary_color'] ?? '#4f46e5',
                'secondary_color' => $_POST['secondary_color'] ?? '#7c3aed',
                'accent_color' => $_POST['accent_color'] ?? '#10b981',
                'background_color' => $_POST['background_color'] ?? '#ffffff',
                'text_color' => $_POST['text_color'] ?? '#1f2937',
                'sidebar_bg' => $_POST['sidebar_bg'] ?? '#ffffff',
                'header_bg' => $_POST['header_bg'] ?? '#ffffff',
                'is_system' => 0
            ];
            
            $id = $this->themeModel->createPreset($data);
            
            if ($id) {
                $preset = $this->themeModel->getPresetById($id);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã tạo theme preset',
                    'preset' => $preset
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể tạo preset');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function updatePresetAction() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID preset không hợp lệ');
            }
            
            $data = [
                'name' => $_POST['name'] ?? 'Theme',
                'icon' => $_POST['icon'] ?? 'palette',
                'primary_color' => $_POST['primary_color'] ?? '#4f46e5',
                'secondary_color' => $_POST['secondary_color'] ?? '#7c3aed',
                'accent_color' => $_POST['accent_color'] ?? '#10b981',
                'background_color' => $_POST['background_color'] ?? '#ffffff',
                'text_color' => $_POST['text_color'] ?? '#1f2937',
                'sidebar_bg' => $_POST['sidebar_bg'] ?? '#ffffff',
                'header_bg' => $_POST['header_bg'] ?? '#ffffff',
            ];
            
            $result = $this->themeModel->updatePreset($id, $data);
            
            if ($result) {
                $preset = $this->themeModel->getPresetById($id);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã cập nhật theme preset',
                    'preset' => $preset
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể cập nhật preset (có thể là system preset)');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function deletePresetAction() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID preset không hợp lệ');
            }
            
            $result = $this->themeModel->deletePreset($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã xóa theme preset'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể xóa preset (có thể là system preset)');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function activatePresetAction() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                throw new Exception('ID preset không hợp lệ');
            }
            
            $result = $this->themeModel->activatePreset($id);
            
            if ($result) {
                $preset = $this->themeModel->getPresetById($id);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã áp dụng theme: ' . $preset['name'],
                    'preset' => $preset
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể áp dụng preset');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function index() {
        // Insert system presets if not exist
        $this->themeModel->insertSystemPresets();
        require_once __DIR__ . '/../../View/admin/ThemeCustomizationView.php';
    }
}

// Auto-execute if called directly
if (basename($_SERVER['PHP_SELF']) === 'ThemeController.php' || 
    (isset($_GET['action']) || isset($_POST['action']))) {
    $controller = new ThemeController();
    $controller->handleRequest();
}

