<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();

/*
 * Database Configuration
 */
$db_host = 'localhost';
$db_name = 'muabandocu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;port=3306;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
  
// VNPAY Configuration - Sử dụng thông tin mới nhất và chính xác
$vnp_TmnCode = "X4DCQ1UX"; // Website ID in VNPAY System
$vnp_HashSecret = "MPI8C42IYO31NDYYLS2HN2KYD0XBYIFH"; // Secret key
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
// Đảm bảo URL này khớp với vị trí file return.php của bạn
$vnp_Returnurl = "http://localhost/WebMuaBanDoCu/app/Controllers/payment/return.php"; 
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
//Config input format
//Expire
$startTime = date("YmdHis");
$expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));


define('BASE_URL', '/WebMuaBanDoCu/');
define('ASSETS_URL', BASE_URL . 'public/assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');
define('CONTROLLERS_URL', BASE_URL . 'app/controllers/');
define('MODELS_URL', BASE_URL . 'app/models/');
define('VIEWS_URL', BASE_URL . 'app/views/');
