<?php
// $currentAdminPage được set từ router / public/admin/index.php
$currentAdminPage = $currentAdminPage ?? 'dashboard';

function adminMenuItem(string $page, string $label, string $iconPath, int $badge = 0): string {
    global $currentAdminPage;

    $isActive = $currentAdminPage === $page;
    $baseUrl = BASE_URL . 'public/admin/index.php?page=' . urlencode($page);

    $itemClasses = 'menu-item group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 ease-in-out';
    $itemClasses .= $isActive
        ? ' bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-500/30 border-l-4 border-indigo-900 scale-[1.02]'
        : ' text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 hover:text-indigo-600 hover:scale-[1.01] dark:text-gray-300 dark:hover:bg-gray-800/80 dark:hover:text-white';

    $iconContainerClasses = 'inline-flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300 ease-in-out';
    $iconContainerClasses .= $isActive
        ? ' bg-white/20 text-white backdrop-blur-sm group-hover:scale-110 group-hover:rotate-6'
        : ' bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100 group-hover:scale-110 group-hover:-rotate-6 dark:bg-indigo-500/10 dark:text-indigo-300';

    $iconStroke = $isActive ? 'white' : 'currentColor';

    $badgeHtml = '';
    if ($badge > 0) {
        $badgeHtml = '<span class="ml-auto flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-red-500 rounded-full animate-pulse">' . $badge . '</span>';
    }

    return '
      <li>
        <a href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '" class="' . $itemClasses . '">
          <span class="' . $iconContainerClasses . '">
            ' . sprintf($iconPath, $iconStroke, $iconStroke) . '
          </span>
          <span class="menu-item-text flex-1">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>
          ' . $badgeHtml . '
        </a>
      </li>
    ';
}
?>

<aside
  class="fixed inset-y-0 left-0 z-40 flex flex-col h-screen px-4 py-6 overflow-y-hidden transition-all duration-300 border-r bg-white/95 border-gray-200 backdrop-blur dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0"
  :class="sidebarToggle ? 'translate-x-0 w-72' : '-translate-x-full w-72 lg:w-72 lg:translate-x-0'"
>
  <!-- Sidebar Header -->
  <div class="flex items-center justify-between gap-2 pb-6 border-b border-gray-100 dark:border-gray-800">
    <a href="<?php echo BASE_URL; ?>public/admin/index.php?page=dashboard" class="flex items-center gap-2">
      <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/40">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
        </svg>
      </div>
      <div class="flex flex-col">
        <span class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">
          Admin Panel
        </span>
        <span class="text-xs text-gray-400">
          WebMuaBanDoCu
        </span>
      </div>
    </a>
    <button
      class="inline-flex items-center justify-center w-9 h-9 text-gray-400 rounded-lg lg:hidden hover:bg-gray-100 dark:hover:bg-gray-800"
      @click.stop="sidebarToggle = !sidebarToggle"
    >
      <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
           viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Sidebar Menu -->
  <div class="flex-1 mt-4 overflow-y-auto no-scrollbar">
    <nav class="space-y-6">
      <!-- Group: Tổng quan -->
      <div>
        <h3 class="mb-4 text-[11px] font-bold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 uppercase">
          <span class="flex items-center gap-2">
            <span class="w-1 h-1 bg-indigo-600 rounded-full animate-pulse"></span>
            Tổng quan
          </span>
        </h3>
        <ul class="space-y-2">
          <?php
          echo adminMenuItem('dashboard', 'Dashboard',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
            </svg>'
          );

          echo adminMenuItem('users', 'Người dùng',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 19a4 4 0 00-8 0m8 0a4 4 0 01-8 0m8 0h3a2 2 0 002-2v-1a4 4 0 00-4-4h-1m-4-3a3 3 0 110-6 3 3 0 010 6z" />
            </svg>'
          );

          echo adminMenuItem('products', 'Quản lý sản phẩm',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 7l9-4 9 4-9 4-9-4z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 17l9 4 9-4M3 12l9 4 9-4" />
            </svg>'
          );

          echo adminMenuItem('products_pending', 'Duyệt sản phẩm',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m2-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>',
            $pendingProductsCount ?? 0
          );

          echo adminMenuItem('coupons', 'Mã giảm giá',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>'
          );

          echo adminMenuItem('notifications', 'Gửi thông báo',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>'
          );
          ?>
        </ul>
      </div>

      <!-- Divider Premium -->
      <div class="relative py-2">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-700"></div>
        </div>
        <div class="relative flex justify-center">
          <span class="px-2 text-xs text-gray-400 bg-white dark:bg-black">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
          </span>
        </div>
      </div>

      <!-- Group: Giao dịch & tương tác -->
      <div>
        <h3 class="mb-4 text-[11px] font-bold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 uppercase">
          <span class="flex items-center gap-2">
            <span class="w-1 h-1 bg-emerald-600 rounded-full animate-pulse"></span>
            Giao dịch & tương tác
          </span>
        </h3>
        <ul class="space-y-2">
          <?php
          // Placeholder pages sẽ được triển khai sau (payments, messages)
          echo adminMenuItem('messages', 'Tin nhắn người dùng',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M7 8h10M7 12h6m-7 8l-3-3V6a2 2 0 012-2h12a2 2 0 012 2v11a2 2 0 01-2 2H7z" />
            </svg>',
            $unreadMessagesCount ?? 0
          );

          echo adminMenuItem('payments', 'Thanh toán & doanh thu',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 8c-1.657 0-3 .843-3 1.882 0 1.04 1.343 1.883 3 1.883s3 .843 3 1.882C15 14.843 13.657 15.686 12 15.686c-1.657 0-3-.843-3-1.882M12 8V6m0 10v-2m9-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>'
          );
          ?>
        </ul>
      </div>

      <!-- Group: Hệ thống & Logs -->
      <div>
        <h3 class="mb-4 text-[11px] font-bold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-gray-600 to-slate-600 uppercase">
          <span class="flex items-center gap-2">
            <span class="w-1 h-1 bg-gray-600 rounded-full animate-pulse"></span>
            Hệ thống & Logs
          </span>
        </h3>
        <ul class="space-y-2">
          <?php
          echo adminMenuItem('user_behavior', 'Thống kê hành vi',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>'
          );

          echo adminMenuItem('admin_logs', 'Lịch sử admin',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>'
          );

          echo adminMenuItem('user_logs', 'Lịch sử người dùng',
            '<svg xmlns="http://www.w3.org/2000/svg" class="%s w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="%s">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>'
          );
          ?>
        </ul>
      </div>
    </nav>
  </div>

  <!-- Footer small info -->
  <div class="pt-4 mt-4 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-800">
    <p>Đăng nhập với vai trò <span class="font-semibold text-gray-600 dark:text-gray-200">Admin</span></p>
    <p class="mt-1 text-[11px]">
      &copy; <?php echo date('Y'); ?> WebMuaBanDoCu
    </p>
  </div>
</aside>
