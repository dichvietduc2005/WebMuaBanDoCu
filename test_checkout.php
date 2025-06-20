<?php
// Test the checkout page loading without browser
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION = [];
ob_start();

try {
    include('app/View/checkout/index.php');
    $output = ob_get_contents();
    echo "SUCCESS: Checkout page loaded without errors\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
} finally {
    ob_end_clean();
}
?>
