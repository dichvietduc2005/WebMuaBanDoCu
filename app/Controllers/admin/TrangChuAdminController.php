<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: /WebMuaBanDoCu/public/index.php");
    exit;
}
?>

<?php require_once('../../View/admin/TrangChuAdminView.php'); ?>
