<?php
require_once(__DIR__ . '/../../../config/config.php'); // For $pdo, session_start(), etc.
require_once(__DIR__ . '/../../Controllers/product/ProductController.php'); // For product-related functions
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../user/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($id && $action) {
    if ($action === 'approve') {
        updateProductStatus($pdo, $id, 'active');
    } elseif ($action === 'reject') {
        updateProductStatus($pdo, $id, 'reject');
    } elseif ($action === 'delete') {
        deleteProduct($pdo, $id);
    }
}
header('Location: admin.php');
exit;