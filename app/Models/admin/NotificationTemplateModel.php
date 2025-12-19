<?php
// app/Models/admin/NotificationTemplateModel.php

function getAllTemplates($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM notification_templates ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTemplateByCode($pdo, $code) {
    $stmt = $pdo->prepare("SELECT * FROM notification_templates WHERE code = ?");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateTemplate($pdo, $id, $data) {
    $allowedFields = ['title', 'message_template', 'is_active'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($updates)) {
        return false;
    }

    $params[] = $id;
    $sql = "UPDATE notification_templates SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function toggleTemplate($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE notification_templates SET is_active = NOT is_active WHERE id = ?");
    return $stmt->execute([$id]);
}

function createTemplate($pdo, $data) {
    $sql = "INSERT INTO notification_templates (code, title, message_template, type, is_active) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['code'],
        $data['title'],
        $data['message_template'],
        $data['type'] ?? 'manual',
        $data['is_active'] ?? 1
    ]);
}
