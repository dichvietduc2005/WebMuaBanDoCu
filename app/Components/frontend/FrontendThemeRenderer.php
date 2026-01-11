<?php
/**
 * FrontendThemeRenderer - Renders theme styles for frontend pages
 * Applies colors, banners, and event themes to Home.php and other frontend pages
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Models/admin/ThemeModel.php';

class FrontendThemeRenderer {
    private $themeModel;
    
    public function __construct() {
        $this->themeModel = new ThemeModel();
    }

    /**
     * Render CSS variables for frontend pages
     */
    public function renderThemeStyles() {
        try {
            $settings = $this->themeModel->getAllThemeSettings();
            $activeEvent = $this->themeModel->getActiveEvent();
        } catch (Exception $e) {
            error_log("FrontendThemeRenderer error: " . $e->getMessage());
            $settings = [];
            $activeEvent = null;
        }
        
        // Get colors from settings with defaults
        $primaryColor = $settings['primary_color']['value'] ?? '#2563eb';
        $secondaryColor = $settings['secondary_color']['value'] ?? '#7c3aed';
        $accentColor = $settings['accent_color']['value'] ?? '#10b981';
        $backgroundColor = $settings['background_color']['value'] ?? '#ffffff';
        $textColor = $settings['text_color']['value'] ?? '#1f2937';
        
        // Override with event theme if active
        if ($activeEvent && isset($activeEvent['theme_config'])) {
            $eventConfig = json_decode($activeEvent['theme_config'], true);
            if ($eventConfig) {
                $primaryColor = $eventConfig['primary_color'] ?? $primaryColor;
                $secondaryColor = $eventConfig['secondary_color'] ?? $secondaryColor;
                $accentColor = $eventConfig['accent_color'] ?? $accentColor;
            }
        }
        
        echo "<style id='frontend-theme-styles'>";
        
        // Override :root CSS variables used by index.css
        echo ":root {";
        echo "  --primary: {$primaryColor} !important;";
        echo "  --primary-dark: {$secondaryColor} !important;";
        echo "  --primary-light: {$accentColor} !important;";
        echo "  --secondary: {$secondaryColor} !important;";
        echo "  --accent: {$accentColor} !important;";
        echo "  --theme-primary: {$primaryColor};";
        echo "  --theme-secondary: {$secondaryColor};";
        echo "  --theme-accent: {$accentColor};";
        echo "  --theme-bg: {$backgroundColor};";
        echo "  --theme-section-bg: #ffffff;"; // Default to white
        // Simple dark mode detection: if bg is black/dark, make section dark gray
        if (strtolower($backgroundColor) == '#000000' || strtolower($backgroundColor) == '#111111' || strtolower($backgroundColor) == '#1f2937') {
             echo "  --theme-section-bg: #1f2937;"; // Dark gray for sections in dark mode
             echo "  --theme-text: #f3f4f6;"; // Light text
        }
        
        echo "  --theme-text: {$textColor};";
        echo "  --gradient-primary: linear-gradient(135deg, {$primaryColor}, {$secondaryColor}) !important;";
        echo "  --gradient-accent: linear-gradient(135deg, {$accentColor}, {$primaryColor}) !important;";
        echo "}";
        
        // Base Styles using Theme Variables
        echo "html { background-color: var(--theme-bg) !important; scroll-behavior: smooth; }";
        echo "body { color: var(--theme-text) !important; }";
        echo "footer { background-color: #111111 !important; color: white !important; padding-top: 60px; margin-top: 0; }"; 
        echo ".chat-container { background-color: var(--theme-section-bg) !important; color: var(--theme-text) !important; }";
        
        // Apply to sections via generic class if needed, but CSS variable is better
        
        // Links
        echo "a:not(.btn):not(.nav-link) { color: {$primaryColor}; }";
        echo "a:not(.btn):not(.nav-link):hover { color: {$secondaryColor}; }";
        
        // Primary Buttons (multiple selectors for high specificity)
        echo ".btn-primary, 
              button.btn-primary, 
              a.btn-primary,
              .hero-btn.btn-white,
              .add-to-cart-btn,
              .btn-add-cart,
              input[type='submit'].btn-primary { 
            background: {$primaryColor} !important; 
            background-color: {$primaryColor} !important;
            border-color: {$primaryColor} !important; 
            color: white !important;
        }";
        
        echo ".btn-primary:hover, 
              .hero-btn.btn-white:hover,
              .add-to-cart-btn:hover,
              .btn-add-cart:hover { 
            background: {$secondaryColor} !important; 
            background-color: {$secondaryColor} !important;
            border-color: {$secondaryColor} !important;
        }";
        
        // Outline buttons
        echo ".btn-outline-primary { 
            color: {$primaryColor} !important; 
            border-color: {$primaryColor} !important; 
        }";
        echo ".btn-outline-primary:hover { 
            background: {$primaryColor} !important; 
            color: white !important; 
        }";
        
        // Text colors
        echo ".text-primary { color: {$primaryColor} !important; }";
        echo ".text-secondary { color: {$secondaryColor} !important; }";
        echo ".text-accent, .text-success { color: {$accentColor} !important; }";
        
        // Background colors
        echo ".bg-primary { background-color: {$primaryColor} !important; }";
        echo ".bg-secondary { background-color: {$secondaryColor} !important; }";
        echo ".bg-accent, .bg-success { background-color: {$accentColor} !important; }";
        
        // Gradients  
        echo ".text-gradient, .navbar-brand h1 { 
            background: linear-gradient(135deg, {$primaryColor}, {$secondaryColor}) !important; 
            -webkit-background-clip: text !important; 
            -webkit-text-fill-color: transparent !important; 
            background-clip: text !important; 
        }";
        
        // Hero section
        echo ".hero { 
            background: linear-gradient(135deg, {$primaryColor}dd, {$secondaryColor}cc) !important;
        }";
        echo ".hero-btn.btn-white { 
            background: white !important; 
            color: {$primaryColor} !important; 
        }";
        echo ".hero-btn.btn-white:hover { 
            background: {$accentColor} !important;
            color: white !important;
        }";
        
        // Product cards
        echo ".product-card .add-to-cart-btn,
              .product-card button[type='submit'],
              .card .btn-primary { 
            background: {$primaryColor} !important; 
            border-color: {$primaryColor} !important;
        }";
        echo ".product-price, .price { color: {$primaryColor} !important; }";
        
        // Badges
        echo ".badge-primary, .badge.bg-primary { background: {$primaryColor} !important; }";
        echo ".badge-success, .badge.bg-success { background: {$accentColor} !important; }";
        echo ".badge-accent { background: {$accentColor} !important; }";
        
        // Nav links active state
        echo ".nav-link:hover, .nav-link.active { color: {$primaryColor} !important; }";
        echo ".nav-link:hover, .nav-link.active { 
            background: linear-gradient(135deg, {$primaryColor}1a, {$secondaryColor}1a) !important; 
        }";
        
        // Search button
        echo ".search-btn, .btn-search { 
            background: {$primaryColor} !important; 
            border-color: {$primaryColor} !important; 
        }";
        
        // View all links
        echo ".view-all, .see-all { color: {$primaryColor} !important; }";
        echo ".view-all:hover, .see-all:hover { color: {$secondaryColor} !important; }";
        
        // Section titles
        echo ".section-title::after { background: linear-gradient(90deg, {$primaryColor}, {$secondaryColor}) !important; }";
        
        // Category cards
        echo ".category-card:hover { border-color: {$primaryColor} !important; }";
        echo ".category-icon { color: {$primaryColor} !important; }";
        
        // Footer
        echo ".footer-link:hover { color: {$primaryColor} !important; }";
        
        // Focus states
        echo "input:focus, textarea:focus, select:focus { 
            border-color: {$primaryColor} !important; 
            box-shadow: 0 0 0 3px {$primaryColor}20 !important; 
        }";
        
        // Pagination
        echo ".pagination .active .page-link, .page-item.active .page-link { 
            background: {$primaryColor} !important; 
            border-color: {$primaryColor} !important; 
        }";
        echo ".page-link { color: {$primaryColor} !important; }";
        
        // Website background image (applied to body, sides visible on wide screens like thegioididong)
        $websiteBg = $settings['website_background']['value'] ?? '';
        if (!empty($websiteBg)) {
            $bgUrl = BASE_URL . ltrim($websiteBg, '/');
            echo "body { 
                background: url('{$bgUrl}') center top fixed !important;
                background-size: cover !important;
                min-height: 100vh;
            }";
            // Container stays transparent - sections inside have white bg for readability
            echo ".container { 
                background: transparent !important;
                box-shadow: none !important;
            }";
            // Sections have white/light bg for readability on background image
            echo ".section { 
                background: rgba(255, 255, 255, 0.98) !important;
                box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1) !important;
            }";
            // Footer container transparent to show dark footer bg
            echo "footer .container { 
                background: transparent !important; 
                box-shadow: none !important; 
                max-width: 1200px !important;
            }";
        }
        
        // Hero section background image
        $heroBg = $settings['hero_background_image']['value'] ?? '';
        if (!empty($heroBg)) {
            $heroUrl = BASE_URL . ltrim($heroBg, '/');
            echo ".hero { 
                background: url('{$heroUrl}') center/cover no-repeat !important;
            }";
            echo ".hero-content { position: relative; z-index: 1; }";
        }
        
        echo "</style>";
    }
    
    /**
     * Get customizable hero content from settings
     */
    public function getHeroContent() {
        try {
            $settings = $this->themeModel->getAllThemeSettings();
        } catch (Exception $e) {
            $settings = [];
        }
        
        return [
            'title' => $settings['hero_title']['value'] ?? 'Mua bán đồ cũ - Tiết kiệm, tiện lợi, bảo vệ môi trường',
            'subtitle' => $settings['hero_subtitle']['value'] ?? 'Tìm kiếm và mua bán các mặt hàng đã qua sử dụng một cách dễ dàng với giá cả hợp lý. Hàng ngàn sản phẩm chất lượng đang chờ bạn!',
            'button1_text' => $settings['hero_button1_text']['value'] ?? 'Mua sắm ngay',
            'button2_text' => $settings['hero_button2_text']['value'] ?? 'Đăng bán đồ',
        ];
    }

    /**
     * Render banner slider for frontend
     */
    public function renderBannerSlider() {
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
            error_log("FrontendThemeRenderer renderBannerSlider error: " . $e->getMessage());
            return '';
        }
        
        $bannerHeight = $this->themeModel->getThemeSetting('banner_height', 300);
        $animationEnabled = $this->themeModel->getThemeSetting('animation_enabled', true);
        
        $html = "<div class='frontend-banner-container' style='height: {$bannerHeight}px; position: relative; overflow: hidden; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>";
        $html .= "<div class='frontend-banner-slider' id='frontendBannerSlider'>";
        
        foreach ($banners as $index => $banner) {
            $isActive = $index === 0 ? 'active' : '';
            $animationType = $banner['animation_type'] ?? 'fade';
            $transitionDuration = $banner['transition_duration'] ?? 500;
            
            $html .= "<div class='frontend-banner-slide {$isActive}' ";
            $html .= "data-animation='{$animationType}' ";
            $html .= "data-duration='{$transitionDuration}' ";
            $html .= "style='background-image: url(" . BASE_URL . ltrim($banner['image_path'], '/') . "); ";
            $html .= "background-size: cover; background-position: center; object-fit: cover; ";
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
                $html .= "<div class='frontend-banner-overlay' style='position: absolute; inset: 0; background: linear-gradient(to right, rgba(0,0,0,0.6), transparent); display: flex; align-items: center;'>";
                $html .= "<div class='frontend-banner-content' style='padding: 2rem; color: white;'>";
                $html .= "<h2 style='font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;'>{$banner['title']}</h2>";
                if (!empty($banner['description'])) {
                    $html .= "<p style='font-size: 1rem; opacity: 0.9;'>{$banner['description']}</p>";
                }
                $html .= "</div></div>";
            }
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        
        // Indicators
        if (count($banners) > 1 && $animationEnabled) {
            $html .= "<div class='frontend-banner-indicators' style='position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;'>";
            foreach ($banners as $index => $banner) {
                $activeClass = $index === 0 ? 'background: white;' : 'background: rgba(255,255,255,0.5);';
                $html .= "<span class='frontend-banner-indicator' data-slide='{$index}' style='width: 12px; height: 12px; border-radius: 50%; cursor: pointer; {$activeClass} transition: background 0.3s;'></span>";
            }
            $html .= "</div>";
        }
        
        // Navigation Arrows
        if (count($banners) > 1 && $animationEnabled) {
            $html .= "<button class='banner-arrow prev' style='position: absolute; left: 16px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.3); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.3; shadow: 0 4px 10px rgba(0,0,0,0.2);'><i class='fas fa-chevron-left'></i></button>";
            $html .= "<button class='banner-arrow next' style='position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.3); border: none; width: 40px; height: 40px; border-radius: 50%; color: white; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.3; shadow: 0 4px 10px rgba(0,0,0,0.2);'><i class='fas fa-chevron-right'></i></button>";
            
            $html .= "<style>
                .banner-arrow:hover { background: rgba(0,0,0,0.6) !important; transform: translateY(-50%) scale(1.1) !important; }
                .frontend-banner-indicator:hover { background: white !important; transform: scale(1.2); }
            </style>";
        }
        
        $html .= "</div>";
        
        // Slider JavaScript
        if (count($banners) > 1 && $animationEnabled) {
            $html .= $this->renderBannerScript();
        }
        
        return $html;
    }

    private function renderBannerScript() {
        return "
        <script>
        (function() {
            const slider = document.getElementById('frontendBannerSlider');
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.frontend-banner-slide');
            if (slides.length <= 1) return;
            
            const indicators = document.querySelectorAll('.frontend-banner-indicator');
            let currentSlide = 0;
            const slideInterval = 5000;
            
            function showSlide(index) {
                slides.forEach((slide, i) => {
                    const animationType = slide.dataset.animation || 'fade';
                    
                    if (i === index) {
                        slide.classList.add('active');
                        if (animationType === 'fade') slide.style.opacity = '1';
                        else if (animationType === 'slide') slide.style.transform = 'translateX(0)';
                        else if (animationType === 'zoom') slide.style.transform = 'scale(1)';
                    } else {
                        slide.classList.remove('active');
                        if (animationType === 'fade') slide.style.opacity = '0';
                        else if (animationType === 'slide') slide.style.transform = 'translateX(100%)';
                        else if (animationType === 'zoom') slide.style.transform = 'scale(1.1)';
                    }
                });
                
                indicators.forEach((indicator, i) => {
                    indicator.style.background = i === index ? 'white' : 'rgba(255,255,255,0.5)';
                });
                
                currentSlide = index;
            }
            
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    clearInterval(autoSlide);
                    showSlide(index);
                    autoSlide = setInterval(nextSlide, slideInterval);
                });
            });
            
            const prevBtn = document.querySelector('.banner-arrow.prev');
            const nextBtn = document.querySelector('.banner-arrow.next');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    clearInterval(autoSlide);
                    const prev = (currentSlide - 1 + slides.length) % slides.length;
                    showSlide(prev);
                    autoSlide = setInterval(nextSlide, slideInterval);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    clearInterval(autoSlide);
                    nextSlide();
                    autoSlide = setInterval(nextSlide, slideInterval);
                });
            }
            
            let autoSlide = setInterval(nextSlide, slideInterval);
        })();
        </script>
        ";
    }
}
