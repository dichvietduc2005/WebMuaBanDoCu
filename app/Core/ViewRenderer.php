<?php
/**
 * ViewRenderer - Tách View Logic ra khỏi Templates
 * Đây là pattern View Helper để bỏ PHP logic trong View files
 * 
 * Usage:
 *   $renderer = new ViewRenderer();
 *   echo $renderer->renderProductCard($product);
 */

class ViewRenderer
{
    /**
     * Render Product Card
     * Bỏ logic card ra khỏi template, tập trung ở đây
     */
    public static function renderProductCard($product, $baseUrl)
    {
        $productId = htmlspecialchars($product['id']);
        $title = htmlspecialchars($product['title']);
        $imagePath = htmlspecialchars($product['image_path'] ?? '');
        $price = number_format($product['price']);
        $categoryName = htmlspecialchars($product['category_name'] ?? '');
        $condition = ViewHelper::getConditionText($product['condition_status'] ?? 'good');
        $stock = (int)($product['stock_quantity'] ?? 0);
        
        $html = '<div class="product-card">';
        
        // Image section
        $html .= '<div class="product-image">';
        if ($product['featured']) {
            $html .= '<span class="product-badge">Nổi bật</span>';
        }
        
        if ($imagePath) {
            $html .= '<img src="' . $baseUrl . 'public/' . $imagePath . '" alt="' . $title . '">';
        } else {
            $html .= '<div class="no-image"><i class="fas fa-image"></i></div>';
        }
        $html .= '</div>';
        
        // Content section
        $html .= '<div class="product-content">';
        
        // Product specs (tags)
        $html .= '<div class="product-specs">';
        if ($categoryName) {
            $html .= '<span class="spec-tag">' . $categoryName . '</span>';
        }
        $html .= '<span class="spec-tag">' . $condition . '</span>';
        $html .= '</div>';
        
        // Title
        $html .= '<h3 class="product-title">' . $title . '</h3>';
        
        // Price
        $html .= '<div class="product-price-section">';
        $html .= '<span class="current-price">' . $price . ' VNĐ</span>';
        if (isset($product['original_price']) && $product['original_price'] > $product['price']) {
            $discount = round((1 - $product['price']/$product['original_price']) * 100);
            $html .= '<span class="discount-percent">-' . $discount . '%</span>';
        }
        $html .= '</div>';
        
        // Rating
        $html .= '<div class="product-rating">';
        $rating = isset($product['rating']) ? number_format($product['rating'], 1) : '5.0';
        $sales = isset($product['sales_count']) ? number_format($product['sales_count']) : rand(10, 500);
        $html .= '<span class="stars"><i class="fas fa-star"></i> ' . $rating . '</span>';
        $html .= '<span class="separator">•</span>';
        $html .= '<span class="sales">Đã bán ' . $sales . '</span>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Quick add button
        if ($stock > 0) {
            $html .= '<div class="product-hover-action">';
            $html .= '<button type="button" class="btn-quick-add" onclick="event.stopPropagation(); addToCart(event, ' . $productId . ')">';
            $html .= '<i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render Product List Grid
     */
    public static function renderProductGrid($products, $baseUrl, $containerId = 'products-grid')
    {
        if (empty($products)) {
            return '<div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6c757d;">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Chưa có sản phẩm</h3>
                        <p>Hãy quay lại sau để xem các sản phẩm mới nhất!</p>
                    </div>';
        }
        
        $html = '<div class="products-grid" id="' . htmlspecialchars($containerId) . '">';
        foreach ($products as $product) {
            $html .= '<a href="' . $baseUrl . 'app/View/product/Product_detail.php?id=' . htmlspecialchars($product['id']) . '" 
                        style="text-decoration: none; color: inherit; display: block;">';
            $html .= self::renderProductCard($product, $baseUrl);
            $html .= '</a>';
        }
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render Category Dropdown Items
     */
    public static function renderCategoryItems($categories)
    {
        if (empty($categories)) {
            return '<li style="padding: 18px 16px; text-align: center; color: #9ca3af; font-size: 13px;">
                        <i class="fas fa-inbox me-2"></i>Chưa có danh mục
                    </li>';
        }
        
        $html = '';
        foreach ($categories as $index => $category) {
            $slug = htmlspecialchars($category['slug'] ?? '');
            $name = htmlspecialchars($category['name'] ?? 'Unnamed');
            $delay = $index * 0.03;
            
            $html .= '<li style="animation: slideIn 0.3s ease forwards; animation-delay: ' . $delay . 's;">
                        <a href="' . BASE_URL . 'app/View/product/category.php?slug=' . $slug . '"
                           style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; 
                                   font-size: 14px; color: #4b5563; font-weight: 500; text-decoration: none; cursor: pointer; 
                                   transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); border-left: 3px solid transparent;"
                           onmouseover="this.style.backgroundColor=\'#f9f8ff\'; this.style.color=\'#4f46e5\'; this.style.borderLeftColor=\'#4f46e5\';"
                           onmouseout="this.style.backgroundColor=\'white\'; this.style.color=\'#4b5563\'; this.style.borderLeftColor=\'transparent\';">
                            <span>' . $name . '</span>
                            <div style="width: 18px; height: 18px; border: 2px solid #e5e7eb; border-radius: 50%; transition: all 0.2s ease;" 
                                 onmouseover="this.style.borderColor=\'#4f46e5\';"
                                 onmouseout="this.style.borderColor=\'#e5e7eb\';"></div>
                        </a>
                    </li>';
        }
        return $html;
    }
    
    /**
     * Render Search Results
     */
    public static function renderSearchResults($results, $query, $baseUrl)
    {
        if (empty($results)) {
            return '<div class="no-results">
                        <i class="fas fa-inbox"></i>
                        <h3>Không có sản phẩm này</h3>
                        <p>Sản phẩm bạn tìm kiếm không tồn tại hoặc đã hết hàng</p>
                        <a href="' . $baseUrl . 'public/index.php?page=home" class="btn-back-home">Về trang chủ</a>
                    </div>';
        }
        
        $html = '<div class="search-results">';
        foreach ($results as $product) {
            $title = htmlspecialchars($product['title']);
            $price = number_format($product['price']);
            $desc = htmlspecialchars(substr($product['description'] ?? '', 0, 100));
            $category = htmlspecialchars($product['category_name'] ?? 'Khác');
            $condition = $product['condition_status'] ?? 'Tốt';
            $stock = $product['stock_quantity'] ?? 0;
            
            $html .= '<div class="product-item" onclick="window.location.href=\'../product/Product_detail.php?id=' . $product['id'] . '\'">';
            
            // Image
            if (!empty($product['image_path'])) {
                $html .= '<div class="product-image"><img src="' . $baseUrl . 'public/' . htmlspecialchars($product['image_path']) . '" alt="' . $title . '"></div>';
            } else {
                $html .= '<div class="product-image"><div class="no-image"><i class="fas fa-image"></i></div></div>';
            }
            
            // Content
            $html .= '<div class="product-content">';
            $html .= '<h3 class="product-title">' . $title . '</h3>';
            $html .= '<p class="product-description">' . $desc . '...</p>';
            
            $html .= '<div class="product-meta">';
            $html .= '<div class="product-price">' . $price . ' VNĐ</div>';
            $html .= '<div class="product-condition"><i class="fas fa-star"></i> ' . htmlspecialchars($condition) . '</div>';
            $html .= '</div>';
            
            $html .= '<div class="product-info">';
            $html .= '<span class="category"><i class="fas fa-tag"></i> ' . $category . '</span>';
            $html .= '<span class="stock"><i class="fas fa-box"></i> Còn ' . $stock . ' sản phẩm</span>';
            $html .= '</div>';
            
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}

