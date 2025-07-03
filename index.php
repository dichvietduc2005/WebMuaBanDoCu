<?php
/**
 * Root index.php - Redirect to public folder
 * This file redirects all requests to the public folder
 * for better security and proper application structure
 */

// Redirect to public folder
header('Location: public/');
exit();
?>
