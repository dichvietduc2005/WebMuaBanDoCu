<?php
namespace App\Core;

class AssetManager {
    protected $cssFiles = [
        'core' => [],
        'components' => [],
        'pages' => [],
        'utilities' => []
    ];
    
    protected $jsFiles = [
        'core' => [],
        'features' => [],
        'pages' => []
    ];
    
    /**
     * Add a CSS file to the queue
     * @param string $path Path relative to public directory (e.g. 'assets/css/style.css')
     * @param string $group Group name: 'core', 'components', 'pages', 'utilities'
     */
    public function addCss($path, $group = 'pages') {
        if (isset($this->cssFiles[$group])) {
            // Avoid duplicates
            if (!in_array($path, $this->cssFiles[$group])) {
                $this->cssFiles[$group][] = $path;
            }
        }
    }
    
    /**
     * Render all registered CSS links with versioning
     */
    public function renderCss() {
        // Defined order of groups
        $groups = ['core', 'components', 'pages', 'utilities'];
        
        foreach ($groups as $group) {
            foreach ($this->cssFiles[$group] as $path) {
                // Ignore empty paths
                if (empty($path)) continue;
                
                // If it's an external URL (starts with http/https), render directly without versioning
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    echo '<link rel="stylesheet" href="' . $path . '">' . PHP_EOL;
                    continue;
                }
                
                // Clean path
                $cleanPath = ltrim($path, '/');
                
                // Get version based on file modification time
                $version = $this->getVersion($cleanPath);
                
                // Generate full URL
                $url = BASE_URL . 'public/' . $cleanPath . '?v=' . $version;
                
                echo '<link rel="stylesheet" href="' . $url . '">' . PHP_EOL;
            }
        }
    }
    
    /**
     * Get file version based on modification time
     */
    protected function getVersion($path) {
        $filePath = PUBLIC_PATH . '/' . $path;
        if (file_exists($filePath)) {
            return filemtime($filePath);
        }
        return time(); // Fallback to current time if file not found
    }
}
