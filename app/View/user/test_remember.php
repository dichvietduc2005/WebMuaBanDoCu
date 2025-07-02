<?php
require_once('../../../config/config.php');

// Simple test for remember me functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    $auth = new Auth($pdo);
    $result = $auth->login($email, $password, $remember_me);
    
    echo "<h3>Login Result:</h3>";
    echo "<pre>";
    var_dump($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h3>Cookies after login:</h3>";
        echo "<pre>";
        var_dump($_COOKIE);
        echo "</pre>";
        
        echo "<h3>Session after login:</h3>";
        echo "<pre>";
        var_dump($_SESSION);
        echo "</pre>";
    }
    
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remember Me Test</title>
</head>
<body>
    <h2>Test Remember Me Login</h2>
    
    <form method="POST">
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="user1@example.com" required>
        </p>
        
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" value="password" required>
        </p>
        
        <p>
            <label>
                <input type="checkbox" name="remember_me" checked> Remember Me
            </label>
        </p>
        
        <p>
            <button type="submit">Login</button>
        </p>
    </form>
    
    <hr>
    
    <h3>Current Status:</h3>
    <?php
    $auth = new Auth($pdo);
    echo "<p>Is Logged In: " . ($auth->isLoggedIn() ? 'YES' : 'NO') . "</p>";
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo "<p>Current User: " . htmlspecialchars($user['full_name'] ?? 'Unknown') . "</p>";
    }
    
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Remember Token Cookie: " . (isset($_COOKIE['remember_token']) ? 'EXISTS' : 'NOT SET') . "</p>";
    ?>
    
    <p><a href="logout.php">Logout</a></p>
    <p><a href="debug_remember.php">Debug Info</a></p>
</body>
</html>
