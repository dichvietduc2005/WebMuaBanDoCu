<?php
/**
 * Header Component
 * Render chỉ header phần, không HTML root
 * 
 * Data từ LayoutManager:
 * - $baseUrl: BASE_URL
 * - $pdo: PDO connection
 * - $categories: Category list
 * - $cart_count: Cart items count
 * - $unread_notifications: Notification count
 */

// Gọi header rendering logic
require_once __DIR__ . '/../../../app/Components/header/Header.php';

// Pass data tới Header.php
$pdo = $pdo ?? null;
$categories = $categories ?? [];
$cart_count = $cart_count ?? 0;
$unread_notifications = $unread_notifications ?? 0;

// Render header
renderHeader($pdo, $categories, $cart_count, $unread_notifications);

