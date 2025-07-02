<?php
require_once '../../../config/config.php'; //

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$sql = 'UPDATE users SET 
    username = ?, 
    full_name = ?, 
    email = ?, 
    phone = ?, 
    address = ? 
    WHERE id = ?';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['user_name'],
        $data['user_full_name'],
        $data['user_email'],
        $data['user_phone'],
        $data['user_address'],
        $data['user_id']
    ]);

    $_SESSION['user_name'] = $data['user_name'];
    $_SESSION['user_full_name'] = $data['user_full_name'];
    $_SESSION['user_email'] = $data['user_email'];
    $_SESSION['user_phone'] = $data['user_phone'];
    $_SESSION['user_address'] = $data['user_address'];

    echo "success";
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>