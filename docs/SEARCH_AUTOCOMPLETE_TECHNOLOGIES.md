# Công Nghệ & Công Cụ Cho Tính Năng Gợi Ý Tìm Kiếm (Search Autocomplete)

## 📋 Tổng Quan

Tài liệu này mô tả các công nghệ và công cụ có thể sử dụng để triển khai tính năng gợi ý tìm kiếm (autocomplete) cho thanh tìm kiếm.

## 🎯 Giải Pháp Hiện Tại (Đã Triển Khai)

### Công Nghệ Sử Dụng
- **Frontend**: Vanilla JavaScript (ES6+)
- **Backend**: PHP + MySQL
- **API**: RESTful API với JSON response
- **File**: `public/assets/js/search-autocomplete.js`
- **API Endpoint**: `app/Controllers/extra/api.php?action=search_suggestions`

### Ưu Điểm
✅ Không cần thư viện bên ngoài  
✅ Dễ tùy chỉnh và bảo trì  
✅ Performance tốt với dataset nhỏ (< 10,000 sản phẩm)  
✅ Tích hợp dễ dàng với codebase hiện tại  
✅ Hỗ trợ keyboard navigation (Arrow keys, Enter, Escape)  
✅ Debounce để tối ưu số lượng API calls  

### Nhược Điểm
❌ Performance giảm khi dataset lớn (> 50,000 sản phẩm)  
❌ Không có fuzzy search (tìm kiếm mờ)  
❌ Không có typo tolerance (chấp nhận lỗi chính tả)  
❌ Không có ranking/relevance scoring  

### Cách Hoạt Động
1. User gõ từ khóa vào input (tối thiểu 2 ký tự)
2. JavaScript debounce 300ms để tránh call API quá nhiều
3. Gửi AJAX request đến API endpoint
4. Backend query MySQL với `LIKE %keyword%`
5. Trả về JSON với danh sách suggestions
6. Frontend hiển thị dropdown với thumbnail và highlighted text

---

## 🚀 Các Công Nghệ/Công Cụ Khác

### 1. Thư Viện JavaScript (Client-Side)

#### **jQuery UI Autocomplete**
```javascript
$("#search-input").autocomplete({
    source: "/api/search-suggestions",
    minLength: 2
});
```
- **Ưu điểm**: Dễ sử dụng, có sẵn nhiều tính năng
- **Nhược điểm**: Phụ thuộc jQuery, bundle size lớn
- **Phù hợp**: Dự án đã dùng jQuery

#### **Typeahead.js (Twitter)**
```javascript
var products = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: '/api/search?q=%QUERY'
});
$('#search-input').typeahead(null, {
    name: 'products',
    source: products
});
```
- **Ưu điểm**: Mạnh mẽ, hỗ trợ prefetch và remote data
- **Nhược điểm**: Không còn được maintain tích cực
- **Phù hợp**: Dự án cần prefetch data

#### **Awesomplete**
```html
<input class="awesomplete" data-list="Apple, Orange, Banana" />
```
- **Ưu điểm**: Nhẹ, không phụ thuộc jQuery
- **Nhược điểm**: Tính năng hạn chế
- **Phù hợp**: Dự án cần giải pháp đơn giản

#### **Select2**
```javascript
$('#search-input').select2({
    ajax: {
        url: '/api/search',
        dataType: 'json'
    }
});
```
- **Ưu điểm**: UI đẹp, nhiều tính năng
- **Nhược điểm**: Bundle size lớn, phức tạp
- **Phù hợp**: Cần dropdown với nhiều options

---

### 2. Search Engine Chuyên Dụng (Backend)

#### **Elasticsearch**
```php
// PHP Client
$params = [
    'index' => 'products',
    'body' => [
        'query' => [
            'multi_match' => [
                'query' => $keyword,
                'fields' => ['title^3', 'description']
            ]
        ]
    ]
];
$results = $client->search($params);
```
- **Ưu điểm**: 
  - Full-text search mạnh mẽ
  - Fuzzy search, typo tolerance
  - Relevance scoring
  - Scale tốt (hàng triệu documents)
- **Nhược điểm**: 
  - Cần server riêng
  - Setup phức tạp
  - Learning curve cao
- **Phù hợp**: 
  - Dataset lớn (> 100,000 sản phẩm)
  - Cần search phức tạp
  - Có budget cho infrastructure

#### **Algolia**
```javascript
const searchClient = algoliasearch('APP_ID', 'SEARCH_KEY');
const index = searchClient.initIndex('products');
index.search(keyword).then(({ hits }) => {
    // Display results
});
```
- **Ưu điểm**: 
  - SaaS, không cần setup server
  - Performance cực tốt
  - Typo tolerance tự động
  - Analytics tích hợp
- **Nhược điểm**: 
  - Chi phí (free tier: 10,000 requests/month)
  - Phụ thuộc service bên ngoài
- **Phù hợp**: 
  - Startup cần search nhanh
  - Không muốn maintain infrastructure
  - Có budget cho SaaS

#### **Meilisearch**
```javascript
const client = new MeiliSearch({ host: 'http://localhost:7700' });
const index = client.index('products');
const results = await index.search(keyword);
```
- **Ưu điểm**: 
  - Open source, miễn phí
  - Setup đơn giản hơn Elasticsearch
  - Performance tốt
  - Typo tolerance
- **Nhược điểm**: 
  - Vẫn cần server riêng
  - Community nhỏ hơn Elasticsearch
- **Phù hợp**: 
  - Cần search engine nhưng không muốn trả phí
  - Dataset vừa phải (100K - 1M documents)

#### **Apache Solr**
- Tương tự Elasticsearch
- Phù hợp: Enterprise applications

---

### 3. Database Full-Text Search

#### **MySQL FULLTEXT Index**
```sql
-- Tạo index
ALTER TABLE products ADD FULLTEXT(title, description);

-- Query
SELECT * FROM products 
WHERE MATCH(title, description) AGAINST('keyword' IN NATURAL LANGUAGE MODE)
LIMIT 10;
```
- **Ưu điểm**: 
  - Không cần công cụ bên ngoài
  - Tích hợp sẵn với MySQL
  - Relevance scoring
- **Nhược điểm**: 
  - Chỉ hỗ trợ MyISAM hoặc InnoDB (MySQL 5.6+)
  - Performance kém hơn Elasticsearch
  - Không có fuzzy search
- **Phù hợp**: 
  - Dataset vừa phải (< 500,000 records)
  - Đã dùng MySQL
  - Không muốn thêm infrastructure

#### **PostgreSQL tsvector**
```sql
-- Tạo index
CREATE INDEX products_search_idx ON products 
USING GIN(to_tsvector('english', title || ' ' || description));

-- Query
SELECT * FROM products 
WHERE to_tsvector('english', title || ' ' || description) 
@@ to_tsquery('english', 'keyword')
LIMIT 10;
```
- **Ưu điểm**: 
  - Full-text search mạnh mẽ
  - Ranking tốt
  - Hỗ trợ nhiều ngôn ngữ
- **Nhược điểm**: 
  - Cần chuyển sang PostgreSQL
  - Setup phức tạp hơn MySQL
- **Phù hợp**: 
  - Dự án mới hoặc sẵn sàng migrate database

---

## 📊 So Sánh Các Giải Pháp

| Giải Pháp | Độ Khó Setup | Performance | Chi Phí | Scale | Fuzzy Search |
|-----------|--------------|-------------|---------|-------|--------------|
| **Vanilla JS + MySQL LIKE** | ⭐ Dễ | ⭐⭐ Trung bình | 💰 Miễn phí | < 50K | ❌ |
| **MySQL FULLTEXT** | ⭐⭐ Trung bình | ⭐⭐⭐ Tốt | 💰 Miễn phí | < 500K | ❌ |
| **jQuery UI Autocomplete** | ⭐ Dễ | ⭐⭐ Trung bình | 💰 Miễn phí | < 50K | ❌ |
| **Meilisearch** | ⭐⭐⭐ Khó | ⭐⭐⭐⭐ Rất tốt | 💰 Miễn phí | > 1M | ✅ |
| **Elasticsearch** | ⭐⭐⭐⭐ Rất khó | ⭐⭐⭐⭐⭐ Xuất sắc | 💰 Miễn phí | > 10M | ✅ |
| **Algolia** | ⭐⭐ Dễ | ⭐⭐⭐⭐⭐ Xuất sắc | 💰💰💰 Trả phí | > 10M | ✅ |

---

## 🎯 Khuyến Nghị

### Cho Dự Án Hiện Tại (WebMuaBanDoCu)

#### **Giai Đoạn 1: Cải Thiện Giải Pháp Hiện Tại** ✅ (Đã làm)
- Sửa lỗi ID mismatch (`search-input` vs `search-input-desktop`)
- Tối ưu debounce time
- Thêm loading state
- Cải thiện UI/UX

#### **Giai Đoạn 2: Nâng Cấp Database Search** (Khi dataset > 10,000 sản phẩm)
```sql
-- Thêm FULLTEXT index
ALTER TABLE products ADD FULLTEXT(title, description);

-- Cập nhật SearchModel
SELECT p.*, pi.image_path,
       MATCH(p.title, p.description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
WHERE MATCH(p.title, p.description) AGAINST(? IN NATURAL LANGUAGE MODE)
  AND p.status = 'active' AND p.stock_quantity > 0
ORDER BY relevance DESC
LIMIT 10;
```

#### **Giai Đoạn 3: Chuyển Sang Search Engine** (Khi dataset > 100,000 sản phẩm)
- **Meilisearch**: Nếu muốn open source, miễn phí
- **Algolia**: Nếu có budget và muốn setup nhanh

---

## 🔧 Cải Thiện Giải Pháp Hiện Tại

### 1. Thêm Caching
```php
// Cache suggestions trong 5 phút
$cacheKey = "search_suggestions_" . md5($keyword);
$cached = $cache->get($cacheKey);
if ($cached) return $cached;

$results = SearchModel::getSuggestions($pdo, $keyword, $limit);
$cache->set($cacheKey, $results, 300); // 5 minutes
```

### 2. Thêm Popular Searches
```php
// Lấy từ khóa tìm kiếm phổ biến
SELECT keyword, COUNT(*) as count 
FROM search_logs 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY keyword 
ORDER BY count DESC 
LIMIT 5;
```

### 3. Thêm Recent Searches (LocalStorage)
```javascript
// Lưu recent searches
const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
recentSearches.unshift(keyword);
recentSearches.splice(5); // Giữ tối đa 5
localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
```

### 4. Thêm Category Suggestions
```php
// Gợi ý cả category
SELECT c.name, c.slug 
FROM categories c
WHERE c.name LIKE ?
LIMIT 3;
```

---

## 📚 Tài Liệu Tham Khảo

- [Elasticsearch Guide](https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html)
- [Algolia Documentation](https://www.algolia.com/doc/)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [MySQL FULLTEXT Search](https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html)
- [jQuery UI Autocomplete](https://jqueryui.com/autocomplete/)

---

## ✅ Kết Luận

**Giải pháp hiện tại (Vanilla JS + MySQL LIKE) phù hợp cho:**
- Dataset < 50,000 sản phẩm
- Budget hạn chế
- Cần triển khai nhanh

**Nên nâng cấp khi:**
- Dataset > 100,000 sản phẩm
- Cần fuzzy search / typo tolerance
- Cần relevance scoring tốt hơn
- Performance trở thành vấn đề
