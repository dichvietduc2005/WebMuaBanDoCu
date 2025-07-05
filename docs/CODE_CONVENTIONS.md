# Code Conventions & Standards - Web Mua Bán Đồ Cũ

## 📋 **Tổng quan**

Tài liệu này định nghĩa các quy ước code, naming conventions và coding standards được áp dụng cho dự án Web Mua Bán Đồ Cũ. Việc tuân thủ các quy ước này giúp đảm bảo code consistency, maintainability và team collaboration.

## 🗂️ **Cấu trúc thư mục**

### **Quy ước đặt tên thư mục**
```
📁 app/
├── 📁 Components/     # UI Components (PascalCase)
├── 📁 Controllers/    # Business Logic (PascalCase)
├── 📁 Models/         # Data Models (PascalCase)
├── 📁 View/           # Templates (PascalCase)
└── 📁 Core/           # Core Classes (PascalCase)

📁 public/
├── 📁 assets/         # Static assets (lowercase)
│   ├── 📁 css/        # Stylesheets (lowercase)
│   ├── 📁 js/         # JavaScript files (lowercase)
│   └── 📁 images/     # Images (lowercase)
└── 📁 uploads/        # User uploads (lowercase)

📁 config/             # Configuration files (lowercase)
📁 data/               # Database files (lowercase)
📁 docs/               # Documentation (lowercase)
📁 logs/               # Log files (lowercase)
```

### **Quy ước đặt tên file**

#### **PHP Files**
```php
// Controllers: PascalCase + Controller suffix
UserController.php
ProductController.php
AdminController.php

// Models: PascalCase + Model suffix (optional)
UserModel.php
ProductModel.php
Auth.php (utility classes)

// Views: PascalCase hoặc snake_case
ProfileUserView.php
product_detail.php
login.php

// Components: PascalCase
Header.php
Footer.php
Sidebar.php
```

#### **CSS Files**
```css
/* snake_case với hyphens */
header.css
product_detail.css
admin_box_chat.css
danh_sach_tai_khoan_admin.css
```

#### **JavaScript Files**
```javascript
// snake_case với hyphens
main.js
product_detail.js
admin_Product.js
user_chat_system.js
search-autocomplete.js
```

#### **Database Files**
```sql
-- snake_case
database_complete_fixed.sql
database_sample_data.sql
```

## 🎯 **PHP Coding Standards**

### **PSR-12 Compliance**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\UserModel;

/**
 * User Controller
 * Handles user-related operations
 */
class UserController
{
    private Database $db;
    private UserModel $userModel;
    
    public function __construct()
    {
        $this->db = new Database();
        $this->userModel = new UserModel($this->db);
    }
    
    /**
     * Display user profile
     * 
     * @param int $userId User ID
     * @return void
     */
    public function showProfile(int $userId): void
    {
        try {
            $user = $this->userModel->getUserById($userId);
            
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            $this->renderView('user/profile', ['user' => $user]);
        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
        }
    }
    
    private function renderView(string $view, array $data = []): void
    {
        // Implementation
    }
    
    private function handleError(string $message): void
    {
        // Error handling
    }
}
```

### **Naming Conventions**

#### **Variables & Functions**
```php
// camelCase cho variables và functions
$userName = 'john_doe';
$productList = [];
$isLoggedIn = true;

function getUserById(int $id): ?array
{
    // Implementation
}

function calculateTotalPrice(array $items): float
{
    // Implementation
}

// Constants: UPPER_SNAKE_CASE
const MAX_LOGIN_ATTEMPTS = 3;
const DEFAULT_PAGE_SIZE = 20;
const UPLOAD_PATH = '/uploads/products/';
```

#### **Classes & Methods**
```php
// PascalCase cho class names
class ProductController
{
    // camelCase cho method names
    public function getProductList(): array
    {
        return $this->productModel->getAllProducts();
    }
    
    public function createProduct(array $data): bool
    {
        return $this->productModel->insertProduct($data);
    }
    
    // Private methods prefix với underscore (optional)
    private function validateProductData(array $data): bool
    {
        // Validation logic
    }
}
```

#### **Database Conventions**
```php
// Table names: snake_case (plural)
$tables = [
    'users',
    'products', 
    'categories',
    'order_items',
    'password_resets'
];

// Column names: snake_case
$columns = [
    'user_id',
    'product_name',
    'created_at',
    'updated_at',
    'is_active'
];

// Foreign keys: table_name_id
$foreignKeys = [
    'user_id',
    'product_id',
    'category_id',
    'order_id'
];
```

## 🎨 **CSS/SCSS Standards**

### **BEM Methodology**
```css
/* Block__Element--Modifier */
.product-card {
    display: flex;
    padding: 1rem;
}

.product-card__image {
    width: 100px;
    height: 100px;
}

.product-card__title {
    font-size: 1.2rem;
    font-weight: bold;
}

.product-card__price {
    color: #e74c3c;
}

.product-card--featured {
    border: 2px solid #3498db;
}

.product-card--sold {
    opacity: 0.6;
}
```

### **CSS Variables & Color Palette**
```css
:root {
    /* Primary Colors */
    --primary-color: #3498db;
    --primary-hover: #2980b9;
    --primary-light: #ebf3fd;
    
    /* Secondary Colors */
    --secondary-color: #2ecc71;
    --secondary-hover: #27ae60;
    
    /* Neutral Colors */
    --text-primary: #2c3e50;
    --text-secondary: #7f8c8d;
    --background: #f8f9fa;
    --white: #ffffff;
    --border: #dee2e6;
    
    /* Status Colors */
    --success: #28a745;
    --warning: #ffc107;
    --error: #dc3545;
    --info: #17a2b8;
    
    /* Spacing */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    
    /* Typography */
    --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    
    /* Shadows */
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
}
```

### **Responsive Design**
```css
/* Mobile First Approach */
.container {
    padding: var(--spacing-md);
}

/* Tablet */
@media (min-width: 768px) {
    .container {
        padding: var(--spacing-lg);
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--spacing-xl);
    }
}

/* Large Desktop */
@media (min-width: 1440px) {
    .container {
        max-width: 1400px;
    }
}
```

## 🔧 **JavaScript Standards**

### **ES6+ Features**
```javascript
// Use const/let instead of var
const API_BASE_URL = '/api/v1';
let currentUser = null;

// Arrow functions
const fetchProducts = async (category) => {
    try {
        const response = await fetch(`${API_BASE_URL}/products?category=${category}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching products:', error);
        throw error;
    }
};

// Destructuring
const { name, price, category } = product;
const [first, second, ...rest] = productList;

// Template literals
const message = `Welcome ${user.name}! You have ${cart.items.length} items in your cart.`;

// Modules
export { fetchProducts, updateCart };
import { fetchProducts } from './api.js';
```

### **Naming Conventions**
```javascript
// camelCase for variables and functions
const userName = 'john_doe';
const productList = [];
const isLoggedIn = true;

function getUserById(id) {
    // Implementation
}

function calculateTotalPrice(items) {
    // Implementation
}

// PascalCase for classes and constructors
class ProductManager {
    constructor(apiUrl) {
        this.apiUrl = apiUrl;
        this.products = [];
    }
    
    async loadProducts() {
        // Implementation
    }
}

// UPPER_SNAKE_CASE for constants
const MAX_ITEMS_PER_PAGE = 20;
const DEFAULT_CATEGORY = 'all';
const API_TIMEOUT = 5000;
```

### **Error Handling**
```javascript
// Consistent error handling
const handleApiError = (error, context = '') => {
    console.error(`API Error ${context}:`, error);
    
    // Show user-friendly message
    showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    
    // Log to monitoring service (if available)
    if (window.errorLogger) {
        window.errorLogger.log(error, context);
    }
};

// Async/await with proper error handling
const saveProduct = async (productData) => {
    try {
        const response = await fetch('/api/products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(productData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        return result;
    } catch (error) {
        handleApiError(error, 'saving product');
        throw error;
    }
};
```

## 📊 **SQL Standards**

### **Query Formatting**
```sql
-- Uppercase keywords, lowercase table/column names
SELECT 
    u.user_id,
    u.username,
    u.email,
    p.product_name,
    p.price,
    p.created_at
FROM users u
INNER JOIN products p ON u.user_id = p.seller_id
WHERE u.is_active = 1
    AND p.status = 'approved'
    AND p.price BETWEEN 100000 AND 1000000
ORDER BY p.created_at DESC
LIMIT 20 OFFSET 0;

-- Use meaningful aliases
SELECT 
    u.username AS seller_name,
    p.product_name AS title,
    p.price AS selling_price,
    c.category_name AS category
FROM users u
INNER JOIN products p ON u.user_id = p.seller_id
INNER JOIN categories c ON p.category_id = c.category_id;
```

### **Prepared Statements**
```php
// Always use prepared statements
$stmt = $pdo->prepare("
    SELECT product_id, product_name, price, description 
    FROM products 
    WHERE category_id = ? 
        AND status = 'approved' 
        AND price BETWEEN ? AND ?
    ORDER BY created_at DESC 
    LIMIT ?
");

$stmt->execute([$categoryId, $minPrice, $maxPrice, $limit]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

## 🔍 **Code Quality Tools**

### **PHP Code Standards**
```json
// composer.json
{
    "require-dev": {
        "squizlabs/php_codesniffer": "^3.7",
        "phpstan/phpstan": "^1.8",
        "phpmd/phpmd": "^2.13"
    },
    "scripts": {
        "cs-check": "phpcs --standard=PSR12 app/",
        "cs-fix": "phpcbf --standard=PSR12 app/",
        "analyze": "phpstan analyse app/",
        "test": "phpunit tests/"
    }
}
```

### **JavaScript Linting**
```json
// package.json
{
    "devDependencies": {
        "eslint": "^8.0.0",
        "eslint-config-standard": "^17.0.0",
        "prettier": "^2.8.0"
    },
    "scripts": {
        "lint": "eslint public/assets/js/",
        "lint:fix": "eslint public/assets/js/ --fix",
        "format": "prettier --write public/assets/js/"
    }
}
```

### **ESLint Configuration**
```javascript
// .eslintrc.js
module.exports = {
    env: {
        browser: true,
        es2021: true,
        jquery: true
    },
    extends: [
        'standard'
    ],
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module'
    },
    rules: {
        'indent': ['error', 4],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
        'no-console': 'warn',
        'no-unused-vars': 'error',
        'prefer-const': 'error',
        'no-var': 'error'
    },
    globals: {
        '$': 'readonly',
        'jQuery': 'readonly'
    }
};
```

## 📝 **Documentation Standards**

### **PHPDoc Comments**
```php
/**
 * User authentication and management class
 * 
 * @package App\Models
 * @author Development Team
 * @version 1.0.0
 */
class Auth
{
    /**
     * Authenticate user with email and password
     * 
     * @param string $email User email address
     * @param string $password User password (plain text)
     * @return array|false User data on success, false on failure
     * @throws \Exception When database connection fails
     */
    public function login(string $email, string $password): array|false
    {
        // Implementation
    }
    
    /**
     * Generate secure password reset token
     * 
     * @param string $email User email address
     * @return string Generated token
     * @throws \InvalidArgumentException When email is invalid
     */
    private function generateResetToken(string $email): string
    {
        // Implementation
    }
}
```

### **JSDoc Comments**
```javascript
/**
 * Product management utilities
 * @namespace ProductManager
 */
const ProductManager = {
    /**
     * Fetch products from API
     * @param {string} category - Product category
     * @param {number} page - Page number
     * @param {number} limit - Items per page
     * @returns {Promise<Object>} API response with products
     * @throws {Error} When API request fails
     */
    async fetchProducts(category, page = 1, limit = 20) {
        // Implementation
    },
    
    /**
     * Add product to cart
     * @param {number} productId - Product ID
     * @param {number} quantity - Quantity to add
     * @returns {Promise<boolean>} Success status
     */
    async addToCart(productId, quantity = 1) {
        // Implementation
    }
};
```

## 🔐 **Security Standards**

### **Input Validation**
```php
/**
 * Validate and sanitize user input
 */
class InputValidator
{
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain number';
        }
        
        return $errors;
    }
}
```

### **XSS Prevention**
```php
// Always escape output
echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');

// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### **CSRF Protection**
```php
// Generate CSRF token
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

## 🧪 **Testing Standards**

### **Unit Tests**
```php
// tests/UserModelTest.php
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    private UserModel $userModel;
    
    protected function setUp(): void
    {
        $this->userModel = new UserModel();
    }
    
    public function testGetUserByIdReturnsUser(): void
    {
        $userId = 1;
        $user = $this->userModel->getUserById($userId);
        
        $this->assertIsArray($user);
        $this->assertEquals($userId, $user['user_id']);
    }
    
    public function testGetUserByIdReturnsFalseForInvalidId(): void
    {
        $user = $this->userModel->getUserById(999999);
        
        $this->assertFalse($user);
    }
}
```

### **Integration Tests**
```javascript
// tests/auth.test.js
describe('Authentication Flow', () => {
    test('should login successfully with valid credentials', async () => {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: 'test@example.com',
                password: 'password123'
            })
        });
        
        expect(response.ok).toBe(true);
        const data = await response.json();
        expect(data.success).toBe(true);
        expect(data.user).toBeDefined();
    });
    
    test('should fail login with invalid credentials', async () => {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: 'invalid@example.com',
                password: 'wrongpassword'
            })
        });
        
        expect(response.ok).toBe(false);
        const data = await response.json();
        expect(data.success).toBe(false);
    });
});
```

## 📋 **Code Review Checklist**

### **General**
- [ ] Code follows established naming conventions
- [ ] No hardcoded values (use constants/config)
- [ ] Proper error handling implemented
- [ ] Code is DRY (Don't Repeat Yourself)
- [ ] Functions are single-purpose and well-named
- [ ] Comments explain "why" not "what"

### **PHP Specific**
- [ ] PSR-12 standards followed
- [ ] Type hints used where appropriate
- [ ] Prepared statements for database queries
- [ ] Input validation and sanitization
- [ ] Proper exception handling
- [ ] PHPDoc comments for public methods

### **JavaScript Specific**
- [ ] ES6+ features used appropriately
- [ ] Async/await for asynchronous operations
- [ ] Proper error handling with try/catch
- [ ] No global variables pollution
- [ ] Event listeners properly removed
- [ ] JSDoc comments for complex functions

### **CSS Specific**
- [ ] BEM methodology followed
- [ ] CSS variables used for consistency
- [ ] Mobile-first responsive design
- [ ] No !important unless absolutely necessary
- [ ] Consistent spacing and typography
- [ ] Cross-browser compatibility considered

### **Security**
- [ ] Input validation implemented
- [ ] XSS prevention measures
- [ ] CSRF protection where needed
- [ ] SQL injection prevention
- [ ] Sensitive data not logged
- [ ] Proper authentication checks

## 🔧 **Development Workflow**

### **Git Workflow**
```bash
# Feature development
git checkout -b feature/user-authentication
git add .
git commit -m "feat: implement user authentication system"
git push origin feature/user-authentication

# Commit message format: type(scope): description
# Types: feat, fix, docs, style, refactor, test, chore
```

### **Pre-commit Hooks**
```bash
#!/bin/sh
# .git/hooks/pre-commit

# Run PHP CS Fixer
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Run ESLint
npm run lint

# Run tests
npm test
php vendor/bin/phpunit

echo "Pre-commit checks passed!"
```

---

**Lưu ý**: Tài liệu này sẽ được cập nhật thường xuyên để phản ánh các thay đổi trong quy ước và standards của team. Mọi thành viên đều có trách nhiệm tuân thủ và góp ý cải thiện các quy ước này. 