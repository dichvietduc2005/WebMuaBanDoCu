<?php
require_once '../config/config.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$search) {
    header('Location: TrangChu.php');
    exit;
}

// Redirect to products page with search parameter
header('Location: products.php?search=' . urlencode($search));
exit;
?>
