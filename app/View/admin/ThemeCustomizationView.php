<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/ThemeModel.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

$themeModel = new ThemeModel();
$settings = $themeModel->getAllThemeSettings();
$banners = $themeModel->getAllBanners();
$events = $themeModel->getAllEvents();

// Get presets from database
$presets = $themeModel->getAllPresets();
$activePresetId = $settings['active_preset_id']['value'] ?? null;

$pageTitle = 'Tùy chỉnh giao diện';
$currentAdminPage = 'theme_customization';

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Tùy chỉnh giao diện</h1>
        <p class="text-gray-600 dark:text-gray-400">Thay đổi màu sắc, banner và sự kiện cho trang quản trị</p>
    </div>

    <!-- Main Layout: 2 Columns -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        
        <!-- Left Column: Themes & Colors -->
        <div class="space-y-6">
            <!-- Preset Themes - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-indigo-600">format_paint</span>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Theme có sẵn</h2>
                    </div>
                    <button onclick="showAddPresetModal()" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-md transition-colors flex items-center gap-1">
                        <span class="material-icons text-sm">add</span>
                        Thêm
                    </button>
                </div>
                
                <!-- Preset Grid - Compact -->
                <div id="presetGrid" class="flex flex-wrap gap-2">
                    <?php if (empty($presets)): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 py-4 w-full text-center">Chưa có theme preset nào</p>
                    <?php else: ?>
                        <?php foreach ($presets as $preset): ?>
                        <div class="preset-card group relative cursor-pointer rounded-lg p-2 border-2 transition-all hover:shadow-md <?= ($activePresetId == $preset['id']) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300' ?>" 
                             data-preset-id="<?= $preset['id'] ?>"
                             data-is-system="<?= $preset['is_system'] ?>"
                             onclick="activatePreset(<?= $preset['id'] ?>)"
                             title="<?= htmlspecialchars($preset['name']) ?>">
                            
                            <!-- Active indicator -->
                            <?php if ($activePresetId == $preset['id']): ?>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="material-icons text-white text-xs">check</span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Colors preview -->
                            <div class="flex gap-0.5 mb-1">
                                <div class="w-5 h-5 rounded-sm" style="background-color: <?= $preset['primary_color'] ?>"></div>
                                <div class="w-5 h-5 rounded-sm" style="background-color: <?= $preset['secondary_color'] ?>"></div>
                                <div class="w-5 h-5 rounded-sm" style="background-color: <?= $preset['accent_color'] ?>"></div>
                            </div>
                            
                            <!-- Name -->
                            <div class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate max-w-[70px] flex items-center gap-1">
                                <span class="material-icons text-xs opacity-60" style="color: <?= $preset['primary_color'] ?>"><?= htmlspecialchars($preset['icon']) ?></span>
                                <span class="truncate"><?= htmlspecialchars($preset['name']) ?></span>
                            </div>
                            
                            <!-- Actions (show on hover, only for custom presets) -->
                            <?php if (!$preset['is_system']): ?>
                            <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity flex">
                                <button onclick="event.stopPropagation(); editPreset(<?= $preset['id'] ?>)" class="p-1 text-gray-500 hover:text-indigo-600" title="Sửa">
                                    <span class="material-icons text-xs">edit</span>
                                </button>
                                <button onclick="event.stopPropagation(); deletePreset(<?= $preset['id'] ?>)" class="p-1 text-gray-500 hover:text-red-600" title="Xóa">
                                    <span class="material-icons text-xs">delete</span>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Color Customization -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-indigo-600">palette</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tùy chỉnh màu sắc</h2>
                    </div>
                    <button onclick="resetColors()" class="px-3 py-1.5 text-sm text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-md transition-colors flex items-center gap-1">
                        <span class="material-icons text-sm">refresh</span>
                        Đặt lại
                    </button>
                </div>
                
                <form id="themeSettingsForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Primary Color -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">color_lens</span>
                                Màu chủ đạo
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-primary" 
                                     style="background-color: <?= htmlspecialchars($settings['primary_color']['value'] ?? '#4f46e5') ?>"
                                     onclick="document.getElementById('input-primary').click()"></div>
                                <input type="color" id="input-primary" name="settings[primary_color]" 
                                       value="<?= htmlspecialchars($settings['primary_color']['value'] ?? '#4f46e5') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('primary', this.value)">
                                <input type="text" id="hex-primary" 
                                       value="<?= strtoupper(htmlspecialchars($settings['primary_color']['value'] ?? '#4f46e5')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Secondary Color -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">brush</span>
                                Màu phụ
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-secondary" 
                                     style="background-color: <?= htmlspecialchars($settings['secondary_color']['value'] ?? '#7c3aed') ?>"
                                     onclick="document.getElementById('input-secondary').click()"></div>
                                <input type="color" id="input-secondary" name="settings[secondary_color]" 
                                       value="<?= htmlspecialchars($settings['secondary_color']['value'] ?? '#7c3aed') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('secondary', this.value)">
                                <input type="text" id="hex-secondary" 
                                       value="<?= strtoupper(htmlspecialchars($settings['secondary_color']['value'] ?? '#7c3aed')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Accent Color -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">star</span>
                                Màu nhấn
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-accent" 
                                     style="background-color: <?= htmlspecialchars($settings['accent_color']['value'] ?? '#10b981') ?>"
                                     onclick="document.getElementById('input-accent').click()"></div>
                                <input type="color" id="input-accent" name="settings[accent_color]" 
                                       value="<?= htmlspecialchars($settings['accent_color']['value'] ?? '#10b981') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('accent', this.value)">
                                <input type="text" id="hex-accent" 
                                       value="<?= strtoupper(htmlspecialchars($settings['accent_color']['value'] ?? '#10b981')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Background Color -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">wallpaper</span>
                                Màu nền
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-background" 
                                     style="background-color: <?= htmlspecialchars($settings['background_color']['value'] ?? '#ffffff') ?>"
                                     onclick="document.getElementById('input-background').click()"></div>
                                <input type="color" id="input-background" name="settings[background_color]" 
                                       value="<?= htmlspecialchars($settings['background_color']['value'] ?? '#ffffff') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('background', this.value)">
                                <input type="text" id="hex-background" 
                                       value="<?= strtoupper(htmlspecialchars($settings['background_color']['value'] ?? '#FFFFFF')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Text Color -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">text_fields</span>
                                Màu chữ
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-text" 
                                     style="background-color: <?= htmlspecialchars($settings['text_color']['value'] ?? '#1f2937') ?>"
                                     onclick="document.getElementById('input-text').click()"></div>
                                <input type="color" id="input-text" name="settings[text_color]" 
                                       value="<?= htmlspecialchars($settings['text_color']['value'] ?? '#1f2937') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('text', this.value)">
                                <input type="text" id="hex-text" 
                                       value="<?= strtoupper(htmlspecialchars($settings['text_color']['value'] ?? '#1F2937')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Sidebar Background -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">view_sidebar</span>
                                Nền Sidebar
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-sidebar" 
                                     style="background-color: <?= htmlspecialchars($settings['sidebar_bg']['value'] ?? '#ffffff') ?>"
                                     onclick="document.getElementById('input-sidebar').click()"></div>
                                <input type="color" id="input-sidebar" name="settings[sidebar_bg]" 
                                       value="<?= htmlspecialchars($settings['sidebar_bg']['value'] ?? '#ffffff') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('sidebar', this.value)">
                                <input type="text" id="hex-sidebar" 
                                       value="<?= strtoupper(htmlspecialchars($settings['sidebar_bg']['value'] ?? '#FFFFFF')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>

                        <!-- Header Background -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <span class="material-icons align-middle text-sm">view_headline</span>
                                Nền Header
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer shadow-sm hover:shadow-md transition-shadow" 
                                     id="preview-header" 
                                     style="background-color: <?= htmlspecialchars($settings['header_bg']['value'] ?? '#ffffff') ?>"
                                     onclick="document.getElementById('input-header').click()"></div>
                                <input type="color" id="input-header" name="settings[header_bg]" 
                                       value="<?= htmlspecialchars($settings['header_bg']['value'] ?? '#ffffff') ?>"
                                       class="sr-only"
                                       onchange="updateColorPreview('header', this.value)">
                                <input type="text" id="hex-header" 
                                       value="<?= strtoupper(htmlspecialchars($settings['header_bg']['value'] ?? '#FFFFFF')) ?>"
                                       class="flex-1 px-2 py-1.5 text-xs font-mono bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white"
                                       readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Cài đặt bổ sung</h3>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="material-icons text-gray-600 dark:text-gray-400 text-sm">image</span>
                                    <div>
                                        <label class="text-sm font-medium text-gray-900 dark:text-white">Bật banner</label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Hiển thị banner slider</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="settings[enable_banner]" value="0" id="hidden-enable-banner">
                                    <input type="checkbox" id="checkbox-enable-banner" value="1" 
                                           <?= ($settings['enable_banner']['value'] ?? '1') == '1' ? 'checked' : '' ?>
                                           class="sr-only peer"
                                           onchange="document.getElementById('hidden-enable-banner').value = this.checked ? '1' : '0'">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="material-icons text-gray-600 dark:text-gray-400 text-sm">animation</span>
                                    <div>
                                        <label class="text-sm font-medium text-gray-900 dark:text-white">Bật animation</label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Hiệu ứng chuyển động</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="settings[animation_enabled]" value="0" id="hidden-animation-enabled">
                                    <input type="checkbox" id="checkbox-animation-enabled" value="1" 
                                           <?= ($settings['animation_enabled']['value'] ?? '1') == '1' ? 'checked' : '' ?>
                                           class="sr-only peer"
                                           onchange="document.getElementById('hidden-animation-enabled').value = this.checked ? '1' : '0'">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    <span class="material-icons align-middle text-sm">height</span>
                                    Chiều cao banner (px)
                                </label>
                                <input type="number" name="settings[banner_height]" 
                                       value="<?= htmlspecialchars($settings['banner_height']['value'] ?? '200') ?>"
                                       min="100" max="500" step="10"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span class="material-icons text-sm">save</span>
                            Lưu cài đặt
                        </button>
                        <button type="button" onclick="previewTheme()" class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                            <span class="material-icons text-sm">preview</span>
                            Xem trước
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Banner & Events -->
        <div class="space-y-6">
            <!-- Ảnh nền Website (Body Background) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-icons text-indigo-600">wallpaper</span>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Ảnh nền Website</h2>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Ảnh hiển thị ở <strong>hai bên website</strong> (phía sau toàn bộ nội dung, giống thegioididong.com)
                </p>
                
                <?php $websiteBg = $settings['website_background']['value'] ?? ''; ?>
                
                <div id="website-bg-preview" class="relative mb-3 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 h-40 flex items-center justify-center"
                     style="<?= $websiteBg ? 'background: url('.BASE_URL.ltrim($websiteBg, '/').') center/cover no-repeat;' : '' ?>">
                    
                    <?php if (!$websiteBg): ?>
                        <div class="text-center text-gray-500 dark:text-gray-400" id="website-bg-placeholder">
                            <span class="material-icons text-4xl">add_photo_alternate</span>
                            <p class="text-sm mt-2">Chưa có ảnh nền website</p>
                            <p class="text-xs mt-1 text-gray-400">Hiển thị phía sau toàn bộ trang</p>
                        </div>
                    <?php else: ?>
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                            <span class="text-white text-sm">Ảnh nền website hiện tại</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <input type="file" id="website-bg-input" accept="image/*" class="hidden" onchange="uploadWebsiteBackground(this)">
                <input type="hidden" name="settings[website_background]" id="website-bg-value" value="<?= htmlspecialchars($websiteBg) ?>">
                
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('website-bg-input').click()" 
                            class="flex-1 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                        <span class="material-icons text-sm">upload</span>
                        Tải lên ảnh nền
                    </button>
                    <?php if ($websiteBg): ?>
                    <button type="button" onclick="removeWebsiteBackground()" 
                            class="px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2" id="remove-website-bg-btn">
                        <span class="material-icons text-sm">delete</span>
                        Xóa
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Banner Management -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-indigo-600">collections</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Nội dung Banner & Slider</h2>
                    </div>
                    <button onclick="showBannerModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1.5">
                        <span class="material-icons text-sm">add</span>
                        Thêm ảnh
                    </button>
                </div>
                
                <!-- Hero Content Settings -->
                <div class="mb-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-700/50 dark:to-gray-700/30 rounded-lg border border-indigo-100 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <span class="material-icons text-indigo-600 text-sm">edit_note</span>
                        Nội dung hiển thị trên Banner
                    </h3>
                    <form id="heroContentForm" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <span class="material-icons align-middle text-sm">title</span>
                                Tiêu đề Banner
                            </label>
                            <input type="text" name="settings[hero_title]" 
                                   value="<?= htmlspecialchars($settings['hero_title']['value'] ?? 'Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường') ?>"
                                   class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <span class="material-icons align-middle text-sm">description</span>
                                Mô tả Banner
                            </label>
                            <textarea name="settings[hero_subtitle]" rows="2"
                                   class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars($settings['hero_subtitle']['value'] ?? 'Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!') ?></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span class="material-icons align-middle text-sm">shopping_cart</span>
                                    Nút 1 (Mua sắm)
                                </label>
                                <input type="text" name="settings[hero_button1_text]" 
                                       value="<?= htmlspecialchars($settings['hero_button1_text']['value'] ?? 'Mua sắm ngay') ?>"
                                       class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <span class="material-icons align-middle text-sm">store</span>
                                    Nút 2 (Đăng bán)
                                </label>
                                <input type="text" name="settings[hero_button2_text]" 
                                       value="<?= htmlspecialchars($settings['hero_button2_text']['value'] ?? 'Đăng bán đồ') ?>"
                                       class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <button type="button" onclick="saveHeroContent()" class="w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition-colors flex items-center justify-center gap-1">
                            <span class="material-icons text-sm">save</span>
                            Lưu nội dung Banner
                        </button>
                    </form>
                </div>
                
                <!-- Hero Background Image -->
                <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <span class="material-icons text-indigo-600 text-sm">image</span>
                        Ảnh nền Hero Section
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                        Ảnh nền hiển thị phía sau nội dung Banner trên trang chủ
                    </p>
                    
                    <?php $heroBackground = $settings['hero_background_image']['value'] ?? ''; ?>
                    
                    <div id="hero-bg-preview" class="relative mb-3 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 h-32 flex items-center justify-center"
                         style="<?= $heroBackground ? 'background: url('.BASE_URL.ltrim($heroBackground, '/').') center/cover no-repeat;' : '' ?>">
                        
                        <?php if (!$heroBackground): ?>
                            <div class="text-center text-gray-500 dark:text-gray-400" id="hero-bg-placeholder">
                                <span class="material-icons text-3xl">add_photo_alternate</span>
                                <p class="text-xs mt-1">Chưa có ảnh nền Hero</p>
                            </div>
                        <?php else: ?>
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                <span class="text-white text-xs">Ảnh nền Hero hiện tại</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="file" id="hero-bg-input" accept="image/*" class="hidden" onchange="uploadHeroBackground(this)">
                    <input type="hidden" name="settings[hero_background_image]" id="hero-bg-value" value="<?= htmlspecialchars($heroBackground) ?>">
                    
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('hero-bg-input').click()" 
                                class="flex-1 px-3 py-2 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-1">
                            <span class="material-icons text-sm">upload</span>
                            Tải lên ảnh nền Hero
                        </button>
                        <?php if ($heroBackground): ?>
                        <button type="button" onclick="removeHeroBackground()" 
                                class="px-3 py-2 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-1" id="remove-hero-bg-btn">
                            <span class="material-icons text-sm">delete</span>
                            Xóa
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Banner Images List -->
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ảnh Banner Slider</h3>
                <?php if (empty($banners)): ?>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <span class="material-icons text-4xl mb-2 opacity-50">image_not_supported</span>
                    <p class="text-sm">Chưa có banner nào</p>
                </div>
                <?php else: ?>
                <div id="bannerList" class="space-y-2">
                    <?php foreach ($banners as $banner): ?>
                    <div class="banner-card flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow" data-id="<?= $banner['id'] ?>">
                        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <span class="material-icons text-lg">drag_handle</span>
                        </div>
                        <img src="<?= BASE_URL . ltrim($banner['image_path'], '/') ?>" 
                             alt="<?= htmlspecialchars($banner['title'] ?? '') ?>"
                             class="w-20 h-14 object-cover rounded border border-gray-300 dark:border-gray-600"
                             onerror="this.src='<?= BASE_URL ?>public/assets/images/placeholder.png'">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                <?= htmlspecialchars($banner['title'] ?? 'Không có tiêu đề') ?>
                            </h3>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                <span class="flex items-center gap-0.5">
                                    <span class="material-icons text-xs">event</span>
                                    <?= htmlspecialchars($banner['event_type']) ?>
                                </span>
                                <span class="flex items-center gap-0.5">
                                    <span class="material-icons text-xs">animation</span>
                                    <?= htmlspecialchars($banner['animation_type']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" <?= $banner['is_active'] ? 'checked' : '' ?>
                                       onchange="toggleBannerStatus(<?= $banner['id'] ?>, this.checked)"
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                            <button onclick="editBanner(<?= $banner['id'] ?>)" 
                                    class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded transition-colors">
                                <span class="material-icons text-sm">edit</span>
                            </button>
                            <button onclick="deleteBanner(<?= $banner['id'] ?>)" 
                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                <span class="material-icons text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Event Management -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-indigo-600">event</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Quản lý sự kiện</h2>
                    </div>
                    <button onclick="showEventModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1.5">
                        <span class="material-icons text-sm">add</span>
                        Thêm
                    </button>
                </div>
                
                <?php if (empty($events)): ?>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <span class="material-icons text-4xl mb-2 opacity-50">event_busy</span>
                    <p class="text-sm">Chưa có sự kiện nào</p>
                </div>
                <?php else: ?>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    <?php foreach ($events as $event): ?>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                    <?= htmlspecialchars($event['event_name']) ?>
                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    <?= htmlspecialchars($event['event_type']) ?>
                                </span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" <?= $event['is_active'] ? 'checked' : '' ?>
                                       onchange="toggleEventStatus(<?= $event['id'] ?>, this.checked)"
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 dark:peer-focus:ring-indigo-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400 mb-2">
                            <span class="flex items-center gap-1">
                                <span class="material-icons text-xs">calendar_today</span>
                                <?= $event['start_date'] ? date('d/m/Y', strtotime($event['start_date'])) : 'Không giới hạn' ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="material-icons text-xs">event</span>
                                <?= $event['end_date'] ? date('d/m/Y', strtotime($event['end_date'])) : 'Không giới hạn' ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editEvent(<?= $event['id'] ?>)" 
                                    class="flex-1 px-2 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded transition-colors flex items-center justify-center gap-1">
                                <span class="material-icons text-sm">edit</span>
                                Sửa
                            </button>
                            <button onclick="deleteEvent(<?= $event['id'] ?>)" 
                                    class="px-2 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                <span class="material-icons text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Banner Modal -->
<div id="bannerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-icons">add_photo_alternate</span>
                Thêm banner
            </h3>
            <button onclick="closeBannerModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="bannerForm" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="material-icons align-middle text-sm">image</span>
                    Ảnh banner
                </label>
                <input type="file" name="banner_image" accept="image/*" required
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="material-icons align-middle text-sm">title</span>
                    Tiêu đề
                </label>
                <input type="text" name="title" placeholder="Nhập tiêu đề banner"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <span class="material-icons align-middle text-sm">event</span>
                        Loại sự kiện
                    </label>
                    <select name="event_type" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="default">Mặc định</option>
                        <option value="noel">Giáng Sinh</option>
                        <option value="tet">Tết Nguyên Đán</option>
                        <option value="custom">Tùy chỉnh</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <span class="material-icons align-middle text-sm">animation</span>
                        Animation
                    </label>
                    <select name="animation_type" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="fade">Fade</option>
                        <option value="slide">Slide</option>
                        <option value="zoom">Zoom</option>
                        <option value="none">Không có</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="material-icons align-middle text-sm">timer</span>
                    Thời gian chuyển (ms)
                </label>
                <input type="number" name="transition_duration" value="500" min="100" max="2000" step="100"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <span class="material-icons text-sm">save</span>
                    Lưu
                </button>
                <button type="button" onclick="closeBannerModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-icons">event</span>
                <span id="eventModalTitle">Thêm sự kiện</span>
            </h3>
            <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="eventForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="material-icons align-middle text-sm">label</span>
                    Tên sự kiện
                </label>
                <input type="text" name="event_name" required placeholder="Ví dụ: Giáng Sinh 2024"
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <span class="material-icons align-middle text-sm">category</span>
                    Loại sự kiện
                </label>
                <select name="event_type" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="default">Mặc định</option>
                    <option value="noel">Giáng Sinh</option>
                    <option value="tet">Tết Nguyên Đán</option>
                    <option value="custom">Tùy chỉnh</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <span class="material-icons align-middle text-sm">calendar_today</span>
                        Ngày bắt đầu
                    </label>
                    <input type="date" name="start_date"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <span class="material-icons align-middle text-sm">event</span>
                        Ngày kết thúc
                    </label>
                    <input type="date" name="end_date"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <span class="material-icons text-sm">save</span>
                    Lưu
                </button>
                <button type="button" onclick="closeEventModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preset Modal -->
<div id="presetModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="presetModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Thêm Theme Mới</h3>
        </div>
        <form id="presetForm" class="p-4 space-y-3">
            <input type="hidden" id="presetId" name="id" value="">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tên theme</label>
                    <input type="text" id="presetName" name="name" required
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Icon</label>
                    <select id="presetIcon" name="icon" class="w-full px-2 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="palette">🎨 Palette</option>
                        <option value="ac_unit">❄️ Tuyết</option>
                        <option value="celebration">🧧 Lễ hội</option>
                        <option value="dark_mode">🌙 Dark</option>
                        <option value="light_mode">☀️ Light</option>
                        <option value="favorite">❤️ Yêu thích</option>
                        <option value="star">⭐ Ngôi sao</option>
                        <option value="eco">🌿 Eco</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Màu chính</label>
                    <input type="color" id="presetPrimary" name="primary_color" value="#4f46e5" class="w-full h-8 rounded cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Màu phụ</label>
                    <input type="color" id="presetSecondary" name="secondary_color" value="#7c3aed" class="w-full h-8 rounded cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Màu nhấn</label>
                    <input type="color" id="presetAccent" name="accent_color" value="#10b981" class="w-full h-8 rounded cursor-pointer">
                </div>
            </div>
            
            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Lưu
                </button>
                <button type="button" onclick="closePresetModal()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-4 min-w-[300px] max-w-md">
    <span class="mdc-snackbar__label flex-1 text-sm" id="toastMessage"></span>
    <button onclick="hideToast()" class="text-indigo-300 hover:text-white text-sm font-medium">Đóng</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
const API_URL = '<?= BASE_URL ?>app/Controllers/admin/ThemeController.php';
let currentPresets = <?= json_encode($presets, JSON_UNESCAPED_UNICODE) ?>;
let activePresetId = <?= $activePresetId ? $activePresetId : 'null' ?>;

function updateColorPreview(type, color) {
    document.getElementById(`preview-${type}`).style.backgroundColor = color;
    document.getElementById(`hex-${type}`).value = color.toUpperCase();
}

// =====================================================
// PRESET MANAGEMENT
// =====================================================

function showAddPresetModal() {
    document.getElementById('presetModalTitle').textContent = 'Thêm Theme Mới';
    document.getElementById('presetForm').reset();
    document.getElementById('presetId').value = '';
    document.getElementById('presetModal').classList.remove('hidden');
}

function closePresetModal() {
    document.getElementById('presetModal').classList.add('hidden');
}

async function editPreset(id) {
    const preset = currentPresets.find(p => p.id == id);
    if (!preset) return;
    
    document.getElementById('presetModalTitle').textContent = 'Sửa Theme';
    document.getElementById('presetId').value = id;
    document.getElementById('presetName').value = preset.name;
    document.getElementById('presetIcon').value = preset.icon;
    document.getElementById('presetPrimary').value = preset.primary_color;
    document.getElementById('presetSecondary').value = preset.secondary_color;
    document.getElementById('presetAccent').value = preset.accent_color;
    document.getElementById('presetModal').classList.remove('hidden');
}

async function deletePreset(id) {
    if (!confirm('Bạn có chắc muốn xóa theme này?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_preset');
        formData.append('id', id);
        
        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
            location.reload();
        } else {
            showToast('Lỗi: ' + result.message);
        }
    } catch (error) {
        showToast('Lỗi kết nối: ' + error.message);
    }
}

async function activatePreset(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'activate_preset');
        formData.append('id', id);
        
        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
            // Update active state visually
            document.querySelectorAll('.preset-card').forEach(card => {
                card.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/30');
                card.classList.add('border-gray-200', 'dark:border-gray-600');
                const checkIcon = card.querySelector('.absolute.-top-1.-right-1');
                if (checkIcon) checkIcon.remove();
            });
            const activeCard = document.querySelector(`[data-preset-id="${id}"]`);
            if (activeCard) {
                activeCard.classList.remove('border-gray-200', 'dark:border-gray-600');
                activeCard.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-indigo-900/30');
            }
            // Reload to apply colors
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Lỗi: ' + result.message);
        }
    } catch (error) {
        showToast('Lỗi kết nối: ' + error.message);
    }
}

// Handle preset form submit
document.getElementById('presetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const presetId = document.getElementById('presetId').value;
    formData.append('action', presetId ? 'update_preset' : 'add_preset');
    
    try {
        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message);
            closePresetModal();
            location.reload();
        } else {
            showToast('Lỗi: ' + result.message);
        }
    } catch (error) {
        showToast('Lỗi kết nối: ' + error.message);
    }
});

// Legacy applyPreset for old presets array (now unused but kept for compatibility)
function applyPreset(presetKey) {
    showToast('Vui lòng click vào theme để áp dụng');
}

function resetColors() {
    if (!confirm('Bạn có chắc muốn đặt lại tất cả màu sắc về mặc định?')) {
        return;
    }
    // Find and activate first system preset (default)
    const defaultPreset = currentPresets.find(p => p.is_system == 1 && p.name === 'Mặc định');
    if (defaultPreset) {
        activatePreset(defaultPreset.id);
    }
}

function previewTheme() {
    showToast('Tính năng xem trước sẽ được cập nhật trong phiên bản tiếp theo');
}

function showToast(message, duration = 3000) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    toastMessage.textContent = message;
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        hideToast();
    }, duration);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

// Background Image Upload
async function uploadBackgroundImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ảnh quá lớn! Dung lượng tối đa 2MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('background_image', file);
    formData.append('action', 'upload_background');
    
    try {
        showToast('Đang tải lên...');
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=upload_background', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('background-image-value').value = result.path;
            document.getElementById('bg-image-preview').style.background = `url(<?= BASE_URL ?>${result.path}) center/cover no-repeat`;
            
            // Update placeholder
            const placeholder = document.getElementById('bg-image-placeholder');
            if (placeholder) placeholder.remove();
            
            // Add/show remove button
            let removeBtn = document.getElementById('remove-bg-btn');
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.id = 'remove-bg-btn';
                removeBtn.type = 'button';
                removeBtn.onclick = removeBackgroundImage;
                removeBtn.className = 'px-3 py-2 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-1';
                removeBtn.innerHTML = '<span class="material-icons text-sm">delete</span> Xóa';
                document.querySelector('#background-image-input').parentElement.querySelector('.flex.gap-2').appendChild(removeBtn);
            }
            
            showToast('Đã tải lên ảnh nền thành công!');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể tải lên'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}

// Website Background Image Upload
async function uploadWebsiteBackground(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('Ảnh quá lớn! Dung lượng tối đa 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('website_background', file);
    formData.append('action', 'upload_website_bg');
    
    try {
        showToast('Đang tải lên...');
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=upload_website_bg', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('website-bg-value').value = result.path;
            document.getElementById('website-bg-preview').style.background = `url(<?= BASE_URL ?>${result.path}) center/cover no-repeat`;
            
            // Update placeholder
            const placeholder = document.getElementById('website-bg-placeholder');
            if (placeholder) placeholder.remove();
            
            // Add/show remove button
            let removeBtn = document.getElementById('remove-website-bg-btn');
            if (!removeBtn) {
                const btnContainer = document.querySelector('#website-bg-input').parentElement.querySelector('.flex.gap-2');
                if (btnContainer) {
                    removeBtn = document.createElement('button');
                    removeBtn.id = 'remove-website-bg-btn';
                    removeBtn.type = 'button';
                    removeBtn.onclick = removeWebsiteBackground;
                    removeBtn.className = 'px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2';
                    removeBtn.innerHTML = '<span class="material-icons text-sm">delete</span> Xóa';
                    btnContainer.appendChild(removeBtn);
                }
            }
            
            showToast('Đã tải lên ảnh nền website!');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể tải lên'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}

async function removeWebsiteBackground() {
    if (!confirm('Bạn có chắc muốn xóa ảnh nền website?')) return;
    
    try {
        const currentPath = document.getElementById('website-bg-value').value;
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=remove_website_bg', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: currentPath })
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('website-bg-value').value = '';
            document.getElementById('website-bg-preview').style.background = '';
            document.getElementById('website-bg-preview').innerHTML = `
                <div class="text-center text-gray-500 dark:text-gray-400" id="website-bg-placeholder">
                    <span class="material-icons text-4xl">add_photo_alternate</span>
                    <p class="text-sm mt-2">Chưa có ảnh nền website</p>
                    <p class="text-xs mt-1 text-gray-400">Hiển thị phía sau toàn bộ trang</p>
                </div>
            `;
            
            const removeBtn = document.getElementById('remove-website-bg-btn');
            if (removeBtn) removeBtn.remove();
            
            showToast('Đã xóa ảnh nền website');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể xóa'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}

// Save Hero Content (title, subtitle, buttons)
async function saveHeroContent() {
    const form = document.getElementById('heroContentForm');
    const formData = new FormData(form);
    formData.append('action', 'save_settings');
    
    try {
        showToast('Đang lưu...');
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=save_settings', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Đã lưu nội dung Banner!');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể lưu'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}

// Hero Background Image Upload
async function uploadHeroBackground(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('Ảnh quá lớn! Dung lượng tối đa 5MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('hero_background', file);
    formData.append('action', 'upload_hero_bg');
    
    try {
        showToast('Đang tải lên...');
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=upload_hero_bg', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('hero-bg-value').value = result.path;
            document.getElementById('hero-bg-preview').style.background = `url(<?= BASE_URL ?>${result.path}) center/cover no-repeat`;
            
            const placeholder = document.getElementById('hero-bg-placeholder');
            if (placeholder) placeholder.remove();
            
            let removeBtn = document.getElementById('remove-hero-bg-btn');
            if (!removeBtn) {
                const btnContainer = document.querySelector('#hero-bg-input').parentElement.querySelector('.flex.gap-2');
                if (btnContainer) {
                    removeBtn = document.createElement('button');
                    removeBtn.id = 'remove-hero-bg-btn';
                    removeBtn.type = 'button';
                    removeBtn.onclick = removeHeroBackground;
                    removeBtn.className = 'px-3 py-2 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-1';
                    removeBtn.innerHTML = '<span class="material-icons text-sm">delete</span> Xóa';
                    btnContainer.appendChild(removeBtn);
                }
            }
            
            showToast('Đã tải lên ảnh nền Hero!');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể tải lên'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}

async function removeHeroBackground() {
    if (!confirm('Bạn có chắc muốn xóa ảnh nền Hero?')) return;
    
    try {
        const currentPath = document.getElementById('hero-bg-value').value;
        
        const response = await fetch('<?= BASE_URL ?>app/Controllers/admin/ThemeController.php?action=remove_hero_bg', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: currentPath })
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('hero-bg-value').value = '';
            document.getElementById('hero-bg-preview').style.background = '';
            document.getElementById('hero-bg-preview').innerHTML = `
                <div class="text-center text-gray-500 dark:text-gray-400" id="hero-bg-placeholder">
                    <span class="material-icons text-3xl">add_photo_alternate</span>
                    <p class="text-xs mt-1">Chưa có ảnh nền Hero</p>
                </div>
            `;
            
            const removeBtn = document.getElementById('remove-hero-bg-btn');
            if (removeBtn) removeBtn.remove();
            
            showToast('Đã xóa ảnh nền Hero');
        } else {
            showToast('Lỗi: ' + (result.message || 'Không thể xóa'));
        }
    } catch (error) {
        console.error(error);
        showToast('Lỗi kết nối!');
    }
}


document.getElementById('themeSettingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'save_settings');
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-icons text-sm animate-spin">hourglass_empty</span> Đang lưu...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        const text = await response.text();
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            showToast('Lưu thành công! Trang sẽ được tải lại.', 2000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'), 5000);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối: ' + error.message, 5000);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.getElementById('bannerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'upload_banner');
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-icons text-sm animate-spin">hourglass_empty</span> Đang tải...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        const text = await response.text();
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            showToast('Upload thành công!', 2000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'), 5000);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối: ' + error.message, 5000);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.getElementById('eventForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const eventId = formData.get('event_id');
    formData.append('action', eventId ? 'update_event' : 'create_event');
    if (eventId) {
        formData.append('id', eventId);
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-icons text-sm animate-spin">hourglass_empty</span> Đang lưu...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        const text = await response.text();
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            showToast('Lưu thành công!', 2000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'), 5000);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Lỗi kết nối: ' + error.message, 5000);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Initialize toggle checkboxes - sync hidden inputs with checkbox state
    const bannerCheckbox = document.getElementById('checkbox-enable-banner');
    const animationCheckbox = document.getElementById('checkbox-animation-enabled');
    
    if (bannerCheckbox) {
        document.getElementById('hidden-enable-banner').value = bannerCheckbox.checked ? '1' : '0';
    }
    if (animationCheckbox) {
        document.getElementById('hidden-animation-enabled').value = animationCheckbox.checked ? '1' : '0';
    }
    
    const bannerList = document.getElementById('bannerList');
    if (bannerList) {
        new Sortable(bannerList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            dragClass: 'shadow-lg',
            onEnd: async function(evt) {
                const items = Array.from(bannerList.children);
                const orders = items.map((item, index) => ({
                    id: parseInt(item.dataset.id),
                    sort_order: index
                }));
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'reorder_banners');
                    formData.append('orders', JSON.stringify(orders));
                    
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const text = await response.text();
                    const result = JSON.parse(text);
                    
                    if (result.success) {
                        showToast('Đã sắp xếp lại banner');
                    } else {
                        showToast('Lỗi khi sắp xếp: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Lỗi kết nối khi sắp xếp');
                }
            }
        });
    }
});

function showBannerModal() {
    document.getElementById('bannerModal').classList.remove('hidden');
}

function closeBannerModal() {
    document.getElementById('bannerModal').classList.add('hidden');
    document.getElementById('bannerForm').reset();
}

function showEventModal(eventId = null) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    const title = document.getElementById('eventModalTitle');
    
    if (eventId) {
        title.textContent = 'Sửa sự kiện';
        form.innerHTML += '<input type="hidden" name="event_id" value="' + eventId + '">';
    } else {
        title.textContent = 'Thêm sự kiện';
    }
    
    modal.classList.remove('hidden');
}

function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
    document.getElementById('eventForm').reset();
    const eventIdInput = document.getElementById('eventForm').querySelector('input[name="event_id"]');
    if (eventIdInput) eventIdInput.remove();
}

function toggleBannerStatus(id, isActive) {
    const formData = new FormData();
    formData.append('action', 'update_banner');
    formData.append('id', id);
    formData.append('is_active', isActive ? 1 : 0);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        const result = JSON.parse(text);
        if (result.success) {
            showToast(isActive ? 'Đã bật banner' : 'Đã tắt banner');
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Lỗi kết nối');
    });
}

function editBanner(id) {
    showToast('Tính năng sửa banner sẽ được cập nhật sau');
}

function deleteBanner(id) {
    if (!confirm('Bạn có chắc muốn xóa banner này?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_banner');
    formData.append('id', id);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        const result = JSON.parse(text);
        if (result.success) {
            showToast('Xóa thành công!', 2000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Lỗi kết nối: ' + error.message);
    });
}

function toggleEventStatus(id, isActive) {
    const formData = new FormData();
    formData.append('action', 'update_event');
    formData.append('id', id);
    formData.append('is_active', isActive ? 1 : 0);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        const result = JSON.parse(text);
        if (result.success) {
            showToast(isActive ? 'Đã kích hoạt sự kiện' : 'Đã tắt sự kiện');
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Lỗi kết nối');
    });
}

function editEvent(id) {
    showEventModal(id);
}

function deleteEvent(id) {
    if (!confirm('Bạn có chắc muốn xóa sự kiện này?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_event');
    formData.append('id', id);
    
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        const result = JSON.parse(text);
        if (result.success) {
            showToast('Xóa thành công!', 2000);
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast('Lỗi: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Lỗi kết nối: ' + error.message);
    });
}
</script>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>
