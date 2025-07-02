<?php
require_once('../../../config/config.php');

// Test auto-login from remember token
echo "<h2>Auto-Login Test</h2>";

// Clear current session but keep cookies
if (isset($_GET['clear_session'])) {
    session_destroy();
    session_start();
    echo "<p><strong>Session cleared!</strong></p>";
}

echo "<h3>Current Status:</h3>";

$auth = new Auth($pdo);

// Check if we have remember token
if (isset($_COOKIE['remember_token'])) {
    echo "<p>✅ Remember token exists: " . substr($_COOKIE['remember_token'], 0, 20) . "...</p>";
    
    // Try to auto-login manually
    $auth_result = $auth->isLoggedIn();
    echo "<p>isLoggedIn() result: " . ($auth_result ? 'SUCCESS' : 'FAILED') . "</p>";
} else {
    echo "<p>❌ No remember token found</p>";
}

echo "<p>Is Logged In: " . ($auth->isLoggedIn() ? 'YES' : 'NO') . "</p>";

if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo "<p>Current User: " . htmlspecialchars($user['full_name'] ?? 'Unknown') . "</p>";
} else {
    echo "<p>Not logged in</p>";
}

echo "<hr>";
echo "<p><a href='?clear_session=1'>Clear Session & Test Auto-Login</a></p>";
echo "<p><a href='test_remember.php'>Back to Remember Test</a></p>";
echo "<p><a href='logout.php'>Full Logout</a></p>";
?>
