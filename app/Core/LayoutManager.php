<?php
/**
 * LayoutManager - Centralized Template & Component Management
 * 
 * Tách view logic ra khỏi individual views
 * Quản lý tập trung: header, footer, layout, components
 * 
 * Usage:
 *   $layout = new LayoutManager();
 *   $layout->render('home', $data);
 */

class LayoutManager
{
    private $layoutPath = 'app/View/layouts/';
    private $componentPath = 'app/View/components/';
    private $viewPath = 'app/View/';
    private $container;
    private $data = [];
    
    public function __construct(Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }
    
    /**
     * Render full page with layout
     * 
     * @param string $template - Template name (e.g., 'home', 'product-list')
     * @param array $data - Data to pass to template
     * @param string $layout - Layout template (default: 'main')
     */
    public function render($template, $data = [], $layout = 'main')
    {
        // Validate template exists
        $templateFile = BASE_PATH . '/' . $this->viewPath . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Template not found: {$template}");
        }
        
        // Merge with default data
        $this->data = array_merge($this->getDefaultData(), $data);
        
        // Start output buffering to capture view
        ob_start();
        extract($this->data);
        require $templateFile;
        $content = ob_get_clean();
        
        // Render layout with content
        $this->renderLayout($layout, ['content' => $content]);
    }
    
    /**
     * Render layout with content
     * Layout wraps the page content with header/footer
     */
    private function renderLayout($layout, $data = [])
    {
        $layoutFile = BASE_PATH . '/' . $this->layoutPath . $layout . '.php';
        if (!file_exists($layoutFile)) {
            // Fallback: if layout not found, just output content
            echo $data['content'] ?? '';
            return;
        }
        
        extract(array_merge($this->data, $data));
        require $layoutFile;
    }
    
    /**
     * Render component (header, footer, sidebar, etc)
     * Components are partial views without full layout
     * 
     * @param string $component - Component name (e.g., 'header', 'footer', 'sidebar')
     * @param array $data - Component specific data
     */
    public function renderComponent($component, $data = [])
    {
        $componentFile = BASE_PATH . '/' . $this->componentPath . $component . '.php';
        if (!file_exists($componentFile)) {
            error_log("Component not found: {$component}");
            return '';
        }
        
        ob_start();
        extract(array_merge($this->data, $data));
        require $componentFile;
        return ob_get_clean();
    }
    
    /**
     * Get default data for all templates
     * Này là common data dùng ở mọi template
     */
    private function getDefaultData()
    {
        $data = [
            // Base URL
            'baseUrl' => BASE_URL,
            
            // Container (để access services)
            'container' => $this->container,
            
            // Common models
            'productModel' => $this->container->get('productModel'),
            'categoryModel' => $this->container->get('categoryModel'),
            'userModel' => $this->container->get('userModel'),
            
            // View helpers
            'viewRenderer' => $this->container->get('viewRenderer'),
            'viewHelper' => $this->container->get('viewHelper'),
            
            // Database
            'pdo' => $this->container->get('pdo'),
            
            // Theme
            'frontendTheme' => $this->container->get('frontendTheme'),
        ];
        
        // Add session data
        if (isset($_SESSION)) {
            $data['user_id'] = $_SESSION['user_id'] ?? null;
            $data['username'] = $_SESSION['username'] ?? null;
            $data['user_role'] = $_SESSION['user_role'] ?? null;
        }
        
        return $data;
    }
    
    /**
     * Set layout path (customize where layouts are stored)
     */
    public function setLayoutPath($path)
    {
        $this->layoutPath = $path;
        return $this;
    }
    
    /**
     * Set component path (customize where components are stored)
     */
    public function setComponentPath($path)
    {
        $this->componentPath = $path;
        return $this;
    }
    
    /**
     * Set view path
     */
    public function setViewPath($path)
    {
        $this->viewPath = $path;
        return $this;
    }
}

/**
 * Helper function - Shortcut to render layouts
 */
function renderLayout($template, $data = [], $layout = 'main')
{
    $manager = new LayoutManager();
    $manager->render($template, $data, $layout);
}

/**
 * Helper function - Render component
 */
function renderComponent($component, $data = [])
{
    $manager = new LayoutManager();
    return $manager->renderComponent($component, $data);
}

