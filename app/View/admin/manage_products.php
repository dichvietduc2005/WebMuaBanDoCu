<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../Controllers/admin/AdminController.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: ' . BASE_URL . 'app/View/user/login.php');
  exit;
}

$currentAdminPage = 'products';
$pageTitle = 'Quản lý tất cả sản phẩm';

$statusFilter = $_GET['status'] ?? '';
$conditionFilter = $_GET['condition'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

$filters = [
  'status' => $statusFilter,
  'condition' => $conditionFilter,
  'keyword' => $keyword,
];

$all_products = getAllProducts($pdo, $filters);
$featured_products = array_filter($all_products, function ($p) {
  return $p['featured'];
});
$regular_products = array_filter($all_products, function ($p) {
  return !$p['featured'];
});

function renderStatusBadge(?string $status): string
{
  $status = strtolower((string) $status);
  $map = [
    'pending' => ['Chờ duyệt', 'bg-amber-50 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'],
    'active' => ['Đang bán', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400'],
    'sold' => ['Đã bán', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'],
    'reject' => ['Đã từ chối', 'bg-rose-50 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400'],
    'inactive' => ['Ẩn', 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'],
    'hidden' => ['Ẩn', 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'],
  ];

  [$label, $classes] = $map[$status] ?? ['Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];

  return '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full ' . $classes . '">' .
    htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
    '</span>';
}

function renderConditionBadge(?string $condition): string
{
  $condition = trim((string) $condition);
  $normalized = mb_strtolower($condition, 'UTF-8');

  $map = [
    'mới' => ['Mới', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
    'da qua su dung' => ['Đã qua sử dụng', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
    'đã qua sử dụng' => ['Đã qua sử dụng', 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
    'kém' => ['Kém', 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
  ];

  [$label, $classes] = $map[$normalized] ?? [$condition ?: 'Không rõ', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'];

  return '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full ' . $classes . '">' .
    htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .
    '</span>';
}

include APP_PATH . '/View/admin/layouts/AdminHeader.php';
?>

<div class="space-y-4">
  <div id="toast-container" class="fixed z-50 p-3 space-y-2 top-4 right-4"></div>

  <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
          Quản lý tất cả sản phẩm
        </h2>
        <p class="mt-0.5 text-[13px] text-gray-500 dark:text-gray-400">
          Hệ thống quản lý trạng thái, tình trạng và hiển thị sản phẩm
        </p>
      </div>
      <form method="get" action="<?php echo BASE_URL; ?>public/admin/index.php"
        class="flex flex-wrap items-center gap-2 text-xs md:justify-end">
        <input type="hidden" name="page" value="products">
        <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
          placeholder="Tìm theo tiêu đề / người đăng..."
          class="px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        <select name="status"
          class="px-2 py-1 text-xs border rounded-lg border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
          <option value="">Tất cả trạng thái</option>
          <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Đang bán</option>
          <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
          <option value="reject" <?php echo $statusFilter === 'reject' ? 'selected' : ''; ?>>Đã từ chối</option>
          <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Ẩn</option>
        </select>
        <select name="condition"
          class="px-2 py-1 text-xs border rounded-lg border-gray-200 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
          <option value="">Tất cả tình trạng</option>
          <option value="Mới" <?php echo $conditionFilter === 'Mới' ? 'selected' : ''; ?>>Mới</option>
          <option value="Đã qua sử dụng" <?php echo $conditionFilter === 'Đã qua sử dụng' ? 'selected' : ''; ?>>Đã qua sử
            dụng</option>
          <option value="Kém" <?php echo $conditionFilter === 'Kém' ? 'selected' : ''; ?>>Kém</option>
        </select>
        <input type="number" name="min_price"
          value="<?php echo htmlspecialchars($_GET['min_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Giá từ..."
          class="w-24 px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        <input type="number" name="max_price"
          value="<?php echo htmlspecialchars($_GET['max_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="...đến"
          class="w-24 px-2 py-1 text-xs border rounded-lg border-gray-200 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
        <button type="submit"
          class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
          <i class="mr-1.5 fas fa-search"></i> Tìm kiếm
        </button>
      </form>
    </div>

    <div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
      <span class="text-gray-500 dark:text-gray-400">Hành động hàng loạt cho sản phẩm đã chọn:</span>
      <button type="button" data-bulk-action="hide"
        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-40 bulk-action-btn"
        disabled>
        <i class="mr-1 fas fa-eye-slash"></i> Ẩn
      </button>
      <button type="button" data-bulk-action="delete"
        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 disabled:opacity-40 bulk-action-btn"
        disabled>
        <i class="mr-1 fas fa-trash"></i> Xóa
      </button>
      <button type="button" data-bulk-action="feature"
        class="inline-flex items-center px-2 py-1 text-xs font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 disabled:opacity-40 bulk-action-btn"
        disabled>
        <i class="mr-1 fas fa-star"></i> Gắn nổi bật
      </button>
      <button type="button" data-bulk-action="unfeature"
        class="inline-flex items-center px-2 py-1 text-xs font-medium text-sky-700 bg-sky-50 rounded-lg hover:bg-sky-100 disabled:opacity-40 bulk-action-btn"
        disabled>
        <i class="mr-1 fas fa-star-half-alt"></i> Bỏ nổi bật
      </button>
    </div>
  </div>

  <?php if (empty($all_products)): ?>
    <div
      class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-center text-gray-500 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
      Không có sản phẩm nào.
    </div>
  <?php else: ?>

    <!-- Tab Navigation -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="flex items-center gap-4 border-b border-gray-200 dark:border-gray-700">
        <button type="button" data-tab="featured"
          class="product-tab px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
          <i class="mr-1.5 text-amber-500 fas fa-star"></i>
          Sản phẩm nổi bật
          <span
            class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold text-white bg-amber-500 rounded-full">
            <?php echo count($featured_products); ?>
          </span>
        </button>
        <button type="button" data-tab="regular"
          class="product-tab px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 border-b-2 border-transparent hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
          <i class="mr-1.5 text-gray-400 fas fa-list"></i>
          Sản phẩm khác
          <span
            class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold text-white bg-gray-500 rounded-full">
            <?php echo count($regular_products); ?>
          </span>
        </button>
      </div>
    </div>

    <!-- Sản phẩm nổi bật -->
    <div
      class="product-tab-content rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]"
      data-tab-content="featured">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
          <i class="mr-1 text-amber-500 fas fa-star"></i> Sản phẩm nổi bật
        </h3>
        <div class="flex items-center gap-2 text-xs">
          <label class="inline-flex items-center gap-1">
            <input type="checkbox" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
              id="select-all-featured">
            <span>Chọn tất cả</span>
          </label>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200" id="featured-table">
          <thead
            class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
            <tr>
              <th class="w-10 px-4 py-3">
                <!-- checkbox trống, sử dụng header bên trên -->
              </th>
              <th class="px-4 py-3">Hình ảnh</th>
              <th class="px-4 py-3">Tiêu đề</th>
              <th class="px-4 py-3">Người đăng</th>
              <th class="px-4 py-3">Giá</th>
              <th class="px-4 py-3">Tình trạng</th>
              <th class="px-4 py-3">Trạng thái</th>
              <th class="px-4 py-3">Ngày đăng</th>
              <th class="px-4 py-3">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php foreach ($featured_products as $product): ?>
              <tr data-product-id="<?php echo (int) $product['id']; ?>"
                class="bg-amber-50/60 featured-row dark:bg-amber-500/5 border-l-4 border-amber-400">
                <td class="px-4 py-3">
                  <input type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 product-checkbox"
                    value="<?php echo (int) $product['id']; ?>">
                </td>
                <td class="px-4 py-3">
                  <div class="relative group w-16 h-16">
                    <?php if (!empty($product['image_path'])):
                      // Xử lý đường dẫn hình ảnh
                      $imagePath = $product['image_path'];
                      // Nếu đã có 'uploads/products' thì giữ nguyên, nếu không thì thêm
                      if (strpos($imagePath, 'uploads/products') === false) {
                        $imagePath = 'uploads/products/' . ltrim($imagePath, '/');
                      }
                      $imageUrl = BASE_URL . 'public/' . ltrim($imagePath, '/');
                      $placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect fill='%23e5e7eb' width='64' height='64'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%239ca3af' font-family='sans-serif' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E";
                      ?>
                      <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Ảnh sản phẩm"
                        class="object-cover w-16 h-16 rounded-xl"
                        onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderSvg, ENT_QUOTES, 'UTF-8'); ?>';">
                      <div
                        class="absolute z-30 hidden w-40 h-40 p-1 bg-white border rounded-xl shadow-xl -right-2 top-1/2 -translate-y-1/2 group-hover:block dark:bg-gray-900 dark:border-gray-700">
                        <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Xem nhanh sản phẩm"
                          class="object-contain w-full h-full rounded-lg"
                          onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderSvg, ENT_QUOTES, 'UTF-8'); ?>';">
                      </div>
                    <?php else: ?>
                      <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-xl dark:bg-gray-800">
                        <i class="text-gray-400 fas fa-image"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <?php echo htmlspecialchars($product['username'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <?php echo number_format((float) $product['price'], 0, ',', '.'); ?> đ
                </td>
                <td class="px-4 py-3">
                  <select
                    class="condition-select inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full border-0 focus:ring-2 focus:ring-indigo-500 cursor-pointer bg-transparent"
                    data-product-id="<?php echo (int) $product['id']; ?>" data-field="condition_status"
                    data-current="<?php echo htmlspecialchars($product['condition_status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    style="appearance: auto; padding: 0.125rem 0.5rem;">
                    <option value="new" <?php echo ($product['condition_status'] ?? '') === 'new' ? 'selected' : ''; ?>>Mới
                    </option>
                    <option value="like_new" <?php echo ($product['condition_status'] ?? '') === 'like_new' ? 'selected' : ''; ?>>Như mới</option>
                    <option value="good" <?php echo ($product['condition_status'] ?? '') === 'good' ? 'selected' : ''; ?>>Tốt
                    </option>
                    <option value="fair" <?php echo ($product['condition_status'] ?? '') === 'fair' ? 'selected' : ''; ?>>Khá
                      tốt</option>
                    <option value="poor" <?php echo ($product['condition_status'] ?? '') === 'poor' ? 'selected' : ''; ?>>Cũ
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3">
                  <select
                    class="status-select inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full border-0 focus:ring-2 focus:ring-indigo-500 cursor-pointer bg-transparent"
                    data-product-id="<?php echo (int) $product['id']; ?>" data-field="status"
                    data-current="<?php echo htmlspecialchars($product['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    style="appearance: auto; padding: 0.125rem 0.5rem;">
                    <option value="pending" <?php echo ($product['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Chờ
                      duyệt</option>
                    <option value="active" <?php echo ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Đang bán
                    </option>
                    <option value="reject" <?php echo ($product['status'] ?? '') === 'reject' ? 'selected' : ''; ?>>Đã từ chối
                    </option>
                    <option value="sold" <?php echo ($product['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Đã bán
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                  <?php echo htmlspecialchars($product['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="px-4 py-3 text-xs actions">
                  <div class="flex items-center justify-end gap-2">
                    <a href="<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo (int) $product['id']; ?>"
                      target="_blank"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200 dark:shadow-none">
                      <i class="fas fa-eye"></i>
                      <span>Chi tiết</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>app/Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?php echo (int) $product['id']; ?>"
                      class="group relative inline-flex items-center justify-center w-8 h-8 text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-500 hover:text-white transition-all dark:bg-amber-500/10 dark:text-amber-400 action-btn">
                      <i class="fas fa-star-half-alt text-[12px]"></i>
                      <span
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Bỏ
                        nổi bật</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>app/Models/admin/AdminModelAPI.php?action=delete&id=<?php echo (int) $product['id']; ?>"
                      class="group relative inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all dark:bg-red-500/10 dark:text-red-400 delete action-btn">
                      <i class="fas fa-trash text-[12px]"></i>
                      <span
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Xóa</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sản phẩm khác -->
    <div
      class="product-tab-content rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] hidden"
      data-tab-content="regular">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
          <i class="mr-1 text-gray-400 fas fa-list"></i> Sản phẩm khác
        </h3>
        <div class="flex items-center gap-2 text-xs">
          <label class="inline-flex items-center gap-1">
            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              id="select-all-regular">
            <span>Chọn tất cả</span>
          </label>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700 align-middle dark:text-gray-200" id="regular-table">
          <thead
            class="text-xs font-semibold tracking-wide text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/60 dark:text-gray-400">
            <tr>
              <th class="w-10 px-4 py-3"></th>
              <th class="px-4 py-3">Hình ảnh</th>
              <th class="px-4 py-3">Tiêu đề</th>
              <th class="px-4 py-3">Người đăng</th>
              <th class="px-4 py-3">Giá</th>
              <th class="px-4 py-3">Tình trạng</th>
              <th class="px-4 py-3">Trạng thái</th>
              <th class="px-4 py-3">Ngày đăng</th>
              <th class="px-4 py-3">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <?php foreach ($regular_products as $product): ?>
              <tr data-product-id="<?php echo (int) $product['id']; ?>" class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                <td class="px-4 py-3">
                  <input type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 product-checkbox"
                    value="<?php echo (int) $product['id']; ?>">
                </td>
                <td class="px-4 py-3">
                  <div class="relative group w-16 h-16">
                    <?php if (!empty($product['image_path'])):
                      // Xử lý đường dẫn hình ảnh
                      $imagePath = $product['image_path'];
                      // Nếu đã có 'uploads/products' thì giữ nguyên, nếu không thì thêm
                      if (strpos($imagePath, 'uploads/products') === false) {
                        $imagePath = 'uploads/products/' . ltrim($imagePath, '/');
                      }
                      $imageUrl = BASE_URL . 'public/' . ltrim($imagePath, '/');
                      $placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect fill='%23e5e7eb' width='64' height='64'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%239ca3af' font-family='sans-serif' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E";
                      ?>
                      <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Ảnh sản phẩm"
                        class="object-cover w-16 h-16 rounded-xl cursor-zoom-in lightbox-trigger"
                        onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderSvg, ENT_QUOTES, 'UTF-8'); ?>';"
                        data-full-img="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>">
                      <div
                        class="absolute z-30 hidden w-40 h-40 p-1 bg-white border rounded-xl shadow-xl -right-2 top-1/2 -translate-y-1/2 group-hover:block dark:bg-gray-900 dark:border-gray-700">
                        <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Xem nhanh sản phẩm"
                          class="object-contain w-full h-full rounded-lg"
                          onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholderSvg, ENT_QUOTES, 'UTF-8'); ?>';">
                      </div>
                    <?php else: ?>
                      <div class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-xl dark:bg-gray-800">
                        <i class="text-gray-400 fas fa-image"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <?php echo htmlspecialchars($product['username'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                  <?php echo number_format((float) $product['price'], 0, ',', '.'); ?> đ
                </td>
                <td class="px-4 py-3">
                  <select
                    class="condition-select inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full border-0 focus:ring-2 focus:ring-indigo-500 cursor-pointer bg-transparent"
                    data-product-id="<?php echo (int) $product['id']; ?>" data-field="condition_status"
                    data-current="<?php echo htmlspecialchars($product['condition_status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    style="appearance: auto; padding: 0.125rem 0.5rem;">
                    <option value="new" <?php echo ($product['condition_status'] ?? '') === 'new' ? 'selected' : ''; ?>>Mới
                    </option>
                    <option value="like_new" <?php echo ($product['condition_status'] ?? '') === 'like_new' ? 'selected' : ''; ?>>Như mới</option>
                    <option value="good" <?php echo ($product['condition_status'] ?? '') === 'good' ? 'selected' : ''; ?>>Tốt
                    </option>
                    <option value="fair" <?php echo ($product['condition_status'] ?? '') === 'fair' ? 'selected' : ''; ?>>Khá
                      tốt</option>
                    <option value="poor" <?php echo ($product['condition_status'] ?? '') === 'poor' ? 'selected' : ''; ?>>Cũ
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3">
                  <select
                    class="status-select inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full border-0 focus:ring-2 focus:ring-indigo-500 cursor-pointer bg-transparent"
                    data-product-id="<?php echo (int) $product['id']; ?>" data-field="status"
                    data-current="<?php echo htmlspecialchars($product['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    style="appearance: auto; padding: 0.125rem 0.5rem;">
                    <option value="pending" <?php echo ($product['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Chờ
                      duyệt</option>
                    <option value="active" <?php echo ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Đang bán
                    </option>
                    <option value="reject" <?php echo ($product['status'] ?? '') === 'reject' ? 'selected' : ''; ?>>Đã từ chối
                    </option>
                    <option value="sold" <?php echo ($product['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Đã bán
                    </option>
                  </select>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                  <?php echo htmlspecialchars($product['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="px-4 py-3 text-xs actions">
                  <div class="flex items-center justify-end gap-2">
                    <a href="<?php echo BASE_URL; ?>app/View/product/Product_detail.php?id=<?php echo (int) $product['id']; ?>"
                      target="_blank"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200 dark:shadow-none">
                      <i class="fas fa-eye"></i>
                      <span>Chi tiết</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>app/Models/admin/AdminModelAPI.php?action=toggle_featured&id=<?php echo (int) $product['id']; ?>"
                      class="group relative inline-flex items-center justify-center w-8 h-8 text-emerald-600 bg-emerald-50 rounded-lg hover:bg-emerald-600 hover:text-white transition-all dark:bg-emerald-500/10 dark:text-emerald-400 action-btn">
                      <i class="fas fa-star text-[12px]"></i>
                      <span
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Nổi
                        bật</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>app/Models/admin/AdminModelAPI.php?action=delete&id=<?php echo (int) $product['id']; ?>"
                      class="group relative inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all dark:bg-red-500/10 dark:text-red-400 delete action-btn">
                      <i class="fas fa-trash text-[12px]"></i>
                      <span
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-[10px] text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Xóa</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php endif; ?>
</div>

<style>
  /* Style cho dropdown status và condition */
  .status-select,
  .condition-select {
    background-color: transparent;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 0.125rem 0.5rem;
    font-size: 11px;
    font-weight: 500;
    border-radius: 9999px;
    transition: all 0.2s;
  }

  .status-select:hover,
  .condition-select:hover {
    opacity: 0.8;
  }

  .status-select:focus,
  .condition-select:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
  }

  .status-select option,
  .condition-select option {
    padding: 0.5rem;
    background-color: white;
    color: #111827;
  }

  /* Style động cho status */
  .status-select[data-current="pending"] {
    background-color: #fef3c7;
    color: #92400e;
  }

  .status-select[data-current="active"] {
    background-color: #d1fae5;
    color: #065f46;
  }

  .status-select[data-current="reject"] {
    background-color: #fee2e2;
    color: #991b1b;
  }

  .status-select[data-current="sold"] {
    background-color: #f3f4f6;
    color: #374151;
  }

  .dark .status-select[data-current="sold"] {
    background-color: rgba(107, 114, 128, 0.1);
    color: #9ca3af;
  }

  /* Style động cho condition */
  .condition-select[data-current="new"] {
    background-color: #e0f2fe;
    color: #0c4a6e;
  }

  .condition-select[data-current="like_new"] {
    background-color: #fef3c7;
    color: #92400e;
  }

  .condition-select[data-current="good"] {
    background-color: #d1fae5;
    color: #065f46;
  }

  .condition-select[data-current="fair"] {
    background-color: #fef3c7;
    color: #92400e;
  }

  .condition-select[data-current="poor"] {
    background-color: #fee2e2;
    color: #991b1b;
  }

  /* Dark mode */
  .dark .status-select[data-current="pending"],
  .dark .condition-select[data-current="new"] {
    background-color: rgba(251, 191, 36, 0.1);
    color: #fbbf24;
  }

  .dark .status-select[data-current="active"],
  .dark .condition-select[data-current="good"] {
    background-color: rgba(34, 197, 94, 0.1);
    color: #22c55e;
  }

  .dark .status-select[data-current="reject"],
  .dark .condition-select[data-current="poor"] {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin_Product.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin_products_manage.js"></script>
<script>
  // Tab navigation cho sản phẩm
  document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.product-tab');
    const tabContents = document.querySelectorAll('.product-tab-content');

    function switchTab(targetTab) {
      // Remove active class from all tabs
      tabs.forEach(t => {
        t.classList.remove('active', 'border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'font-semibold');
        t.classList.add('text-gray-600', 'dark:text-gray-400', 'font-medium');
      });

      // Add active class to target tab
      const activeTab = document.querySelector(`[data-tab="${targetTab}"]`);
      if (activeTab) {
        activeTab.classList.add('active', 'border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'font-semibold');
        activeTab.classList.remove('text-gray-600', 'dark:text-gray-400', 'font-medium');
      }

      // Hide all tab contents
      tabContents.forEach(content => {
        content.classList.add('hidden');
      });

      // Show target tab content
      const targetContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
      if (targetContent) {
        targetContent.classList.remove('hidden');
      }
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        const targetTab = this.getAttribute('data-tab');
        switchTab(targetTab);
      });
    });

    // Select All Toggle Logic
    function setupSelectAll(allId, tableId) {
      const selectAll = document.getElementById(allId);
      if (!selectAll) return;

      selectAll.addEventListener('change', function () {
        const checkboxes = document.querySelectorAll(`#${tableId} .product-checkbox`);
        checkboxes.forEach(cb => {
          cb.checked = this.checked;
          // Trigger change styling if needed
          const row = cb.closest('tr');
          if (this.checked) {
            row.classList.add('bg-indigo-50/30', 'dark:bg-indigo-500/5');
          } else {
            row.classList.remove('bg-indigo-50/30', 'dark:bg-indigo-500/5');
          }
        });
        updateBulkActionVisibility();
      });
    }

    setupSelectAll('select-all-featured', 'featured-table');
    setupSelectAll('select-all-regular', 'regular-table');

    function updateBulkActionVisibility() {
      const checked = document.querySelectorAll('.product-checkbox:checked').length;
      const btns = document.querySelectorAll('.bulk-action-btn');
      btns.forEach(btn => btn.disabled = checked === 0);
    }

    document.querySelectorAll('.product-checkbox').forEach(cb => {
      cb.addEventListener('change', updateBulkActionVisibility);
    });

    // Set initial active tab (featured)
    switchTab('featured');

    // Xử lý cập nhật status và condition_status qua dropdown
    document.querySelectorAll('.status-select, .condition-select').forEach(select => {
      select.addEventListener('change', async function () {
        const productId = this.getAttribute('data-product-id');
        const field = this.getAttribute('data-field');
        const oldValue = this.getAttribute('data-current');
        const newValue = this.value;

        // Nếu giá trị không thay đổi, không làm gì
        if (oldValue === newValue) {
          return;
        }

        // Disable select trong lúc đang xử lý
        this.disabled = true;
        const originalValue = this.value;

        try {
          const formData = new URLSearchParams();
          formData.append('action', 'update_field');
          formData.append('id', productId);
          formData.append('field', field);
          formData.append('value', newValue);

          const response = await fetch('<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8'); ?>app/Models/admin/AdminModelAPI.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: formData.toString(),
            credentials: 'same-origin'
          });

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          const text = await response.text();
          let data;
          try {
            data = JSON.parse(text);
          } catch (e) {
            console.error('Response text:', text);
            throw new Error('Server trả về dữ liệu không hợp lệ');
          }

          if (!data.success) {
            // Revert về giá trị cũ
            this.value = oldValue;
            showToast('error', 'Lỗi', data.message || 'Không thể cập nhật');
            return;
          }

          // Cập nhật data-current attribute để CSS tự động cập nhật màu
          this.setAttribute('data-current', newValue);

          // Trigger change event để cập nhật style nếu cần
          this.dispatchEvent(new Event('change'));

          // Hiển thị toast thành công
          const fieldLabel = field === 'status' ? 'trạng thái' : 'tình trạng';
          showToast('success', 'Thành công', `Đã cập nhật ${fieldLabel} thành công`);

          // Logging đã được xử lý ở phía server trong AdminModelAPI.php

        } catch (error) {
          console.error('Update error:', error);
          // Revert về giá trị cũ
          this.value = oldValue;
          showToast('error', 'Lỗi', error.message || 'Đã xảy ra lỗi khi cập nhật');
        } finally {
          this.disabled = false;
        }

        // Logic ẩn dòng nếu đang ở chế độ lọc status (ví dụ: đang lọc 'Pending', duyệt xong -> ẩn)
        const urlParams = new URLSearchParams(window.location.search);
        const currentStatusFilter = urlParams.get('status');

        // Chỉ ẩn khi đang lọc theo status và field thay đổi là status
        if (currentStatusFilter && field === 'status' && newValue !== currentStatusFilter) {
          const row = this.closest('tr');
          if (row) {
            // Fade out effect
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '0';
            setTimeout(() => {
              row.remove();
              // Update layout/empty state if needed
              const remainingRows = document.querySelectorAll('tbody tr').length;
              if (remainingRows === 0) {
                location.reload(); // Reload to show empty state or new items
              }
            }, 500);
          }
        }
      });
    });
    // Image Lightbox Logic
    const triggers = document.querySelectorAll('.lightbox-trigger');
    triggers.forEach(trigger => {
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        const fullImgUrl = this.getAttribute('data-full-img');
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm cursor-zoom-out animate-fadeIn';
        overlay.innerHTML = `
            <div class="relative max-w-4xl max-h-[90vh] p-4">
              <img src="${fullImgUrl}" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl animate-zoomIn">
              <button class="absolute -top-10 right-0 text-white text-3xl hover:text-gray-300">&times;</button>
              <p class="absolute -bottom-10 left-0 right-0 text-center text-white/70 text-sm">Click bất cứ đâu để đóng</p>
            </div>
          `;
        overlay.onclick = () => overlay.remove();
        document.body.appendChild(overlay);
      });
    });
  });
</script>

<style>
  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  @keyframes zoomIn {
    from {
      transform: scale(0.9);
      opacity: 0;
    }

    to {
      transform: scale(1);
      opacity: 1;
    }
  }

  .animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
  }

  .animate-zoomIn {
    animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
</style>

<?php include APP_PATH . '/View/admin/layouts/AdminFooter.php'; ?>