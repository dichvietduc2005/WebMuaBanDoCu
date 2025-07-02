<?php
require_once('../../../config/config.php');

try{
    if (isset($_POST['action']) && $_POST['action'] === 'block' && isset($_POST['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET status= 'inactive' WHERE id=?");
        $stmt->execute([$_POST['user_id']]);
        
    } else if (isset($_POST['action']) && $_POST['action'] === 'unlock' && isset($_POST['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET status= 'active' WHERE id=?");
        $stmt->execute([$_POST['user_id']]);
    }

    echo "success";
}catch(PDOException $e){
    echo "Faild: " . $e->getMessage();
}

?>