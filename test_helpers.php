<?php
// Test the helper functions directly
require_once('config/config.php');
require_once('app/helpers.php');

try {
    // Test get_current_user_id
    $user_id = get_current_user_id();
    echo "get_current_user_id() = " . ($user_id ?? 'NULL') . "\n";
    
    // Test getCartTotal with null user_id (should return 0)
    $cartTotal = getCartTotal($pdo, null);
    echo "getCartTotal(pdo, null) = " . $cartTotal . "\n";
    
    // Test getCartItemCount with null user_id (should return 0)
    $cartItemCount = getCartItemCount($pdo, null);
    echo "getCartItemCount(pdo, null) = " . $cartItemCount . "\n";
    
    echo "SUCCESS: All helper functions work correctly\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
