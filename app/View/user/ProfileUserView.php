<?php
// Ensure config is loaded to define BASE_URL
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../../config/config.php';
}

// View logic only - data is passed from Controller

// Ensure variables are initialized to avoid undefined errors if accessed directly (fallback)
$categories = $categories ?? [];
$cart_count = $cart_count ?? 0;
$unread_notifications = $unread_notifications ?? 0;
global $pdo; // Header might still need $pdo if it's not fully refactored yet, though we passed it from controller


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
    <?php
    // Basic CSS
    $assetManager = new \App\Core\AssetManager();
    $assetManager->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', 'core');
    $assetManager->addCss('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', 'core');
    $assetManager->addCss('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', 'core');
    
    // Components
    $assetManager->addCss('assets/css/footer.css', 'components');
    
    // Page Specific
    $assetManager->addCss('assets/css/pages/home.css', 'pages'); // Base structure
    $assetManager->addCss('assets/css/pages/profile.css', 'pages'); // Profile specific
    
    // Render
    $assetManager->renderCss();
    ?>


    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-header-card {
            background: rgb(59,130,246);
            background: linear-gradient(135deg, rgba(59,130,246,1) 0%, rgba(37,99,235,1) 100%);
            color: white;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            background-color: rgba(255,255,255,0.2);
            border: 4px solid rgba(255,255,255,0.3);
            font-size: 3rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1rem;
        }
        .info-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .form-floating > .form-control:disabled {
            background-color: #f8f9fa;
            opacity: 1;
        }
        .btn-action {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 767px) {
            .container {
                padding: 12px !important;
                margin-top: 0 !important;
            }
            
            .info-card {
                border-radius: 0 !important;
                margin: 0 -12px !important;
            }
            
            .info-card .p-4,
            .info-card .p-md-5 {
                padding: 16px !important;
            }
            
            .info-card h3 {
                font-size: 18px !important;
                margin-bottom: 16px !important;
            }
            
            #action-buttons {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }
            
            #btn-edit,
            #btn-save {
                width: 100% !important;
                padding: 12px !important;
                font-size: 14px !important;
            }
            
            .form-floating {
                margin-bottom: 12px !important;
            }
            
            .form-floating > .form-control {
                height: 48px !important;
                padding: 16px 12px 4px 12px !important;
                font-size: 16px !important; /* Prevent iOS zoom */
            }
            
            .form-floating > label {
                padding: 12px !important;
                font-size: 14px !important;
            }
            
            .row.g-3 {
                margin: 0 !important;
            }
            
            .row.g-3 > [class*="col-"] {
                padding: 0 !important;
                margin-bottom: 12px !important;
            }
            
            .mb-4 {
                margin-bottom: 16px !important;
                padding: 0 12px !important;
            }
            
            .mb-4 a {
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body>
    <!-- Header: Pass explicit data to guarantee display -->
    <?php 
    renderHeader($pdo, $categories, $cart_count, $unread_notifications); 
    ?>

    <!-- Main Content -->
    <div class="container py-5 mt-4">
        <!-- Breadcrumb & Back -->
        <div class="mb-4">
            <a href="<?php echo BASE_URL; ?>public/index.php" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card info-card overflow-hidden">


                    <!-- Details Section -->
                    <div class="p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row">
                            <h3 class="fw-bold m-0 text-dark mb-3 mb-md-0"><i class="fas fa-id-card me-2 text-primary"></i>Thông tin cá nhân</h3>
                            <div id="action-buttons" class="w-100 w-md-auto">
                                <button id="btn-edit" class="btn btn-outline-primary btn-action w-100 w-md-auto">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                </button>
                                <button id="btn-save" class="btn btn-primary btn-action d-none w-100 w-md-auto">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </div>

                        <form id="profile-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="user_name" placeholder="Username"
                                            value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" disabled>
                                        <label for="user_name">Tên đăng nhập</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="user_full_name" placeholder="Họ và tên"
                                            value="<?php echo htmlspecialchars($_SESSION['user_full_name'] ?? ''); ?>" disabled>
                                        <label for="user_full_name">Họ và tên</label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="user_email" placeholder="name@example.com"
                                            value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" disabled>
                                        <label for="user_email">Email</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="user_phone" placeholder="Số điện thoại"
                                            value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>" disabled>
                                        <label for="user_phone">Số điện thoại</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="user_address" placeholder="Địa chỉ"
                                            value="<?php echo htmlspecialchars($_SESSION['user_address'] ?? ''); ?>" disabled>
                                        <label for="user_address">Địa chỉ</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        // Use window.userId to avoid redeclaration errors ('const' in global scope)
        if (typeof window.userId === 'undefined') {
             window.userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
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