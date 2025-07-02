<?php
require_once('../../../config/config.php');

echo "<h2>Remember Me Token Debug</h2>";

// Lấy token từ cookie
$cookie_token = $_COOKIE['remember_token'] ?? null;
echo "<h3>Cookie Token:</h3>";
echo "<p>Raw: " . ($cookie_token ?? 'NULL') . "</p>";

if ($cookie_token) {
    $hashed_cookie_token = hash('sha256', $cookie_token);
    echo "<p>Hashed: " . $hashed_cookie_token . "</p>";
    
    // Kiểm tra token trong database
    echo "<h3>Database Check:</h3>";
    $stmt = $pdo->prepare("
        SELECT rt.*, u.username, u.email 
        FROM remember_tokens rt 
        JOIN users u ON rt.user_id = u.id 
        WHERE rt.token = ? AND rt.expires_at > NOW()
    ");
    $stmt->execute([$hashed_cookie_token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($token_data) {
        echo "<p style='color: green;'>✅ Token FOUND in database!</p>";
        echo "<pre>";
        print_r($token_data);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Token NOT FOUND in database!</p>";
        
        // Kiểm tra tất cả token để debug
        echo "<h4>All tokens in database:</h4>";
        $stmt = $pdo->prepare("SELECT * FROM remember_tokens ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $all_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($all_tokens);
        echo "</pre>";
    }
}

// Test Auth class
echo "<h3>Auth Class Test:</h3>";
$auth = new Auth($pdo);
echo "<p>isLoggedIn(): " . ($auth->isLoggedIn() ? 'TRUE' : 'FALSE') . "</p>";

if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    echo "<p>Current User: " . $user['username'] . " (" . $user['email'] . ")</p>";
}

// Session info
echo "<h3>Session Info:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>

<p><a href="logout.php">Logout</a></p>
<p><a href="test_remember.php">Back to Test</a></p>
