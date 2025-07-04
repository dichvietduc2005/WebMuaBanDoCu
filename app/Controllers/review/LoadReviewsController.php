<?php
require_once '../../../config/config.php';

$userId = $_SESSION['user_id'] ?? false;

if (!$userId) {
    header('Location: /WebMuaBanDoCu/app/View/user/login.php');
    exit;
}

try {

    $product_id = $_POST['product_id'];

    $stmt = $pdo->prepare("SELECT * FROM review_products WHERE product_id = ? ORDER BY sent_at ASC");
    $stmt->execute([$product_id]);

    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($reviews);

} catch (PDOException $e) {
    echo "" . $e->getMessage();
}

?>