<?php
function getUserProducts($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProduct($pdo, $user_id, $product_id, $data) {
    $stmt = $pdo->prepare("UPDATE products SET category_id=?, title=?, description=?, price=?, condition_status=?, location=? WHERE id=? AND user_id=?");
    return $stmt->execute([
        $data['category_id'],
        $data['title'],
        $data['description'],
        $data['price'],
        $data['condition_status'],
        $data['location'],
        $product_id,
        $user_id
    ]);
}

function deleteUserProduct($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    return $stmt->execute([$product_id, $user_id]);
}
?>