<?php
function getPendingProducts($pdo) {
    $stmt = $pdo->query("
        SELECT p.*, u.username, pi.image_path
        FROM products p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateProductStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
    }

    function deleteProduct($pdo, $product_id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?" );
        return $stmt->execute([$product_id]);
    }

    function getAllProducts($pdo) {
        $stmt = $pdo->query("
            SELECT p.*, u.username, pi.image_path
            FROM products p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.status = 'active'
            ORDER BY p.featured DESC, p.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>