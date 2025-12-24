<?php
require_once __DIR__ . '/../../../config/config.php';

class ThemeModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getThemeSetting($key, $default = null) {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value, setting_type FROM admin_theme_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return $default;
            }

            $value = $result['setting_value'];
            
            switch ($result['setting_type']) {
                case 'boolean':
                    return (bool) $value;
                case 'json':
                    return json_decode($value, true);
                case 'color':
                case 'text':
                default:
                    return $value;
            }
        } catch (PDOException $e) {
            error_log("ThemeModel::getThemeSetting error: " . $e->getMessage());
            return $default;
        }
    }

    public function setThemeSetting($key, $value, $type = 'text', $description = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_theme_settings (setting_key, setting_value, setting_type, description)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    setting_type = VALUES(setting_type),
                    description = VALUES(description),
                    updated_at = CURRENT_TIMESTAMP
            ");
            
            $valueStr = is_array($value) ? json_encode($value) : (string) $value;
            return $stmt->execute([$key, $valueStr, $type, $description]);
        } catch (PDOException $e) {
            error_log("ThemeModel::setThemeSetting error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllThemeSettings() {
        try {
            $stmt = $this->pdo->query("SELECT setting_key, setting_value, setting_type, description FROM admin_theme_settings ORDER BY setting_key");
            $results = $stmt->fetchAll();
            
            $settings = [];
            foreach ($results as $row) {
                $value = $row['setting_value'];
                
                switch ($row['setting_type']) {
                    case 'boolean':
                        $value = (bool) $value;
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                }
                
                $settings[$row['setting_key']] = [
                    'value' => $value,
                    'type' => $row['setting_type'],
                    'description' => $row['description']
                ];
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("ThemeModel::getAllThemeSettings error: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveBanners($eventType = null) {
        try {
            if ($eventType) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM admin_banner_images 
                    WHERE is_active = 1 AND event_type = ?
                    ORDER BY sort_order ASC, id ASC
                ");
                $stmt->execute([$eventType]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT * FROM admin_banner_images 
                    WHERE is_active = 1
                    ORDER BY sort_order ASC, id ASC
                ");
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ThemeModel::getActiveBanners error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllBanners() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM admin_banner_images 
                ORDER BY sort_order ASC, id ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ThemeModel::getAllBanners error: " . $e->getMessage());
            return [];
        }
    }

    public function addBanner($imagePath, $title = null, $eventType = 'default', $animationType = 'fade', $transitionDuration = 500) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_banner_images (image_path, title, event_type, animation_type, transition_duration)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$imagePath, $title, $eventType, $animationType, $transitionDuration]);
        } catch (PDOException $e) {
            error_log("ThemeModel::addBanner error: " . $e->getMessage());
            return false;
        }
    }

    public function updateBanner($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = ['image_path', 'title', 'description', 'event_type', 'is_active', 'sort_order', 'animation_type', 'transition_duration'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $values[] = $id;
            $sql = "UPDATE admin_banner_images SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("ThemeModel::updateBanner error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBanner($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM admin_banner_images WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ThemeModel::deleteBanner error: " . $e->getMessage());
            return false;
        }
    }

    public function getActiveEvent() {
        try {
            $currentDate = date('Y-m-d');
            $stmt = $this->pdo->prepare("
                SELECT * FROM admin_theme_events 
                WHERE is_active = 1 
                AND (start_date IS NULL OR start_date <= ?)
                AND (end_date IS NULL OR end_date >= ?)
                ORDER BY start_date DESC
                LIMIT 1
            ");
            $stmt->execute([$currentDate, $currentDate]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("ThemeModel::getActiveEvent error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllEvents() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM admin_theme_events ORDER BY start_date DESC, created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ThemeModel::getAllEvents error: " . $e->getMessage());
            return [];
        }
    }

    public function createEvent($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_theme_events (event_name, event_type, start_date, end_date, is_active, theme_config)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $themeConfig = isset($data['theme_config']) ? json_encode($data['theme_config']) : null;
            
            return $stmt->execute([
                $data['event_name'],
                $data['event_type'],
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['is_active'] ?? 1,
                $themeConfig
            ]);
        } catch (PDOException $e) {
            error_log("ThemeModel::createEvent error: " . $e->getMessage());
            return false;
        }
    }

    public function updateEvent($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            $allowedFields = ['event_name', 'event_type', 'start_date', 'end_date', 'is_active', 'theme_config'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($field === 'theme_config' && is_array($data[$field])) {
                        $fields[] = "$field = ?";
                        $values[] = json_encode($data[$field]);
                    } else {
                        $fields[] = "$field = ?";
                        $values[] = $data[$field];
                    }
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $values[] = $id;
            $sql = "UPDATE admin_theme_events SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("ThemeModel::updateEvent error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteEvent($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM admin_theme_events WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ThemeModel::deleteEvent error: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================
    // HERO CONTENT METHODS
    // =====================================================
    
    /**
     * Get Hero Content for homepage
     */
    public function getHeroContent()
    {
        try {
            return [
                'title' => $this->getThemeSetting('hero_title', 'Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường'),
                'subtitle' => $this->getThemeSetting('hero_subtitle', 'Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý'),
                'button1_text' => $this->getThemeSetting('hero_button1_text', 'Mua sắm ngay'),
                'button2_text' => $this->getThemeSetting('hero_button2_text', 'Đăng bán đồ'),
            ];
        } catch (PDOException $e) {
            error_log("ThemeModel::getHeroContent error: " . $e->getMessage());
            return [
                'title' => 'Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường',
                'subtitle' => 'Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý',
                'button1_text' => 'Mua sắm ngay',
                'button2_text' => 'Đăng bán đồ',
            ];
        }
    }

    // =====================================================
    // THEME PRESETS METHODS
    // =====================================================

    public function getAllPresets() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM theme_presets ORDER BY is_system DESC, name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ThemeModel::getAllPresets error: " . $e->getMessage());
            return [];
        }
    }

    public function getActivePreset() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM theme_presets WHERE is_active = 1 LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ThemeModel::getActivePreset error: " . $e->getMessage());
            return null;
        }
    }

    public function getPresetById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM theme_presets WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ThemeModel::getPresetById error: " . $e->getMessage());
            return null;
        }
    }

    public function createPreset($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO theme_presets (name, icon, primary_color, secondary_color, accent_color, background_color, text_color, sidebar_bg, header_bg, is_system)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['icon'] ?? 'palette',
                $data['primary_color'],
                $data['secondary_color'],
                $data['accent_color'],
                $data['background_color'] ?? '#ffffff',
                $data['text_color'] ?? '#1f2937',
                $data['sidebar_bg'] ?? '#ffffff',
                $data['header_bg'] ?? '#ffffff',
                $data['is_system'] ?? 0
            ]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("ThemeModel::createPreset error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePreset($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE theme_presets SET 
                    name = ?, icon = ?, primary_color = ?, secondary_color = ?, accent_color = ?,
                    background_color = ?, text_color = ?, sidebar_bg = ?, header_bg = ?
                WHERE id = ? AND is_system = 0
            ");
            return $stmt->execute([
                $data['name'],
                $data['icon'] ?? 'palette',
                $data['primary_color'],
                $data['secondary_color'],
                $data['accent_color'],
                $data['background_color'] ?? '#ffffff',
                $data['text_color'] ?? '#1f2937',
                $data['sidebar_bg'] ?? '#ffffff',
                $data['header_bg'] ?? '#ffffff',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("ThemeModel::updatePreset error: " . $e->getMessage());
            return false;
        }
    }

    public function deletePreset($id) {
        try {
            // Only delete non-system presets
            $stmt = $this->pdo->prepare("DELETE FROM theme_presets WHERE id = ? AND is_system = 0");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ThemeModel::deletePreset error: " . $e->getMessage());
            return false;
        }
    }

    public function activatePreset($id) {
        try {
            // Deactivate all presets
            $this->pdo->exec("UPDATE theme_presets SET is_active = 0");
            
            // Activate selected preset
            $stmt = $this->pdo->prepare("UPDATE theme_presets SET is_active = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            // Get preset colors and apply to theme settings
            $preset = $this->getPresetById($id);
            if ($preset) {
                $this->setThemeSetting('primary_color', $preset['primary_color'], 'color');
                $this->setThemeSetting('secondary_color', $preset['secondary_color'], 'color');
                $this->setThemeSetting('accent_color', $preset['accent_color'], 'color');
                $this->setThemeSetting('background_color', $preset['background_color'], 'color');
                $this->setThemeSetting('text_color', $preset['text_color'], 'color');
                $this->setThemeSetting('sidebar_bg', $preset['sidebar_bg'], 'color');
                $this->setThemeSetting('header_bg', $preset['header_bg'], 'color');
                $this->setThemeSetting('active_preset_id', $id, 'text');
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("ThemeModel::activatePreset error: " . $e->getMessage());
            return false;
        }
    }

    public function insertSystemPresets() {
        try {
            // Check if system presets exist
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM theme_presets WHERE is_system = 1");
            if ($stmt->fetchColumn() > 0) {
                return true; // Already exists
            }

            $systemPresets = [
                ['Mặc định', 'palette', '#4f46e5', '#7c3aed', '#10b981', '#ffffff', '#1f2937', '#ffffff', '#ffffff'],
                ['Giáng Sinh', 'ac_unit', '#dc2626', '#16a34a', '#ffffff', '#fef2f2', '#1f2937', '#ffffff', '#ffffff'],
                ['Tết Nguyên Đán', 'celebration', '#dc2626', '#fbbf24', '#fde047', '#fef2f2', '#1f2937', '#ffffff', '#ffffff'],
            ];

            $stmt = $this->pdo->prepare("
                INSERT INTO theme_presets (name, icon, primary_color, secondary_color, accent_color, background_color, text_color, sidebar_bg, header_bg, is_system)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($systemPresets as $preset) {
                $stmt->execute($preset);
            }

            return true;
        } catch (PDOException $e) {
            error_log("ThemeModel::insertSystemPresets error: " . $e->getMessage());
            return false;
        }
    }
}

