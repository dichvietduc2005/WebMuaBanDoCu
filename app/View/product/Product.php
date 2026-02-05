<?php


require_once '../../../config/config.php';
require_once('../../../app/Controllers/product/ProductUserController.php');
include_once __DIR__ . '/../../Components/header/Header.php';
include_once __DIR__ . '/../../Components/footer/Footer.php';
include_once __DIR__ . '/../../Models/product/CategoryModel.php';

// Explicitly fetch categories logic moved to body
$categories = [];
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'app/View/user/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Get Filter & Pagination Params
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 6;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Fetch Data
$products = getUserProducts($pdo, $user_id, $status_filter, $sort_order, $page, $limit);
$total_products = countUserProducts($pdo, $user_id, $status_filter);
$total_pages = ceil($total_products / $limit);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm của tôi - Web Mua Bán Đồ Cũ</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../../public/assets/css/footer.css" rel="stylesheet">
    <link href="../../../public/assets/css/my-products.css" rel="stylesheet">

    <!-- Scripts (Moved to head for stability) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Ensuring Footer Visibility Override from previous fix */
        body>.footer-wrapper>footer {
            position: relative !important;
            background: #111111 !important;
            padding: 40px 0 !important;
            z-index: 9999 !important;
        }
    </style>
</head>

<body>
    <?php
    // Fetch categories for Form (and pass to header for optimization)
    $categories = [];
    if (class_exists('CategoryModel')) {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->getAllActive();
    }

    renderHeader($pdo, $categories);
    ?>

    <div class="page-container">
        <!-- Header Section -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Sản phẩm của tôi</h1>
                <p class="text-muted mb-0">Quản lý các sản phẩm bạn đang đăng bán</p>
            </div>
            <a href="sell.php" class="btn-add-new">
                <i class="fas fa-plus-circle"></i> Đăng sản phẩm mới
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="content-card mb-4 p-3">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Đang hiện</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="reject" <?= $status_filter == 'reject' ? 'selected' : '' ?>>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Sắp xếp</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="newest" <?= $sort_order == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="oldest" <?= $sort_order == 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                        <option value="price_asc" <?= $sort_order == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="price_desc" <?= $sort_order == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần
                        </option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">Hiển thị <?= count($products) ?> / <?= $total_products ?> sản phẩm</small>
                </div>
            </form>
        </div>

        <?php if (empty($products)): ?>
            <!-- Empty State -->
            <div class="content-card empty-state">
                <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                <h3 class="mb-3">Bạn chưa có sản phẩm nào</h3>
                <p class="empty-text mb-4">Hãy đăng bán những món đồ cũ không dùng đến để kiếm thêm thu nhập nhé!</p>
                <a href="sell.php" class="btn btn-primary btn-lg px-5" style="border-radius: 50px;">Đăng bán ngay</a>
            </div>
        <?php else: ?>

            <!-- Desktop View: Table -->
            <div class="content-card desktop-table-container">
                <div class="table-responsive">
                    <table class="table custom-table mb-0" id="products-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá bán</th>
                                <th>Tình trạng</th>
                                <th>Địa chỉ</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr id="row-<?= $product['id'] ?>" data-id="<?= $product['id'] ?>"
                                    data-title="<?= htmlspecialchars($product['title']) ?>"
                                    data-price="<?= htmlspecialchars($product['price']) ?>"
                                    data-category_id="<?= htmlspecialchars($product['category_id'] ?? '') ?>"
                                    data-condition_status="<?= htmlspecialchars($product['condition_status']) ?>"
                                    data-location="<?= htmlspecialchars($product['location']) ?>"
                                    data-description="<?= htmlspecialchars($product['description']) ?>">

                                    <td>
                                        <div class="product-info">
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?php echo BASE_URL; ?>public/<?php echo htmlspecialchars($product['image_path']); ?>"
                                                    class="product-img" alt="Product">
                                            <?php else: ?>
                                                <div class="product-img d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="product-details">
                                                <h5><?= htmlspecialchars($product['title']) ?></h5>
                                                <div class="meta-info">
                                                    <span class="meta-item"><i class="far fa-clock"></i>
                                                        <?= date('d/m/Y', strtotime($product['created_at'] ?? 'now')) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="price-tag"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                                    </td>

                                    <td>
                                        <?php
                                        $condMap = ['new' => 'Mới', 'like_new' => 'Như mới', 'good' => 'Tốt', 'fair' => 'Khá', 'poor' => 'Cũ'];
                                        echo $condMap[$product['condition_status']] ?? $product['condition_status'];
                                        ?>
                                    </td>

                                    <td>
                                        <span class="text-muted"><i
                                                class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($product['location']) ?></span>
                                    </td>

                                    <td>
                                        <?php
                                        $statusClass = 'status-default';
                                        $statusLabel = $product['status'];
                                        switch ($product['status']) {
                                            case 'active':
                                                $statusClass = 'status-active';
                                                $statusLabel = 'Đang hiện';
                                                break;
                                            case 'pending':
                                                $statusClass = 'status-pending';
                                                $statusLabel = 'Chờ duyệt';
                                                break;
                                            case 'reject':
                                                $statusClass = 'status-reject';
                                                $statusLabel = 'Từ chối';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>

                                    <td class="text-end">
                                        <a href="#" class="action-btn btn-edit" data-id="<?= $product['id'] ?>">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="#" class="action-btn btn-delete delete-btn" data-id="<?= $product['id'] ?>"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa không?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View: Cards -->
            <div class="mobile-cards">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" id="card-<?= $product['id'] ?>">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?php echo BASE_URL; ?>public/<?php echo htmlspecialchars($product['image_path']); ?>"
                                class="card-img" alt="Product">
                        <?php else: ?>
                            <div class="card-img d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item edit-btn-mobile" href="#"
                                                data-id="<?= $product['id'] ?>">Sửa tin</a></li>
                                        <li><a class="dropdown-item text-danger delete-btn" href="#"
                                                data-id="<?= $product['id'] ?>"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa không?');">Xóa tin</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-row">
                                <span class="price-tag"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                                <?php
                                $statusClass = 'status-default';
                                $statusLabel = $product['status'];
                                switch ($product['status']) {
                                    case 'active':
                                        $statusClass = 'status-active';
                                        $statusLabel = 'Đang hiện';
                                        break;
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        $statusLabel = 'Chờ duyệt';
                                        break;
                                    case 'reject':
                                        $statusClass = 'status-reject';
                                        $statusLabel = 'Từ chối';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"
                                    style="font-size: 0.7rem;"><?= $statusLabel ?></span>
                            </div>

                            <div class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($product['location']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!--  Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4 mb-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <!-- Previous -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&sort=<?= $sort_order ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                        
                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&sort=<?= $sort_order ?>"><?= $i ?></a>
                                        </li>
                                <?php endfor; ?>
                        
                                <!-- Next -->
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&sort=<?= $sort_order ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Modern Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chỉnh sửa tin đăng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Tiêu đề sản phẩm</label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Giá bán</label>
                                <div class="input-group">
                                    <input type="text" name="price" id="edit_price" class="form-control" placeholder="0"
                                        required>
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Danh mục</label>
                                <select name="category_id" id="edit_category_id" class="form-select" required>
                                    <?php
                                    // Reuse categories from top
                                    foreach ($categories as $cat) {
                                        echo '<option value="' . (int) $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tình trạng</label>
                                <select name="condition_status" id="edit_condition_status" class="form-select" required>
                                    <option value="new">Mới</option>
                                    <option value="like_new">Như mới</option>
                                    <option value="good">Tốt</option>
                                    <option value="fair">Khá tốt</option>
                                    <option value="poor">Cũ</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Khu vực / Địa chỉ</label>
                                <input type="text" name="location" id="edit_location" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea name="description" id="edit_description" class="form-control" rows="4"
                                    required></textarea>
                            </div>
                            <!-- Detailed Images Display -->
                            <div class="col-12">
                                <label class="form-label">Hình ảnh chi tiết</label>
                                <div id="edit_images_preview"
                                    class="d-flex flex-wrap gap-2 p-2 border rounded bg-light">
                                    <div class="text-muted small fst-italic">Đang tải ảnh...</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" style="padding: 12px; font-weight: 600;">Lưu
                                thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Overlay -->
    <div id="lightbox-overlay" class="lightbox-overlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:10000; align-items:center; justify-content:center;">
        <span class="lightbox-close"
            style="position:absolute; top:20px; right:30px; font-size:40px; color:white; cursor:pointer;">&times;</span>
        <img id="lightbox-img" src="" alt="Fullsize"
            style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 0 20px rgba(255,255,255,0.1);">
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast align-items-center text-white bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../../public/assets/js/my_products.js"></script>
    <!-- Footer -->
    <div class="footer-wrapper">
        <?php footer(); ?>
    </div>

    <script>
        userId = <?php echo $_SESSION['user_id'] ?>
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/user_chat_system.js"> </script>
</body>

</html>