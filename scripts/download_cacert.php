<?php
/**
 * Script to download cacert.pem for SSL verification fixes on Windows
 */

$url = 'https://curl.se/ca/cacert.pem';
$dest = __DIR__ . '/../config/cacert.pem';

echo "Downloading CA certificate from $url...\n";

$content = file_get_contents($url);

if ($content === false) {
    echo "Error: Could not download CA certificate.\n";
    exit(1);
}

if (file_put_contents($dest, $content)) {
    echo "Success: CA certificate saved to " . realpath($dest) . "\n";
    echo "\nTO FIX SSL ERROR, PLEASE UPDATE YOUR php.ini:\n";
    echo "1. Open " . php_ini_loaded_file() . "\n";
    echo "2. Find [curl] section and set:\n";
    echo "   curl.cainfo = \"" . realpath($dest) . "\"\n";
    echo "3. Find [openssl] section and set:\n";
    echo "   openssl.cafile = \"" . realpath($dest) . "\"\n";
    echo "4. RESTART WAMP SERVICES.\n";
} else {
    echo "Error: Could not save CA certificate to $dest\n";
    exit(1);
}
