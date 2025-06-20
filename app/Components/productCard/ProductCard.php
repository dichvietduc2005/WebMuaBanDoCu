<?php
function productCard($product) {
    // Lấy hình ảnh chính của sản phẩm
    $defaultImage = '/assets/images/default_product_image.png';
    $productImage = $defaultImage;
    
    if (isset($product['id'])) {
        global $pdo;
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1");
            $stmt->execute([$product['id']]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $image['image_path'])) {
                $productImage = '/' . $image['image_path'];
            }
        }
    }
    
    // Format condition status
    $conditionLabels = [
        'new' => 'Mới',
        'like_new' => 'Như mới', 
        'good' => 'Tốt',
        'fair' => 'Khá tốt',
        'poor' => 'Cần sửa chữa'
    ];
    
    $condition = $conditionLabels[$product['condition_status']] ?? $product['condition_status'];
    
    // Tạo slug từ title và id
    $slug = isset($product['slug']) ? $product['slug'] : $product['id'];
    ?>
    <div class="card h-100 product-card">
        <?php if (isset($product['featured']) && $product['featured']): ?>
            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Nổi bật</span>
        <?php endif; ?>
        
        <img src="<?= htmlspecialchars($productImage) ?>" 
             class="card-img-top object-fit-cover" 
             alt="<?= htmlspecialchars($product['title']) ?>" 
             style="height: 200px;">
        
        <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
            
            <div class="mb-2">
                <?php if (isset($product['category_name'])): ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($product['category_name']) ?></span>
                <?php endif; ?>
                <span class="badge bg-info"><?= htmlspecialchars($condition) ?></span>
                <?php if (isset($product['status']) && $product['status'] === 'sold'): ?>
                    <span class="badge bg-dark">Đã bán</span>
                <?php endif; ?>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <div class="fw-bold text-primary"><?= number_format($product['price'], 0, ',', '.') ?> ₫</div>
                <div class="text-muted small">
                    <i class="bi bi-eye me-1"></i> <?= $product['views'] ?? 0 ?>
                </div>
            </div>
            
            <a href="/product/<?= $slug ?>" class="stretched-link"></a>
        </div>
        
        <div class="card-footer bg-white border-0 pt-0">
            <small class="text-muted">
                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($product['location'] ?? 'Không rõ') ?>
            </small>
            <br>
            <small class="text-muted">
                <i class="bi bi-clock"></i> <?= date('d/m/Y', strtotime($product['created_at'])) ?>
            </small>
        </div>
    </div>
    <?php
}