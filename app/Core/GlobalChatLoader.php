<?php
/**
 * Global Chat Loader - Load chat widget on all pages
 * This file should be included at the end of every page before closing </body>
 * 
 * Usage:
 * - Add this to your base layout or template footer
 * - Or include in config.php for auto-loading
 */

if (!function_exists('render_global_chat_widget')) {
    function render_global_chat_widget() {
        // Only show chat widget for logged-in users
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        // Get user ID
        $user_id = $_SESSION['user_id'];
        
        // Check if BASE_URL is defined
        if (!defined('BASE_URL')) {
            define('BASE_URL', '/WebMuaBanDoCu/');
        }
        
        // Load chat widget component
        $chat_widget_path = __DIR__ . '/../Components/ChatWidget.php';
        
        if (file_exists($chat_widget_path)) {
            // Pass user ID to chat widget via global scope
            global $pdo;
            
            // Output user ID for JavaScript
            echo '<script>';
            echo 'window.userId = ' . intval($user_id) . ';';
            echo '</script>';
            
            // Include chat widget
            include_once $chat_widget_path;
        }
    }
}

// Auto-load if this file is loaded
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    // Store a flag that chat is loaded
    $_SESSION['chat_widget_loaded'] = true;
}
?>

