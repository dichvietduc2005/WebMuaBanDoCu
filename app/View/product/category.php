<?php
require_once '../../../config/config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (!$slug) {
    header('Location: ../../../public/TrangChu.php');
    exit;
}

// Get category info
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: ../../../public/TrangChu.php');
    exit;
}

// Redirect to products page with category filter
header('Location: products.php?category=' . $category['id']);
exit;
?>
