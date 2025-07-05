<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/config.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'count' => 0]);
        exit;
    }

    // Get cart count (only active items that are not sold and not hidden)
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity) as total_quantity 
        FROM carts c 
        JOIN cart_items ci ON c.id = ci.cart_id 
        WHERE c.user_id = ? 
        AND (ci.status IS NULL OR ci.status != 'sold')
        AND (ci.is_hidden IS NULL OR ci.is_hidden = 0)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cart_count = $result['total_quantity'] ?? 0;

    echo json_encode([
        'success' => true,
        'count' => (int) $cart_count
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'count' => 0
    ]);
}
?>
