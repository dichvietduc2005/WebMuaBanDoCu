<?php
/**
 * Header Component - Unified Loader
 * Tự động chọn giữa HeaderFull (Desktop) và HeaderSimple (Mobile)
 */

require_once __DIR__ . '/HeaderFull.php';
require_once __DIR__ . '/HeaderSimple.php';

/**
 * Render Header
 * 
 * @param PDO $pdo Connection database
 * @param array|null $categories Danh sách danh mục (nếu null sẽ tự load)
 * @param int|null $cart_count Số lượng giỏ hàng (nếu null sẽ tự load)
 * @param int|null $unread_notifications Số lượng thông báo chưa đọc (nếu null sẽ tự load)
 */
function renderHeader($pdo, $categories = null, $cart_count = null, $unread_notifications = null)
{
    // Đảm bảo có kết nối PDO
    if (!$pdo) {
        global $pdo;
    }

    // Tự động load danh mục nếu chưa có
    if ($categories === null || empty($categories)) {
        try {
            $stmt = $pdo->query("SELECT id, name, slug, icon FROM categories WHERE status = 'active' ORDER BY name ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $categories = [];
        }
    }

    // Tự động load giả hàng nếu chưa có và user đã đăng nhập
    if ($cart_count === null) {
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $cart_count = (int)$stmt->fetchColumn();
            } catch (Exception $e) {
                $cart_count = 0;
            }
        } else {
            $cart_count = 0;
        }
    }

    // Tự động load thông báo nếu chưa có và user đã đăng nhập
    if ($unread_notifications === null) {
        if (isset($_SESSION['user_id'])) {
            try {
                // Kiểm tra table notifications có tồn tại không trước khi query
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$_SESSION['user_id']]);
                $unread_notifications = (int)$stmt->fetchColumn();
            } catch (Exception $e) {
                $unread_notifications = 0;
            }
        } else {
            $unread_notifications = 0;
        }
    }

    // Render bản Desktop (CSS trong HeaderFull sẽ ẩn/hiện theo breakpoint)
    renderHeaderFull($pdo, $categories, $cart_count, $unread_notifications);
    
    // Render bản Mobile/Simple
    renderHeaderSimple($pdo, $categories, $cart_count, $unread_notifications);
}
