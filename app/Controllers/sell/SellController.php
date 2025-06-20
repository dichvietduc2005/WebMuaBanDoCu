<?php


function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function generateUniqueSlug($pdo, $title) {
    $baseSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) {
            break;
        }
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
    return $slug;
}

// Thêm sản phẩm mới
function addProduct($pdo, $user_id, $data, $slug) {
    $stmt = $pdo->prepare("INSERT INTO products (user_id, category_id, title, slug, description, price, condition_status, status, location, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
    return $stmt->execute([
        $user_id,
        $data['category_id'],
        $data['title'],
        $slug,
        $data['description'],
        $data['price'],
        $data['condition_status'],
        $data['location']
    ]);
}
?>
