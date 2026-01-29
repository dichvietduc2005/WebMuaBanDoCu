<?php
// Enhanced Get Products with Pagination & Filtering
function getUserProducts($pdo, $user_id, $status = null, $sort = 'newest', $page = 1, $limit = 6)
{
    $offset = ($page - 1) * $limit;

    // Base SQL
    $sql = "SELECT p.*, pi.image_path 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.user_id = ?";
    $params = [$user_id];

    // Filter by Status
    if ($status && $status !== 'all') {
        $sql .= " AND p.status = ?";
        $params[] = $status;
    }

    // Sorting
    switch ($sort) {
        case 'oldest':
            $sql .= " ORDER BY p.created_at ASC";
            break;
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        default:
            $sql .= " ORDER BY p.created_at DESC"; // newest
    }

    // Pagination
    $sql .= " LIMIT " . (int) $offset . ", " . (int) $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUserProducts($pdo, $user_id, $status = null)
{
    $sql = "SELECT COUNT(*) FROM products WHERE user_id = ?";
    $params = [$user_id];

    if ($status && $status !== 'all') {
        $sql .= " AND p.status = ?"; // Wait, count usually doesn't need alias if simple, but let's be safe or strict. 
        // Actually earlier query aliased p in WHERE? NO, p.user_id was in base.
        // Simple count usually: SELECT COUNT(*) FROM products WHERE user_id = ? ...
        // Let's use clean SQL without alias for count unless we join.
        $sql = "SELECT COUNT(*) FROM products WHERE user_id = ?";
        // Correction: parameters must match.
    }

    // Re-writing count logic to be cleaner
    $sql = "SELECT COUNT(*) FROM products WHERE user_id = ?";
    $params = [$user_id];

    if ($status && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getProductById($pdo, $user_id, $product_id)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProduct($pdo, $user_id, $product_id, $data)
{
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

function getProductDetails($pdo, $user_id, $product_id)
{
    // 1. Get Product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product)
        return null;

    // 2. Get Images
    $stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
    $stmtImg->execute([$product_id]);
    $product['images'] = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

    return $product;
}

function deleteUserProduct($pdo, $user_id, $product_id)
{
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    return $stmt->execute([$product_id, $user_id]);
}
?>