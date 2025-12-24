<?php
require_once '../../../config/config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (!$slug) {
    header('Location: ../../../public/index.php');
    exit;
}

// Get category info
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: ../../../public/index.php');
    exit;
}

// Redirect to products page with category filter
header('Location: ' . BASE_URL . 'public/index.php?page=products&category=' . $category['id']);
exit;
?>
