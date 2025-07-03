<?php
// Test file to debug remember me functionality
require_once('../../../config/config.php');

echo "<h2>Remember Me Debug Information</h2>";

echo "<h3>Session Information:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h3>Cookie Information:</h3>";
echo "<pre>";
var_dump($_COOKIE);
echo "</pre>";

$auth = new Auth($pdo);
echo "<h3>Auth->isLoggedIn() Result:</h3>";
echo $auth->isLoggedIn() ? "TRUE" : "FALSE";

if ($auth->isLoggedIn()) {
    echo "<h3>Current User:</h3>";
    echo "<pre>";
    var_dump($auth->getCurrentUser());
    echo "</pre>";
}

// Check remember tokens in database
echo "<h3>Remember Tokens in Database:</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE expires_at > NOW()");
    $stmt->execute();
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    var_dump($tokens);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "<hr>";
echo "<a href='login.php'>Go to Login</a> | ";
echo "<a href='logout.php'>Logout</a> | ";
echo "<a href='../../../public/TrangChu.php'>Home</a>";
?>
