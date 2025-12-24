<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/ThemeModel.php';

class AdminThemeRenderer {
    private $themeModel;
    
    public function __construct() {
        $this->themeModel = new ThemeModel();
    }

    public function renderThemeStyles() {
        try {
            $settings = $this->themeModel->getAllThemeSettings();
            $activeEvent = $this->themeModel->getActiveEvent();
        } catch (Exception $e) {
            error_log("AdminThemeRenderer error: " . $e->getMessage());
            $settings = [];
            $activeEvent = null;
        }
        
        $primaryColor = $settings['primary_color']['value'] ?? '#4f46e5';
        $secondaryColor = $settings['secondary_color']['value'] ?? '#7c3aed';
        $accentColor = $settings['accent_color']['value'] ?? '#10b981';
        $backgroundColor = $settings['background_color']['value'] ?? '#ffffff';
        $textColor = $settings['text_color']['value'] ?? '#1f2937';
        $sidebarBg = $settings['sidebar_bg']['value'] ?? '#ffffff';
        $headerBg = $settings['header_bg']['value'] ?? '#ffffff';
        
        if ($activeEvent && isset($activeEvent['theme_config'])) {
            $eventConfig = json_decode($activeEvent['theme_config'], true);
            if ($eventConfig) {
                $primaryColor = $eventConfig['primary_color'] ?? $primaryColor;
                $secondaryColor = $eventConfig['secondary_color'] ?? $secondaryColor;
                $accentColor = $eventConfig['accent_color'] ?? $accentColor;
            }
        }
        
        echo "<style id='admin-theme-styles'>";
        echo ":root {";
        echo "  --admin-primary: {$primaryColor};";
        echo "  --admin-secondary: {$secondaryColor};";
        echo "  --admin-accent: {$accentColor};";
        echo "  --admin-bg: {$backgroundColor};";
        echo "  --admin-text: {$textColor};";
        echo "  --admin-sidebar-bg: {$sidebarBg};";
        echo "  --admin-header-bg: {$headerBg};";
        echo "}";
        
        echo ".admin-theme-primary { background-color: var(--admin-primary) !important; }";
        echo ".admin-theme-secondary { background-color: var(--admin-secondary) !important; }";
        echo ".admin-theme-accent { background-color: var(--admin-accent) !important; }";
        echo ".admin-theme-text { color: var(--admin-text) !important; }";
        
        echo "</style>";
    }

    public function renderBanner($containerClass = 'admin-banner-container') {
        try {
            $enableBanner = $this->themeModel->getThemeSetting('enable_banner', true);
            
            if (!$enableBanner) {
                return '';
            }
            
            $activeEvent = $this->themeModel->getActiveEvent();
            $eventType = $activeEvent ? $activeEvent['event_type'] : 'default';
            
            $banners = $this->themeModel->getActiveBanners($eventType);
            
            if (empty($banners)) {
                $banners = $this->themeModel->getActiveBanners('default');
            }
            
            if (empty($banners)) {
                return '';
            }
        } catch (Exception $e) {
            error_log("AdminThemeRenderer renderBanner error: " . $e->getMessage());
            return '';
        }
        
        $bannerHeight = $this->themeModel->getThemeSetting('banner_height', 200);
        $animationEnabled = $this->themeModel->getThemeSetting('animation_enabled', true);
        
        $html = "<div class='{$containerClass}' style='height: {$bannerHeight}px; position: relative; overflow: hidden;'>";
        $html .= "<div class='admin-banner-slider' id='adminBannerSlider'>";
        
        foreach ($banners as $index => $banner) {
            $isActive = $index === 0 ? 'active' : '';
            $animationType = $banner['animation_type'] ?? 'fade';
            $transitionDuration = $banner['transition_duration'] ?? 500;
            
            $html .= "<div class='admin-banner-slide {$isActive}' ";
            $html .= "data-animation='{$animationType}' ";
            $html .= "data-duration='{$transitionDuration}' ";
            $html .= "style='background-image: url(" . BASE_URL . ltrim($banner['image_path'], '/') . "); ";
            $html .= "background-size: cover; background-position: center; ";
            $html .= "position: absolute; top: 0; left: 0; width: 100%; height: 100%; ";
            
            if ($animationType === 'fade') {
                $html .= "opacity: " . ($isActive ? '1' : '0') . "; transition: opacity {$transitionDuration}ms ease-in-out;";
            } elseif ($animationType === 'slide') {
                $html .= "transform: translateX(" . ($isActive ? '0' : '100%') . "); transition: transform {$transitionDuration}ms ease-in-out;";
            } elseif ($animationType === 'zoom') {
                $html .= "transform: scale(" . ($isActive ? '1' : '1.1') . "); transition: transform {$transitionDuration}ms ease-in-out;";
            }
            
            $html .= "'>";
            
            if (!empty($banner['title'])) {
                $html .= "<div class='admin-banner-overlay'>";
                $html .= "<div class='admin-banner-content'>";
                $html .= "<h2 class='admin-banner-title'>{$banner['title']}</h2>";
                if (!empty($banner['description'])) {
                    $html .= "<p class='admin-banner-description'>{$banner['description']}</p>";
                }
                $html .= "</div></div>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        
        if (count($banners) > 1 && $animationEnabled) {
            $html .= "<div class='admin-banner-indicators'>";
            foreach ($banners as $index => $banner) {
                $activeClass = $index === 0 ? 'active' : '';
                $html .= "<span class='admin-banner-indicator {$activeClass}' data-slide='{$index}'></span>";
            }
            $html .= "</div>";
        }
        
        $html .= "</div>";
        
        return $html;
    }

    public function renderThemeScript() {
        $animationEnabled = $this->themeModel->getThemeSetting('animation_enabled', true);
        
        if (!$animationEnabled) {
            return '';
        }
        
        return "
        <script>
        (function() {
            const slider = document.getElementById('adminBannerSlider');
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.admin-banner-slide');
            if (slides.length <= 1) return;
            
            const indicators = document.querySelectorAll('.admin-banner-indicator');
            let currentSlide = 0;
            const slideInterval = 5000;
            
            function showSlide(index) {
                slides.forEach((slide, i) => {
                    const animationType = slide.dataset.animation || 'fade';
                    const duration = parseInt(slide.dataset.duration) || 500;
                    
                    if (i === index) {
                        slide.classList.add('active');
                        if (animationType === 'fade') {
                            slide.style.opacity = '1';
                        } else if (animationType === 'slide') {
                            slide.style.transform = 'translateX(0)';
                        } else if (animationType === 'zoom') {
                            slide.style.transform = 'scale(1)';
                        }
                    } else {
                        slide.classList.remove('active');
                        if (animationType === 'fade') {
                            slide.style.opacity = '0';
                        } else if (animationType === 'slide') {
                            slide.style.transform = 'translateX(100%)';
                        } else if (animationType === 'zoom') {
                            slide.style.transform = 'scale(1.1)';
                        }
                    }
                });
                
                indicators.forEach((indicator, i) => {
                    indicator.classList.toggle('active', i === index);
                });
                
                currentSlide = index;
            }
            
            function nextSlide() {
                const next = (currentSlide + 1) % slides.length;
                showSlide(next);
            }
            
            setInterval(nextSlide, slideInterval);
            
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => showSlide(index));
            });
        })();
        </script>
        ";
    }
}

