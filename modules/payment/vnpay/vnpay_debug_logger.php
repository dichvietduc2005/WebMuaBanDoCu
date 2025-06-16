<?php
if (!defined('VNPAY_DEBUG_LOG_FILE')) {
    define('VNPAY_DEBUG_LOG_FILE', __DIR__ . '/../../../logs/payment_debug.log');
}

if (!function_exists('log_vnpay_debug_data')) {
    function log_vnpay_debug_data($stage, $data_array = [], $hash_string_to_log = null, $calculated_hash = null, $received_hash = null) {
        $log_message = "---------------------------------------------------------------------\n";
        $log_message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $log_message .= "Stage: " . $stage . "\n";

        if (!empty($data_array)) {
            $log_message .= "Data:\n" . print_r($data_array, true) . "\n";
        }

        if ($hash_string_to_log !== null) {
            $log_message .= "String to Hash: [" . $hash_string_to_log . "]\n";
        }

        if ($calculated_hash !== null) {
            $log_message .= "Calculated Hash: [" . $calculated_hash . "]\n";
        }

        if ($received_hash !== null) {
            $log_message .= "Received Hash: [" . $received_hash . "]\n";
        }
        
        $log_message .= "---------------------------------------------------------------------\n\n";

        // Ensure logs directory exists
        $log_dir = dirname(VNPAY_DEBUG_LOG_FILE);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        file_put_contents(VNPAY_DEBUG_LOG_FILE, $log_message, FILE_APPEND);
    }
}

if (!function_exists('get_vnpay_config_for_logging')) {
    function get_vnpay_config_for_logging() {
        global $vnp_TmnCode, $vnp_HashSecret, $vnp_Url, $vnp_Returnurl; // Assuming these are in global scope from config.php
        return [
            'vnp_TmnCode_from_config' => $vnp_TmnCode ?? 'NOT_SET',
            'vnp_HashSecret_from_config_partial' => isset($vnp_HashSecret) ? substr($vnp_HashSecret, 0, 5) . '...' . substr($vnp_HashSecret, -5) : 'NOT_SET',
            'vnp_Url_from_config' => $vnp_Url ?? 'NOT_SET',
            'vnp_ReturnUrl_from_config' => $vnp_Returnurl ?? 'NOT_SET'
        ];
    }
}
?>
