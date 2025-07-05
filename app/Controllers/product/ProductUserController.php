<?php
function getUserProducts($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_path
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// function updateProduct($pdo, $user_id, $product_id, $data) {
//     $stmt = $pdo->prepare("UPDATE products SET category_id=?, title=?, description=?, price=?, condition_status=?, location=? WHERE id=? AND user_id=?");
//     return $stmt->execute([
//         $data['category_id'],
//         $data['title'],
//         $data['description'],
//         $data['price'],
//         $data['condition_status'],
//         $data['location'],
//         $product_id,
//         $user_id
//     ]);
// }

function deleteUserProduct($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    return $stmt->execute([$product_id, $user_id]);
}
?>