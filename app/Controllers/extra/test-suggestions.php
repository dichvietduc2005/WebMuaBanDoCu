<?php
// Test file để kiểm tra API
header('Content-Type: application/json');

$keyword = $_GET['keyword'] ?? '';

if (strlen($keyword) < 2) {
    echo json_encode([
        'success' => true,
        'data' => [
            'suggestions' => []
        ]
    ]);
    exit;
}

$suggestions = [
    'Điện thoại ' . $keyword,
    'Laptop ' . $keyword,
    'Máy tính ' . $keyword,
    'Tai nghe ' . $keyword,
    'Đồng hồ ' . $keyword
];

echo json_encode([
    'success' => true,
    'data' => [
        'suggestions' => $suggestions
    ]
]);
?>
