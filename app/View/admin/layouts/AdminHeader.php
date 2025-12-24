<?php
require_once __DIR__ . '/../../../../config/config.php';

// Đảm bảo chỉ admin truy cập layout này (phòng trường hợp include trực tiếp)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'app/View/user/login_admin.php');
    exit;
}

// Cho phép view thiết lập tiêu đề riêng, mặc định là Dashboard
$pageTitle = isset($pageTitle) ? $pageTitle : 'Trang quản trị - Dashboard';

// Tên page hiện tại dùng cho highlight menu
$currentAdminPage = $currentAdminPage ?? 'dashboard';
?>

<!doctype html>
<html lang="vi"
      x-data="{ page: 'ecommerce', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }"
      x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode') ?? 'false');
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
      "
      :class="{'dark bg-gray-900': darkMode === true}">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Google Fonts - Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN (Load FIRST - before other styles) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            important: false,
            corePlugins: {
                preflight: false
            }
        }
    </script>

    <!-- Material Icons (Load AFTER Tailwind to override any resets) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        /* Material Icons - High specificity to prevent override */
        .material-icons {
            font-family: 'Material Icons' !important;
            font-weight: normal !important;
            font-style: normal !important;
            font-size: 24px !important;
            line-height: 1 !important;
            letter-spacing: normal !important;
            text-transform: none !important;
            display: inline-block !important;
            white-space: nowrap !important;
            word-wrap: normal !important;
            direction: ltr !important;
            -webkit-font-feature-settings: 'liga' !important;
            font-feature-settings: 'liga' !important;
            -webkit-font-smoothing: antialiased !important;
            text-rendering: optimizeLegibility !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
    </style>

    <!-- TailAdmin CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-style.css">
    
    <!-- Material Design Theme Customization CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-theme-customization.css">
    
    <!-- Dynamic Theme Variables Applied to UI (MUST be after ThemeRenderer) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-dynamic-theme.css">
    
    <!-- Admin Theme Customization -->
    <?php
    if (!isset($themeRenderer)) {
        require_once APP_PATH . '/Components/admin/AdminThemeRenderer.php';
        $themeRenderer = new AdminThemeRenderer();
    }
    $themeRenderer->renderThemeStyles();
    ?>
    
    <!-- Banner Slider CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/admin-banner.css">
    
    <!-- Custom style để đảm bảo Roboto được áp dụng -->
    <style>
      body, * {
        font-family: 'Roboto', sans-serif !important;
      }
    </style>

    <!-- Alpine.js cho các tương tác basic của TailAdmin -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </head>
  <body>
    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">

      <!-- Sidebar -->
      <?php include APP_PATH . '/View/admin/layouts/AdminSidebar.php'; ?>

      <!-- Content Area -->
      <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
        <!-- Header đơn giản cho admin -->
        <header
          class="sticky top-0 z-50 flex w-full border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90"
        >
          <div class="flex items-center justify-between w-full px-4 py-3 md:px-6">
            <div class="flex items-center gap-3">
              <button
                @click.stop="sidebarToggle = !sidebarToggle"
                class="inline-flex items-center justify-center w-10 h-10 text-gray-500 border rounded-lg lg:hidden border-gray-200 dark:border-gray-700 dark:text-gray-300"
              >
                <svg
                  class="w-5 h-5"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div>
                <p class="text-xs font-medium tracking-wide text-gray-400 uppercase">
                  Khu vực quản trị
                </p>
                <h1 class="text-base font-semibold text-gray-800 md:text-lg dark:text-white/90">
                  <?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>
                </h1>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <!-- Nút chuyển dark / light -->
              <!-- <button
                class="inline-flex items-center justify-center w-10 h-10 text-gray-500 bg-white border rounded-full shadow-sm border-gray-200 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                @click.prevent="darkMode = !darkMode"
              >
                <svg x-show="!darkMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 3v2.25m6.364.386-1.59 1.59M21 12h-2.25m-.386 6.364-1.59-1.59M12 18.75V21m-4.774-4.226-1.59 1.59M5.25 12H3m4.226-4.774-1.59-1.59M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
              </button> -->

              <!-- Tài khoản admin -->
              <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button
                  @click="open = !open"
                  class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border rounded-full shadow-sm border-gray-200 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                >
                  <span class="inline-flex items-center justify-center bg-indigo-600 rounded-full w-7 h-7 text-white text-xs font-semibold">
                    <?php
                    $name = $_SESSION['username'] ?? 'Admin';
                    echo strtoupper(substr($name, 0, 1));
                    ?>
                  </span>
                  <span class="hidden text-sm md:inline">
                    <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                  <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  x-show="open"
                  class="absolute right-0 z-50 w-56 mt-2 origin-top-right bg-white border rounded-2xl shadow-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700"
                >
                  <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                      <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                      Admin Panel
                    </p>
                  </div>
                  <div class="py-1">
                    <a
                      href="<?php echo BASE_URL; ?>public/index.php"
                      class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                      <span class="mr-2">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                        </svg>
                      </span>
                      Về trang người dùng
                    </a>
                    <a
                      href="<?php echo BASE_URL; ?>app/View/user/logout.php"
                      class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40"
                    >
                      <span class="mr-2">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3-3m0 0l3 3m-3-3v12" />
                        </svg>
                      </span>
                      Đăng xuất
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- Banner Slider (chỉ hiển thị trên Dashboard) -->
        <?php 
        // Only show banner on dashboard page
        $currentPage = $_GET['page'] ?? 'dashboard';
        if (isset($themeRenderer) && $currentPage === 'dashboard') {
            echo $themeRenderer->renderBanner(); 
        }
        ?>

        <!-- Main Content Wrapper: view con sẽ render bên trong -->
        <main class="p-4 mx-auto max-w-[1536px] md:p-6">

