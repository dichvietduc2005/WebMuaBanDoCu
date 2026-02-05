<?php
// Ensure config is loaded to define BASE_URL
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../../config/config.php';
}

// Get database connection
global $pdo;
if (!isset($pdo) || $pdo === null) {
    // Explicitly load Database class if possible
    $dbPath = __DIR__ . '/../../Core/Database.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
    }

    if (class_exists('Database')) {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
    } else {
        // Fallback
        try {
            $db_host = $_ENV['DB_HOST'] ?? 'localhost';
            $db_port = $_ENV['DB_PORT'] ?? '3306';
            $db_name = $_ENV['DB_NAME'] ?? 'muabandocu';
            $db_user = $_ENV['DB_USER'] ?? 'root';
            $db_pass = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Exception $e) {
            error_log("ProfileUserView DB error: " . $e->getMessage());
        }
    }
}

// Ensure variables are initialized to avoid undefined errors
$categories = [];
$cart_count = 0;
$unread_notifications = 0;

if (isset($pdo) && $pdo) {
    // 1. Fetch Categories (Force load)
    try {
        // DEBUG: Select ALL to be safe
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Profile header category load error: " . $e->getMessage());
    }

    // 2. Fetch User Data (Cart & Notifications) if logged in
    if (isset($_SESSION['user_id'])) {
        try {
            // Cart count
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $cart_count = (int) $stmt->fetchColumn();

            // Notifications
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$_SESSION['user_id']]);
            $unread_notifications = (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Profile header user data load error: " . $e->getMessage());
        }
    }
}

// Header will use these passed variables
require_once __DIR__ . '/../../Components/header/Header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/index.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
    <!-- PROMAX UI CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/profile-promax.css?v=<?php echo time(); ?>">

    <!-- Custom Fonts for Promax -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header: Pass explicit data to guarantee display -->
    <?php
    renderHeader($pdo, $categories, $cart_count, $unread_notifications);
    ?>

    <!-- PROMAX UI LAYOUT -->
    <div class="promax-container">

        <!-- SIDEBAR (Identity Rail) -->
        <aside class="promax-sidebar">
            <div class="promax-avatar-frame">
                <!-- Avatar Logic: Image or Initials -->
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Avatar"
                        class="promax-avatar-img">
                <?php else: ?>
                    <div class="promax-avatar-img">
                        <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <!-- Edit Overlay -->
                <div class="promax-avatar-edit-btn">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <!-- Hidden Upload Input -->
            <input type="file" id="avatar-upload" accept="image/*" class="d-none">

            <div class="promax-user-details">
                <h2 class="promax-user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h2>
                <div class="promax-user-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Member'); ?></div>
            </div>

            <nav class="promax-nav">
                <a href="#" class="promax-nav-item active">
                    <i class="fas fa-id-card"></i> Thông tin cá nhân
                </a>
                <!-- Link to Home Removed as requested -->
            </nav>
        </aside>

        <!-- MAIN CONTENT (Workspace) -->
        <main class="promax-content">
            <header class="promax-header">
                <div class="promax-title-group">
                    <h1>Hồ sơ của tôi</h1>
                    <p class="promax-subtitle">Quản lý thông tin định danh và bảo mật</p>
                </div>

                <div class="promax-actions">
                    <button id="btn-edit" class="btn-promax btn-promax-ghost">
                        <i class="fas fa-pen"></i> Chỉnh sửa
                    </button>
                    <button id="btn-save" class="btn-promax btn-promax-primary d-none">
                        <i class="fas fa-check"></i> Lưu thay đổi
                    </button>
                </div>
            </header>

            <form id="profile-form">
                <div class="promax-form-grid">

                    <!-- Username -->
                    <div class="promax-field">
                        <label class="promax-label">Tên đăng nhập (ID)</label>
                        <div class="promax-input-wrapper">
                            <input type="text" class="promax-input" id="user_name"
                                value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" disabled>
                        </div>
                    </div>

                    <!-- Role/Status (Read-only context) -->
                    <div class="promax-field">
                        <label class="promax-label">Trạng thái</label>
                        <div class="promax-input-wrapper">
                            <input type="text" class="promax-input" value="Hoạt động" disabled style="color: #10b981;">
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="promax-field full-width">
                        <label class="promax-label" for="user_full_name">Họ và tên hiển thị</label>
                        <div class="promax-input-wrapper">
                            <input type="text" class="promax-input" id="user_full_name"
                                value="<?php echo htmlspecialchars($_SESSION['user_full_name'] ?? ''); ?>" disabled>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="promax-field full-width">
                        <label class="promax-label" for="user_email">Địa chỉ Email</label>
                        <div class="promax-input-wrapper">
                            <input type="email" class="promax-input" id="user_email"
                                value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" disabled>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="promax-field">
                        <label class="promax-label" for="user_phone">Số điện thoại</label>
                        <div class="promax-input-wrapper">
                            <input type="tel" class="promax-input" id="user_phone"
                                value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>" disabled>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="promax-field">
                        <label class="promax-label" for="user_address">Địa chỉ giao hàng</label>
                        <div class="promax-input-wrapper">
                            <input type="text" class="promax-input" id="user_address"
                                value="<?php echo htmlspecialchars($_SESSION['user_address'] ?? ''); ?>" disabled>
                        </div>
                    </div>

                </div>
            </form>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        // Use window.userId to avoid redeclaration errors ('const' in global scope)
        if (typeof window.userId === 'undefined') {
            window.userId = <?php echo isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 'null'; ?>;
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page Specific Scripts -->
    <script src="<?php echo BASE_URL; ?>public/assets/js/profile_user.js?v=7"></script>

    <!-- Chat Widget - Add this to fix ChatMessagesContainer error -->
    <?php require_once __DIR__ . '/../../Components/ChatWidget.php'; ?>

    <?php require_once __DIR__ . '/../Components/footer.php'; ?>
</body>

</html>