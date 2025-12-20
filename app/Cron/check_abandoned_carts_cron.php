<?php
// app/Cron/check_abandoned_carts_cron.php

// Đảm bảo chỉ chạy từ CLI hoặc được bảo vệ
if (php_sapi_name() !== 'cli' && !isset($_GET['secret_key'])) {
    die('Access denied');
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Services/AbandonedCartService.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $service = new AbandonedCartService($pdo);
    $count = $service->checkAbandonedCarts();

    echo "Successfully queued $count abandoned cart notifications.\n";
    
    // Log result
    error_log("[" . date('Y-m-d H:i:s') . "] Abandoned Cart Cron: Queued $count notifications.");

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("[" . date('Y-m-d H:i:s') . "] Abandoned Cart Cron Error: " . $e->getMessage());
}
