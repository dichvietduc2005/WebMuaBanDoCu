<?php
// Khởi động buffer để đảm bảo chỉ trả JSON thuần
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// Load cấu hình chung (kết nối DB + session)
require_once __DIR__ . '/../../../config/config.php';
require_once APP_PATH . '/Controllers/auth_helper.php';

// Xóa mọi output không mong muốn từ config/auth_helper (nếu có)
$buffer = ob_get_contents();
ob_clean();

// Set header JSON
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

try {
    // Check admin permission
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        exit;
    }

    // Đảm bảo $pdo đã tồn tại
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Không thể kết nối cơ sở dữ liệu');
    }

    // Hỗ trợ cả GET lẫn POST
    $source = array_merge($_GET, $_POST);
    $id = $source['id'] ?? null;
    $action = $source['action'] ?? null;
    $reason = trim($source['reason'] ?? '');

    if (!$action) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu tham số cần thiết'
        ]);
        exit;
    }

    // Bulk actions được xử lý riêng
    if ($action === 'bulk') {
        $ids = $_POST['ids'] ?? [];
        $bulkAction = $_POST['bulk_action'] ?? null;

        if (empty($ids) || !is_array($ids) || !$bulkAction) {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu danh sách sản phẩm hoặc hành động'
            ]);
            exit;
        }

        $pdo->beginTransaction();

        $successCount = 0;
        $failCount = 0;

        foreach ($ids as $rawId) {
            $pid = (int)$rawId;
            if ($pid <= 0) {
                $failCount++;
                continue;
            }

            try {
                $stmt = $pdo->prepare('SELECT user_id, title, featured, status FROM products WHERE id = ?');
                $stmt->execute([$pid]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$product) {
                    $failCount++;
                    continue;
                }

                $user_id = (int)$product['user_id'];
                $title = htmlspecialchars($product['title']);
                $oldStatus = $product['status'];
                $oldFeatured = (int)$product['featured'];

                $logDetails = [
                    'old_status' => $oldStatus,
                    'old_featured' => $oldFeatured,
                    'bulk' => true,
                ];

                switch ($bulkAction) {
                    case 'hide':
                        $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
                        $ok = $stmt->execute(['inactive', $pid]);
                        $logDetails['new_status'] = 'inactive';
                        break;
                    case 'delete':
                        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                        $ok = $stmt->execute([$pid]);
                        $logDetails['new_status'] = 'deleted';
                        break;
                    case 'feature':
                        $stmt = $pdo->prepare('UPDATE products SET featured = 1 WHERE id = ?');
                        $ok = $stmt->execute([$pid]);
                        $logDetails['new_featured'] = 1;
                        break;
                    case 'unfeature':
                        $stmt = $pdo->prepare('UPDATE products SET featured = 0 WHERE id = ?');
                        $ok = $stmt->execute([$pid]);
                        $logDetails['new_featured'] = 0;
                        break;
                    default:
                        $ok = false;
                }

                if (!$ok) {
                    $failCount++;
                    continue;
                }

                $successCount++;

                if (function_exists('log_admin_action') && isset($_SESSION['user_id'])) {
                    $adminId = (int)$_SESSION['user_id'];
                    log_admin_action($pdo, $adminId, 'product_bulk_' . $bulkAction, $pid, $logDetails);
                }

            } catch (Throwable $inner) {
                $failCount++;
                continue;
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Đã xử lý ' . $successCount . ' sản phẩm. Thất bại: ' . $failCount . '.',
            'action' => 'bulk',
            'bulk_action' => $bulkAction,
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu mã sản phẩm'
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // Get product info (bao gồm status để log)
    $stmt = $pdo->prepare('SELECT user_id, title, featured, status FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }

    $user_id = (int)$product['user_id'];
    $title = htmlspecialchars($product['title']);
    $oldStatus = $product['status'];
    $result = false;
    $newFeaturedStatus = null;
    $autoUnfeaturedId = null;

    // Thông tin log chi tiết
    $logDetails = [
        'old_status' => $oldStatus,
        'reason' => $reason !== '' ? $reason : null,
    ];

    // Handle actions
    switch ($action) {
        case 'toggle_featured':
            $currentFeatured = (int)$product['featured'];

            // Nếu sắp đặt sản phẩm thành nổi bật, đảm bảo tổng số nổi bật không vượt quá giới hạn
            if (!$currentFeatured) {
                $MAX_FEATURED = 12;
                // Đếm tổng số sản phẩm đang ở trạng thái nổi bật
                $countStmt = $pdo->query('SELECT COUNT(*) FROM products WHERE featured = 1');
                $featuredCount = (int) $countStmt->fetchColumn();

                // Nếu đã vượt giới hạn, tự động bỏ nổi bật sản phẩm lâu nhất
                if ($featuredCount >= $MAX_FEATURED) {
                    $oldestStmt = $pdo->query('SELECT id FROM products WHERE featured = 1 ORDER BY created_at ASC LIMIT 1');
                    $oldestId = $oldestStmt->fetchColumn();
                    if ($oldestId) {
                        $pdo->prepare('UPDATE products SET featured = 0 WHERE id = ?')->execute([$oldestId]);
                        $autoUnfeaturedId = (int) $oldestId;
                    }
                }
            }

            // Tiến hành toggle trạng thái nổi bật cho sản phẩm hiện tại
            $newFeatured = $currentFeatured ? 0 : 1;
            $stmt = $pdo->prepare('UPDATE products SET featured = ? WHERE id = ?');
            $result = $stmt->execute([$newFeatured, $id]);
            $newFeaturedStatus = $newFeatured;
            $logDetails['old_featured'] = $currentFeatured;
            $logDetails['new_featured'] = $newFeatured;
            if ($autoUnfeaturedId !== null) {
                $logDetails['auto_unfeatured_id'] = $autoUnfeaturedId;
            }
            break;

        case 'approve':
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['active', $id]);
            $logDetails['new_status'] = 'active';
            break;

        case 'reject':
            // Với enum hiện tại trong DB là 'reject'
            $stmt = $pdo->prepare('UPDATE products SET status = ? WHERE id = ?');
            $result = $stmt->execute(['reject', $id]);
            $logDetails['new_status'] = 'reject';
            break;

        case 'delete':
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $result = $stmt->execute([$id]);
            $logDetails['new_status'] = 'deleted';
            break;

        case 'update_field':
            $field = $source['field'] ?? null;
            $value = $source['value'] ?? null;
            
            if (!$field || $value === null) {
                throw new Exception('Thiếu tham số field hoặc value');
            }
            
            // Chỉ cho phép update status và condition_status
            if (!in_array($field, ['status', 'condition_status'])) {
                throw new Exception('Field không được phép cập nhật');
            }
            
            // Validate giá trị
            if ($field === 'status') {
                $allowedStatuses = ['pending', 'active', 'reject', 'sold'];
                if (!in_array($value, $allowedStatuses)) {
                    throw new Exception('Trạng thái không hợp lệ');
                }
            } elseif ($field === 'condition_status') {
                $allowedConditions = ['new', 'like_new', 'good', 'fair', 'poor'];
                if (!in_array($value, $allowedConditions)) {
                    throw new Exception('Tình trạng không hợp lệ');
                }
            }
            
            $stmt = $pdo->prepare("UPDATE products SET {$field} = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$value, $id]);
            $logDetails['field'] = $field;
            $logDetails['old_value'] = $product[$field] ?? null;
            $logDetails['new_value'] = $value;
            
            // Log admin action riêng cho update_field
            if (function_exists('log_admin_action') && isset($_SESSION['user_id'])) {
                log_admin_action(
                    $pdo,
                    $_SESSION['user_id'],
                    'update_' . $field,
                    $id,
                    [
                        'field' => $field,
                        'old_value' => $product[$field] ?? null,
                        'new_value' => $value,
                        'product_title' => $title
                    ]
                );
            }
            break;

        default:
            throw new Exception('Hành động không hợp lệ');
    }

    if (!$result) {
        throw new Exception('Không thể thực hiện thao tác');
    }

    // Create notification for user
    $notificationMessage = '';
    switch ($action) {
        case 'toggle_featured':
            $statusText = $newFeaturedStatus ? 'được đặt làm sản phẩm nổi bật' : 'được bỏ khỏi danh sách nổi bật';
            $notificationMessage = "Sản phẩm '$title' của bạn đã $statusText";
            break;
        case 'approve':
            $notificationMessage = "Sản phẩm '$title' của bạn đã được duyệt";
            break;
        case 'reject':
            $notificationMessage = "Sản phẩm '$title' của bạn đã bị từ chối";
            break;
        case 'delete':
            $notificationMessage = "Sản phẩm '$title' của bạn đã bị xóa";
            break;
        case 'update_field':
            $field = $source['field'] ?? '';
            $newValue = $source['value'] ?? '';
            $fieldLabel = $field === 'status' ? 'trạng thái' : 'tình trạng';
            $valueLabels = [
                'status' => [
                    'pending' => 'chờ duyệt',
                    'active' => 'đang bán',
                    'reject' => 'đã từ chối',
                    'sold' => 'đã bán'
                ],
                'condition_status' => [
                    'new' => 'mới',
                    'like_new' => 'như mới',
                    'good' => 'tốt',
                    'fair' => 'khá tốt',
                    'poor' => 'cũ'
                ]
            ];
            $valueLabel = $valueLabels[$field][$newValue] ?? $newValue;
            $notificationMessage = "Sản phẩm '$title' của bạn đã được cập nhật $fieldLabel thành '$valueLabel'";
            break;
    }

    if ($notificationMessage && $user_id > 0) {
        try {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$user_id, $notificationMessage]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to create notification: " . $e->getMessage());
        }
    }

    $pdo->commit();

    // Ghi log thao tác admin
    if (function_exists('log_admin_action') && isset($_SESSION['user_id']) && $action !== 'update_field') {
        $adminId = (int)$_SESSION['user_id'];
        $logAction = 'product_' . $action;
        log_admin_action($pdo, $adminId, $logAction, (int)$id, $logDetails);
    }

    // Return success response
    $response = [
        'success' => true,
        'message' => getActionMessage($action),
        'action' => $action,
        'productId' => $id
    ];

    if ($action === 'toggle_featured' && $newFeaturedStatus !== null) {
        $response['newFeaturedStatus'] = $newFeaturedStatus;
        $response['isFeatured'] = (bool)$newFeaturedStatus;
        if ($autoUnfeaturedId !== null) {
            $response['autoUnfeaturedId'] = $autoUnfeaturedId;
        }
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Xóa toàn bộ output khác, chỉ gửi JSON
    $output = ob_get_clean();
    // Chỉ echo nếu output là JSON hợp lệ
    if ($output && (strpos($output, '{') === 0 || strpos($output, '[') === 0)) {
        echo $output;
    } else {
        // Nếu có output không mong muốn, chỉ trả JSON error
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi xử lý request'
        ]);
    }
}

function getActionMessage($action) {
    switch ($action) {
        case 'approve':
            return 'Duyệt sản phẩm thành công';
        case 'reject':
            return 'Từ chối sản phẩm thành công';
        case 'toggle_featured':
            return 'Cập nhật trạng thái nổi bật thành công';
        case 'delete':
            return 'Xóa sản phẩm thành công';
        default:
            return 'Thao tác thành công';
    }
}
?>
