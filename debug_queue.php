<?php
require_once __DIR__ . '/config/config.php';

echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";

$stmt = $pdo->query("SELECT NOW() as db_time");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Current DB Time: " . $row['db_time'] . "\n";

echo "\n--- Templates ---\n";
$stmt = $pdo->query("SELECT * FROM notification_templates");
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($templates)) {
    echo "No templates found. Inserting default...\n";
    $sql = "INSERT INTO notification_templates (code, title, message_template, type, is_active) VALUES 
    ('cart_abandoned', 'Bạn bỏ quên sản phẩm trong giỏ hàng!', 'Chào {username}, bạn còn {count} sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!', 'system', 1)";
    $pdo->exec($sql);
    echo "Inserted default template.\n";
    
    // Fetch again
    $stmt = $pdo->query("SELECT * FROM notification_templates");
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($templates as $t) {
    echo "ID: {$t['id']}, Code: {$t['code']}, Active: {$t['is_active']}\n";
}

echo "\n--- Queue (Pending) ---\n";
$stmt = $pdo->query("SELECT * FROM notification_queue WHERE status = 'pending'");
$queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Pending Count: " . count($queue) . "\n";
foreach ($queue as $q) {
    echo "ID: {$q['id']}, User: {$q['user_id']}, Template: {$q['template_code']}, Scheduled: {$q['scheduled_at']}\n";
}
