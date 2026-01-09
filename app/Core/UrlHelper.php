<?php
namespace App\Core;

/**
 * UrlHelper - Centralized URL management
 * 
 * Replaces hardcoded BASE_URL throughout the application
 * Provides consistent URL generation for assets, routes, and resources
 * 
 * Usage:
 *   UrlHelper::css('core/variables.css')
 *   UrlHelper::js('main.js')
 *   UrlHelper::asset('images/logo.png')
 *   UrlHelper::route('product', ['id' => 123])
 */
class UrlHelper
{
    /**
     * Get base URL from constant or config
     * 
     * @return string Base URL with trailing slash
     */
    private static function getBaseUrl(): string
    {
        if (defined('BASE_URL')) {
            return BASE_URL;
        }
        
        // Fallback: try to detect from server
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Extract base path from script name
        $basePath = dirname($scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        
        return $protocol . '://' . $host . $basePath . '/';
    }
    
    /**
     * Generate URL for asset files (CSS, JS, images)
     * 
     * @param string $path Asset path relative to public/assets/
     * @param bool $addVersion Add version query string for cache busting
     * @return string Full URL to asset
     */
    public static function asset(string $path, bool $addVersion = false): string
    {
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . 'public/assets/' . ltrim($path, '/');
        
        if ($addVersion) {
            $version = defined('ASSET_VERSION') ? ASSET_VERSION : time();
            $url .= '?v=' . $version;
        }
        
        return $url;
    }
    
    /**
     * Generate URL for CSS files
     * 
     * @param string $file CSS filename relative to public/assets/css/
     * @param bool $addVersion Add version query string
     * @return string Full URL to CSS file
     */
    public static function css(string $file, bool $addVersion = false): string
    {
        return self::asset('css/' . ltrim($file, '/'), $addVersion);
    }
    
    /**
     * Generate URL for JavaScript files
     * 
     * @param string $file JS filename relative to public/assets/js/
     * @param bool $addVersion Add version query string
     * @return string Full URL to JS file
     */
    public static function js(string $file, bool $addVersion = false): string
    {
        return self::asset('js/' . ltrim($file, '/'), $addVersion);
    }
    
    /**
     * Generate URL for image files
     * 
     * @param string $file Image filename relative to public/assets/images/
     * @param bool $addVersion Add version query string
     * @return string Full URL to image file
     */
    public static function image(string $file, bool $addVersion = false): string
    {
        return self::asset('images/' . ltrim($file, '/'), $addVersion);
    }
    
    /**
     * Generate route URL
     * 
     * @param string $page Page name (e.g., 'home', 'product', 'login')
     * @param array $params Query parameters
     * @return string Full URL to route
     */
    public static function route(string $page, array $params = []): string
    {
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . 'public/index.php?page=' . urlencode($page);
        
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Generate URL for admin routes
     * 
     * @param string $page Admin page name
     * @param array $params Query parameters
     * @return string Full URL to admin route
     */
    public static function adminRoute(string $page, array $params = []): string
    {
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . 'public/admin/index.php?page=' . urlencode($page);
        
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Generate absolute URL from relative path
     * 
     * @param string $path Relative path (e.g., '/app/View/user/login.php')
     * @return string Full URL
     */
    public static function to(string $path): string
    {
        $baseUrl = self::getBaseUrl();
        return $baseUrl . ltrim($path, '/');
    }
    
    /**
     * Generate URL for uploads
     * 
     * @param string $file Uploaded file path relative to public/uploads/
     * @return string Full URL to uploaded file
     */
    public static function upload(string $file): string
    {
        $baseUrl = self::getBaseUrl();
        return $baseUrl . 'public/uploads/' . ltrim($file, '/');
    }
    
    /**
     * Get current page URL
     * 
     * @param bool $withQuery Include query string
     * @return string Current URL
     */
    public static function current(bool $withQuery = true): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (!$withQuery && strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        return $protocol . '://' . $host . $uri;
    }
}
