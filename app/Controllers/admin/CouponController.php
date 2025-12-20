<?php
require_once __DIR__ . '/../../Models/admin/CouponModel.php';

// Handle Actions
if (isset($_GET['action'])) {
    // Suppress warnings to prevent JSON errors
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    
    // Check admin permission
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $action = $_GET['action'];

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'discount_type' => $_POST['discount_type'] ?? 'percent',
            'discount_value' => $_POST['discount_value'] ?? 0,
            'min_order_value' => $_POST['min_order_value'] ?? 0,
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'usage_limit' => $_POST['usage_limit'] ?? 0,
            'status' => isset($_POST['status']) ? 1 : 0
        ];

        if (createCoupon($pdo, $data)) {
            echo json_encode(['success' => true, 'message' => 'Tạo mã giảm giá thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Mã giảm giá có thể đã tồn tại.']);
        }
        exit;
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        if (deleteCoupon($pdo, $_POST['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa.']);
        }
        exit;
    }

    if ($action === 'toggle_status' && isset($_POST['id'])) {
        if (toggleCouponStatus($pdo, $_POST['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật trạng thái.']);
        }
        exit;
    }
}
