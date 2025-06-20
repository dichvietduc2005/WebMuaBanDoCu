<?php
/**
 * VNPay Debug Logger - Simple logging function for VNPay debugging
 */

function log_vnpay_debug_data($event_name, $data, $hash_data = null, $calculated_hash = null, $received_hash = null) {
    // Simple debug logging - only log if in development mode
    if (defined('VNPAY_DEBUG') && VNPAY_DEBUG === true) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event_name,
            'data' => $data
        ];
        
        if ($hash_data !== null) {
            $log_entry['hash_data'] = $hash_data;
        }
        if ($calculated_hash !== null) {
            $log_entry['calculated_hash'] = $calculated_hash;
        }
        if ($received_hash !== null) {
            $log_entry['received_hash'] = $received_hash;
        }
        
        error_log("VNPAY_DEBUG: " . json_encode($log_entry));
    }
}

function get_vnpay_config_for_logging() {
    global $vnp_TmnCode, $vnp_HashSecret;
    return [
        'vnp_TmnCode' => $vnp_TmnCode ?? 'NOT_SET',
        'vnp_HashSecret_partial' => isset($vnp_HashSecret) ? substr($vnp_HashSecret, 0, 5) . '...' . substr($vnp_HashSecret, -5) : 'NOT_SET'
    ];
}
?>
