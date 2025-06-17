<?php
    function getPendingProducts($pdo) {
        $stmt = $pdo->query("SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function updateProductStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
    }

    function deleteProduct($pdo, $product_id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id=?" );
        return $stmt->execute(($product_id));
    }
?>