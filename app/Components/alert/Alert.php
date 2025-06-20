<?php
function alert($type = 'success', $message = '') {
    $icons = [
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'exclamation-circle',
        'info' => 'info-circle'
    ];
    
    $icon = $icons[$type] ?? 'info-circle';
    ?>
    <div class="alert alert-<?= $type ?> d-flex align-items-center" role="alert">
        <i class="bi bi-<?= $icon ?> me-2"></i>
        <div><?= htmlspecialchars($message) ?></div>
    </div>
    <?php
}